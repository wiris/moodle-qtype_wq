<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Factory class for creating a \enrol_quizzeslti\tool implementation
 *
 * @package    qtype_wq
 * @copyright  2022 Maths for More S.L. <info@wiris.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * */

namespace qtype_wq;

use \qtype_wq\access_provider;

class php_service_proxy {

    private static $SERVICES;
    private static $MIMES;

    public function __construct() {
        if (!isset(self::$SERVICES)) {
            self::$SERVICES = array(
                "render" => get_config('qtype_wq', 'quizzeseditorurl') . "/render",
                "quizzes" => get_config('qtype_wq', 'quizzesserviceurl') . "/rest",
                "grammar" => get_config('qtype_wq', 'quizzesserviceurl') . "/grammar",
                "wirislauncher" => get_config('qtype_wq', 'quizzeswirislauncherurl'),
                "mathml2accessible" => get_config('qtype_wq', 'quizzeseditorurl') . "/mathml2accessible",
                "plot.png" => get_config('qtype_wq', 'quizzesgraphurl') . "/plot.png",
                "plot.png.base64" => get_config('qtype_wq', 'quizzesgraphurl') . "/plot.png"
            );
        }
        if (!isset(self::$MIMES)) {
            self::$MIMES = array(
                "render" => "image/png",
                "plot.png" => "image/png",
                "plot.png.base64" => "text/plain",
                "quizzes" => "application/xml",
                "grammar" => "text/plain",
                "wirislauncher" => "application/json",
                "mathml2accessible" => "text/plain"
            );
        }
    }

    public function dispatch() {
        $accessprovider = new access_provider();
        if ($accessprovider->is_enabled()) {
            if (!$accessprovider->require_access()) {
                $this->send_error(401, "Login is required to access this service.");
                return;
            }
        }

        if (!isset($_REQUEST["service"])) {
            $this->send_error(404, 'Missing "service" parameter');
            return;
        }

        $service = $_REQUEST["service"];
        if ($service == "resource") {
            if (!isset($_REQUEST["name"])) {
                $this->send_error(404, 'Missing "name" parameter');
                return;
            }

            $name = "../quizzes/lib/" . $_REQUEST["name"];
            if (!file_exists($name)) {
                $this->send_error(404, 'File not found');
                return;
            }

            header('Content-Type: ' . $this->get_resource_content_type($name));
            header('Cache-Control: max-age=1800');

            if (strpos($name, 'quizzes.js') != false) {
                $this->send_quizzes_js($name);
            } else {
                readfile($name);
            }
        } else if ($service == 'url') {
            $url = $this->allowed_url($_REQUEST['url']);
            if (!isset($url)) {
                $this->send_error(403, "URL not allowed");
            }
            
            $headers = array('Accept-Charset: utf-8');

            header('Content-Type: ' . $this->get_url_mime($url));

            echo $this->call_service($url, $headers);
        } else {
            if (!isset(self::$SERVICES[$service])) {
                $this->send_error(404, 'Service not found');
                return;
            }

            $url = self::$SERVICES[$service];
            if (isset($_REQUEST['path'])) {
                $url .= "/" . $_REQUEST['path'];
            }

            $rawpostdata = isset($_REQUEST['rawpostdata']) && $_REQUEST['rawpostdata'] == "true";
            
            $postfields = $rawpostdata ? $_REQUEST['postdata'] : http_build_query(array_filter($_REQUEST, function($value) {
                return $value != "service" && $value != "rawpostdata" && $value != "path";
            }));
            $mime = $rawpostdata ? "text/plain" : "application/x-www-form-urlencoded";

            $headers = array(
                "Accept-Charset: utf-8",
                "Content-Type: " . $mime . ";charset=utf-8",
            );

            if (strpos($service, 'base64') != false) {
                $headers[] = 'Accept: text/plain';
            }

            header('Content-Type: ' . self::$MIMES[$service]);

            echo $this->call_service($url, $headers, true, $postfields);
        }
    }

    public function send_error($errorcode, $message) {
        http_response_code($errorcode);
        echo($errorcode . " " . $message);
    }

