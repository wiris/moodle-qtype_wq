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
 * @copyright  2021 Maths for More S.L. <info@wiris.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * */
$bootfile = dirname(__FILE__) . '/../bootstrap.php';
if (@is_readable($bootfile)) require_once($bootfile);

use \qtype_wq\php_service_proxy;

if(version_compare(PHP_VERSION, '7.0.0', '<')) {
    exit('Your current PHP version is: ' . PHP_VERSION . '. Wiris Quizzes needs version 7.0.0 or later');
};

$MAX_UPLOAD_SIZE = 1048576;

foreach ($_FILES as $key => $file) {
    if ($file['size'] > 0) {
        if ($file['size'] > $MAX_UPLOAD_SIZE) {
            http_response_code(400);
            echo "File " . $key . " too large";
            exit;
        }

        $content = '';
        $fh = fopen($file['tmp_name'], 'rb');
        if ($fh !== false) {
            while (!feof($fh)) {
                $content .= fread($fh, 4096);
            }
            fclose($fh);
        }
        $_REQUEST[$key] = $content;
    }
}

$proxy = new php_service_proxy();
$proxy->dispatch();

?>