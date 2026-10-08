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
 * Loads the Quizzes client after the page DOM is complete.
 *
 * @package    qtype_wq
 * @copyright  WIRIS Europe (Maths for more S.L)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_wq;

defined('MOODLE_INTERNAL') || die();

/** Queues the shared client used by question forms and quiz attempts. */
class quizzes_loader {
    /**
     * Queues the client without blocking parsing of Moodle's footer.
     *
     * A synchronous script request can let AMD callbacks run before the footer's
     * message drawer exists, aborting other callbacks such as TinyMCE's startup.
     *
     * @param \moodle_page $page The page that needs the Quizzes client.
     */
    public static function load(\moodle_page $page): void {
        $serviceurl = new \moodle_url('/question/type/wq/quizzes/service.php', [
            'name' => 'quizzes.js',
            'service' => 'resource',
        ]);
        $serviceurl = json_encode($serviceurl->out(false));
        $jscode = <<<'JS'
            if (!window.wqQuizzesServiceRequested) {
                window.wqQuizzesServiceRequested = true;

                // CalcMe loads legacy UMD assets as classic scripts and expects their globals.
                // Keep Moodle's AMD flag for every script outside those CalcMe resources.
                var amdDefine = window.define;
                var amdDescriptor = amdDefine && Object.getOwnPropertyDescriptor(amdDefine, 'amd');
                if (amdDescriptor && amdDescriptor.configurable && 'value' in amdDescriptor) {
                    Object.defineProperty(amdDefine, 'amd', {
                        configurable: true,
                        enumerable: amdDescriptor.enumerable,
                        get: function() {
                            var currentScript = document.currentScript;
                            if (currentScript && currentScript.src) {
                                var scriptURL = new URL(currentScript.src, document.baseURI);
                                var scriptPath = scriptURL.pathname.replace(/\/+/g, '/');
                                var isCalcResource = scriptPath.endsWith('/resources/jwt/jwt-decoder.js') ||
                                    (scriptPath.indexOf('/resources/code-mirror/') !== -1 && scriptPath.endsWith('.js'));
                                var quizzes = window.com && window.com.wiris && window.com.wiris.quizzes;
                                if (isCalcResource && quizzes && quizzes.api && quizzes.api.Quizzes &&
                                        quizzes.api.ConfigurationKeys) {
                                    var configuration = quizzes.api.Quizzes.getInstance().getConfiguration();
                                    var calcURL = new URL(configuration.get(quizzes.api.ConfigurationKeys.CALC_URL),
                                        document.baseURI);
                                    var resourcePath = calcURL.pathname.replace(/\/+/g, '/').replace(/\/$/, '') +
                                        '/resources/';
                                    if (scriptURL.origin === calcURL.origin && (
                                            scriptPath === resourcePath + 'jwt/jwt-decoder.js' ||
                                            scriptPath.indexOf(resourcePath + 'code-mirror/') === 0)) {
                                        return false;
                                    }
                                }
                            }
                            return amdDescriptor.value;
                        },
                        set: function(value) {
                            amdDescriptor.value = value;
                        }
                    });
                }
                var script = document.createElement("script");
            JS;
        $jscode .= 'script.async = true;' .
            'script.src = ' . $serviceurl . ';' .
            'document.head.appendChild(script);' .
            '}';
        $page->requires->js_init_code($jscode, true);
    }
}