    private function send_quizzes_js($name) {
        $js = file_get_contents($name);

        global $CFG;

        $prefix = "com.wiris.quizzes.impl.ConfigurationImpl.";
		$conf = $prefix . "DEF_WIRIS_URL" . " = \"" . $this->js_escape(get_config('qtype_wq', 'quizzeswirisurl')) . "\";\x0A";
		$conf .= $prefix . "DEF_CALC_URL" . " = \"" . $this->js_escape(get_config('qtype_wq', 'quizzescalcmeurl')) . "\";\x0A";
		$conf .= $prefix . "DEF_EDITOR_URL" . " = \"" . $this->js_escape(get_config('qtype_wq', 'quizzeseditorurl')) . "\";\x0A";
		$conf .= $prefix . "DEF_HAND_URL" . " = \"" . $this->js_escape(get_config('qtype_wq', 'quizzeshandurl')) . "\";\x0A";
		$conf .= $prefix . "DEF_SERVICE_URL" . " = \"" . $this->js_escape(get_config('qtype_wq', 'quizzesserviceurl')) . "\";\x0A";
		$conf .= $prefix . "DEF_PROXY_URL" . " = \"" . $this->js_escape($CFG->wwwroot . '/question/type/wq/quizzes/service.php') . "\";\x0A";
		$conf .= $prefix . "DEF_CACHE_DIR" . " = \"" . $this->js_escape($CFG->dataroot . '/filter/wiris/cache') . "\";\x0A";
		$conf .= $prefix . "DEF_MAXCONNECTIONS" . " = \"" . $this->js_escape("-1") . "\";\x0A";
		$conf .= $prefix . "DEF_HAND_ENABLED" . " = \"" . $this->js_escape("true") . "\";\x0A";
		$conf .= $prefix . "DEF_CALC_ENABLED" . " = \"" . $this->js_escape("true") . "\";\x0A";
		$conf .= $prefix . "DEF_SERVICE_OFFLINE" . " = \"" . $this->js_escape("false") . "\";\x0A";
		$conf .= $prefix . "DEF_WIRISLAUNCHER_URL" . " = \"" . $this->js_escape(get_config('qtype_wq', 'quizzeswirislauncherurl')) . "\";\x0A";
		$conf .= $prefix . "DEF_CROSSORIGINCALLS_ENABLED" . " = \"" . $this->js_escape("false") . "\";\x0A";
		$conf .= $prefix . "DEF_RESOURCES_STATIC" . " = \"" . $this->js_escape("false") . "\";\x0A";
		$conf .= $prefix . "DEF_HAND_LOGTRACES" . " = \"" . $this->js_escape("false") . "\";\x0A";
		$conf .= $prefix . "DEF_GRAPH_URL" . " = \"" . $this->js_escape(get_config('qtype_wq', 'quizzesgraphurl')) . "\";\x0A";
		$conf .= $prefix . "DEF_VERSION" . " = \"" . $this->js_escape($this->get_version()) . "\";\x0A";
		$conf .= $prefix . "DEF_DEPLOYMENT_ID" . " = \"" . $this->js_escape("quizzes-moodle") . "\";\x0A";
		$conf .= $prefix . "DEF_LICENSE_ID" . " = \"" . $this->js_escape("") . "\";\x0A";
		$conf .= $prefix . "DEF_TELEMETRY_URL" . " = \"" . $this->js_escape("https://telemetry.wiris.net") . "\";\x0A";
		$conf .= $prefix . "DEF_TELEMETRY_TOKEN" . " = \"" . $this->js_escape("1lt1OnlX3898VauysJ1nr5ODR8CNfVmB80KGxSSt") . "\";\x0A";
		$conf .= $prefix . "DEF_QUIZZES_LOGGING_LEVEL" . " = \"" . $this->js_escape("WARNING") . "\";\x0A";
		$conf .= $prefix . "DEF_QUIZZES_TRACKING_ENABLED" . " = \"" . $this->js_escape("true") . "\";\x0A";
        
        $expose = "if(!window.com) window.com={};\n" . 
        "if(!window.com.wiris) window.com.wiris={};\n" . 
        "if(!window.com.wiris.quizzes) window.com.wiris.quizzes={};\n" .
        "if(!window.com.wiris.quizzes.api) window.com.wiris.quizzes.api={};\n" .
        "if(!window.com.wiris.quizzes.api.ui) window.com.wiris.quizzes.api.ui={};\n" .
        "if(!window.com.wiris.quizzes.api.assertion) window.com.wiris.quizzes.api.assertion={};\n" .
        "window.com.wiris.quizzes.api.Quizzes = com.wiris.quizzes.api.Quizzes;\n" .
        "window.com.wiris.quizzes.api.QuizzesBuilder = com.wiris.quizzes.api.QuizzesBuilder;\n" .
        "window.com.wiris.quizzes.api.ConfigurationKeys = com.wiris.quizzes.api.ConfigurationKeys;\n" .
        "window.com.wiris.quizzes.api.PropertyName = com.wiris.quizzes.api.PropertyName;\n" .
        "window.com.wiris.quizzes.api.assertion.ComparisonName = com.wiris.quizzes.api.assertion.ComparisonName;\n" .
        "window.com.wiris.quizzes.api.assertion.SyntaxName = com.wiris.quizzes.api.assertion.SyntaxName;\n" .
        "window.com.wiris.quizzes.api.assertion.ValidationName = com.wiris.quizzes.api.assertion.ValidationName;\n" .
        "window.com.wiris.quizzes.api.assertion.ComparisonParameterName = com.wiris.quizzes.api.assertion.ComparisonParameterName;\n" . 
        "window.com.wiris.quizzes.api.assertion.ComparisonName = com.wiris.quizzes.api.assertion.ComparisonName;\n" .
        "window.com.wiris.quizzes.api.assertion.SyntaxParameterName = com.wiris.quizzes.api.assertion.SyntaxParameterName;\n" .
        "window.com.wiris.quizzes.api.assertion.ValidationParameterName = com.wiris.quizzes.api.assertion.ValidationParameterName;\n" .
        "window.com.wiris.quizzes.api.QuizzesConstants = com.wiris.quizzes.api.QuizzesConstants;\n" .
        "window.com.wiris.quizzes.api.ui.QuizzesUIConstants = com.wiris.quizzes.api.ui.QuizzesUIConstants;\n" .
        "window.com.wiris.quizzes.api.ui.AnswerFieldType = com.wiris.quizzes.api.ui.AnswerFieldType;\n" .
        "window.com.wiris.quizzes.api.ui.AuthoringFieldType = com.wiris.quizzes.api.ui.AuthoringFieldType;\n" .
        "window.com.wiris.quizzes.api.ui.EmbeddedAnswersEditorMode = com.wiris.quizzes.api.ui.EmbeddedAnswersEditorMode;\n";

        $main = "com.wiris.quizzes.JsQuizzesFilter.main();\n";
        echo "(function(){\x0A" . $js . "\x0A" . $conf . $expose . $main . "})();";
    }

