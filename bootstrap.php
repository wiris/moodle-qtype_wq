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

require_once(dirname(__FILE__) . '/../../../config.php'); // @codingStandardsIgnoreLine

// A background service request may arrive with a SID invalidated by a later login.
// Keep reading the incoming session for access checks, but never let its response
// replace the browser's newer session. Login and logout pages manage that cookie.
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'service.php') {
    global $CFG;
    $sessioncookiename = 'MoodleSession' . $CFG->sessioncookie;
    header_register_callback(static function() use ($sessioncookiename) {
        $cookieheaders = [];
        foreach (headers_list() as $header) {
            if (preg_match('/^Set-Cookie:\s*([^=]+)=/i', $header, $matches)) {
                if ($matches[1] !== $sessioncookiename) {
                    $cookieheaders[] = $header;
                }
            }
        }
        header_remove('Set-Cookie');
        foreach ($cookieheaders as $header) {
            header($header, false);
        }
    });
}