    private function get_version() {
        global $CFG;
        $plugin = new \stdClass();
        require_once($CFG->dirroot . '/question/type/wq/version.php');
        if (isset($plugin->release)) {
            return $plugin->release;
        } else if ($plugin->maturity == MATURITY_BETA) {
            return $plugin->version;
        } else {
            return '';
        }
    }

    private function js_escape($text) {
        $text = str_replace("\\", "\\\\", $text);
		$text = str_replace("\"", "\\\"", $text);
		$text = str_replace("\x0A", "\\n", $text);
		$text = str_replace("\x0D", "\\r", $text);
		$text = str_replace("\x09", "\\t", $text);
		return $text;
    }

    private function allowed_url($url) {
        foreach (self::$SERVICES as $key => $value) {
            if ($this->starts_with($value, "https://") && $this->starts_with($url, "http://")) {
                $url = "https://" . substr($url, 7, strlen($url) - 7);
            } else if ($this->starts_with($value, "http://") && $this->starts_with($url, "https://")) {
                $url = "http://" . substr($url, 8, strlen($url) - 8);
            }

            if ($this->starts_with($url, $value)) {
                return $url;
            }
        }
        return null;
    }

    private function get_url_mime($url) {
        foreach (self::$SERVICES as $key => $value) {
            if ($this->starts_with($url, $value)) {
                if ($value == "grammar" && strpos($url, "json=true") != false) {
                    return "application/json";
                } else {
                    return $value;
                }
            }
        }
    }

    private function starts_with($text, $start) {
        return substr($text, 0, strlen($start)) == $start;
    }

    private function get_resource_content_type($name) {
        $dotpos = strrpos($name, ".");
		$ext = substr($name, $dotpos + 1, strlen($name) - $dotpos - 1);
		if($ext === "png") {
			return "image/png";
		} else if($ext === "gif") {
			return "image/gif";
        } else if($ext === "jpg" || $ext === "jpeg") {
			return "image/jpeg";
		} else if($ext === "html" || $ext === "htm") {
			return "text/html";
		} else if($ext === "css") {
			return "text/css";
        } else if($ext === "js") {
			return "application/javascript";
		} else if($ext === "txt") {
			return "text/plain";
        } else if($ext === "ini") {
            return "text/plain";
        } else {
			return "application/octet-stream";
		}
	}

    private function call_service($url, $headers = array(), $post = false, $postfields = "") {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            print_object("" . curl_error($ch) . curl_errno($ch));
        }

        curl_close($ch);
        return $response;
    }

}