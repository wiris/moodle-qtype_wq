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

require_once('../../../config.php'); // @codingStandardsIgnoreLine

// BEGIN HELPERS FUNCTIONS.
function wrs_assert_simple($condition) {
    if ($condition) {
        return '<span class="ok wrs_filter wrs_plugin">'. get_string('ok', 'qtype_wq').'</span>';
    } else {
        return '<span class="error wrs_filter wrs_plugin">'. get_string('error', 'qtype_wq').'</span>';
    }
}

function wrs_assert($condition, $reporttext, $solutionlink) {
    if ($condition) {
        return $reporttext;
    } else {
        if ($solutionlink != '') {
            return '<span class="error wrs_filter wrs_plugin">' . $reporttext . '</span>' .
            '<a target="_blank" href="' . $solutionlink . '"><img class="wrs_filter wrs_plugin" alt="" src="img/help.gif" /></a>';
        } else {
            return '<span class="error wrs_filter wrs_plugin">' . $reporttext . '</span>';
        }
    }
}

function wrs_getstatus($condition) {
    if ($condition) {
            return '<span class="ok wrs_filter wrs_plugin">'. get_string('ok', 'qtype_wq').'</span>';
    } else {
            return '<span class="error wrs_filter wrs_plugin">'. get_string('error', 'qtype_wq').'</span>';
    }
}

function wrs_createtablerow($testname, $reporttext, $solutionlink, $condition) {
    $output = '<td class="wrs_filter wrs_plugin">' . $testname . '</td>';
    $output .= '<td class="wrs_filter wrs_plugin">' . wrs_assert($condition, $reporttext, $solutionlink) . '</td>';
    $output .= '<td class="wrs_filter wrs_plugin">' . wrs_getstatus($condition) . '</td>';
    return $output;
}
// END HELPERS FUNCTIONS.

// BEGUIN PAGE PROLOGUE.
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('info_maintitle', 'qtype_wq'));
$PAGE->set_url('/wq/info.php', array());
echo $OUTPUT->header();

$output = '';
$output .= html_writer::start_tag('h1');
$output .= get_string('info_maintitle', 'qtype_wq');
$output .= html_writer::end_tag('h1');

$output .= html_writer::start_tag('table', array('id' => 'wrs_filter_info_table', 'class' => 'wrs_filter wrs_plugin'));

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
$output .= html_writer::start_tag('th', array('class' => 'wrs_filter wrs_plugin'));
$output .= get_string('info_tableheader_test', 'qtype_wq');
$output .= html_writer::end_tag('th');
$output .= html_writer::start_tag('th', array('class' => 'wrs_filter wrs_plugin'));
$output .= get_string('info_tableheader_report', 'qtype_wq');
$output .= html_writer::end_tag('th');
$output .= html_writer::start_tag('th', array('class' => 'wrs_filter wrs_plugin'));
$output .= get_string('info_tableheader_status', 'qtype_wq');
$output .= html_writer::end_tag('th');
$output .= html_writer::end_tag('tr');
echo $output;
// END PAGE PROLOGUE.

// BEGIN TEST 1.
$testname = get_string('info_test1_name', 'qtype_wq');
$solutionlink = 'http://www.wiris.com/quizzes/download';
$output = '';

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));

$plugin = new stdClass();
require_once($CFG->dirroot . '/question/type/wq/version.php');
if (isset($plugin->release)) {
    $version = $plugin->release;
    $reporttext = get_string('info_test1_rt1', 'qtype_wq') . $version;
    $condition = true;
} else if ($plugin->maturity == MATURITY_BETA) {
    $version = $plugin->version;
    $reporttext = get_string('info_test1_rt1', 'qtype_wq') . $version;
    $condition = true;
} else {
    $reporttext = get_string('info_test1_rt2', 'qtype_wq');
    $condition = false;
}
$output .= wrs_createTableRow($testname, $reporttext, $solutionlink, $condition);
$output .= html_writer::end_tag('tr');
echo $output;
// END TEST 1.

// BEGIN TEST 2.
$testname = get_string('info_test2_name', 'qtype_wq');
$solutionlink = 'http://www.wiris.com/plugins/moodle/download';
$output = '';

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));

require_once($CFG->dirroot . '/filter/wiris/version.php');
$plugininfo = get_string('info_test2_info', 'qtype_wq') . '<a href="../../../filter/wiris/info.php" target="_blank">' .
    get_string('info_test2_infopage', 'qtype_wq') . '</a>';
if (isset($plugin->release)) {
    $version = $plugin->release;
    if ($version >= '3.17.20') {
        $reporttext = get_string('info_test2_rt1', 'qtype_wq') . ' ' . $plugininfo;
        $condition = true;
    } else {
        $reporttext = get_string('info_test2_rt2', 'qtype_wq') . ' ' . $version . ' ' . $plugininfo;
        $condition = false;
    }
} else if ($plugin->maturity == MATURITY_BETA) {
    $reporttext = get_string('info_test2_rt1', 'qtype_wq') . ' ' . $plugininfo;
    $condition = true;
} else {
    $reporttext = get_string('info_test2_rt3', 'qtype_wq') . ' ' . $plugininfo;
    $condition = false;
}
$output .= wrs_createTableRow($testname, $reporttext, $solutionlink, $condition);
$output .= html_writer::end_tag('tr');
echo $output;
// END TEST 2.

// BEGIN TEST 3.
$testname = get_string('info_test3_name', 'qtype_wq');
$solutionlink = null;
$output = '';

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));

if (isset($version)) {
    if ($version >= '2011060313') {
        $reporttext = get_string('info_test3_rt1', 'qtype_wq');
        $condition = true;
    } else {
        $reporttext = sprintf(get_string('info_test3_rt2', 'qtype_wq'), $version);
        $condition = false;
    }
} else {
    $reporttext = get_string('info_test3_rt3', 'qtype_wq');
    $condition = false;
}
$output .= wrs_createTableRow($testname, $reporttext, $solutionlink, $condition);
$output .= html_writer::end_tag('tr');
echo $output;
// END TEST 3.

// BEGIN TEST 4.
$testname = get_string('info_test4_name', 'qtype_wq');
$solutionlink = null;
$output = '';

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));

$expectedplugins = array(
    'TrueFalse' => array(
        'name' => get_string('info_test4_pluginname1', 'qtype_wq'),
        'path' => $CFG->dirroot . '/question/type/truefalsewiris',
        'url' => 'https://moodle.org/plugins/qtype_truefalsewiris'
    ),
    'ShortAnswer' => array(
        'name' => get_string('info_test4_pluginname2', 'qtype_wq'),
        'path' => $CFG->dirroot . '/question/type/shortanswerwiris',
        'url' => 'https://moodle.org/plugins/qtype_shortanswerwiris'
    ),
    'MultiAnswer' => array(
        'name' => get_string('info_test4_pluginname3', 'qtype_wq'),
        'path' => $CFG->dirroot . '/question/type/multianswerwiris',
        'url' => 'https://moodle.org/plugins/qtype_multianswerwiris'
    ),
    'MultipleChoice' => array(
        'name' => get_string('info_test4_pluginname4', 'qtype_wq'),
        'path' => $CFG->dirroot . '/question/type/multichoicewiris',
        'url' => 'https://moodle.org/plugins/qtype_multichoicewiris'
    ),
    'Matching' => array(
        'name' => get_string('info_test4_pluginname5', 'qtype_wq'),
        'path' => $CFG->dirroot . '/question/type/matchwiris',
        'url' => 'https://moodle.org/plugins/qtype_matchwiris'
    ),
    'Essay' => array(
        'name' => get_string('info_test4_pluginname6', 'qtype_wq'),
        'path' => $CFG->dirroot . '/question/type/essaywiris',
        'url' => 'https://moodle.org/plugins/qtype_essaywiris'
    ),
    'WQ' => array(
        'name' => get_string('info_test4_pluginname7', 'qtype_wq'),
        'path' => $CFG->dirroot . '/question/type/wq',
        'url' => 'https://moodle.org/plugins/qtype_shortanswerwiris'
    )
);
$missingplugins = array();
$installedplugins = array();

foreach ($expectedplugins as $key => $plugin) {
    if (! empty($plugin['path']) ) {
        if (! is_dir($plugin['path']) ) {
            $missingplugins[$key] = $plugin;
        } else {
            $installedplugins[$key] = $plugin;
        }
    }
}

$reporttext = '';
if ( ! empty($installedplugins) ) {
    $reporttext .= get_string('info_test4_rt1', 'qtype_wq');
    $reporttext .= html_writer::start_tag('ul', array('class' => 'wrs_filter wrs_plugin'));
    foreach ($installedplugins as $key => $plugin) {
        $reporttext .= html_writer::start_tag('li');
        $reporttext .= $plugin['name'];
        $reporttext .= html_writer::end_tag('li');
    }
    $reporttext .= html_writer::end_tag('ul');
}

if ( ! empty($missingplugins) ) {
    $reporttext .= get_string('info_test4_rt2', 'qtype_wq');
    $reporttext .= html_writer::start_tag('ul', array('class' => 'wrs_filter wrs_plugin'));
    foreach ($missingplugins as $key => $plugin) {
        $reporttext .= html_writer::start_tag('li');
        $reporttext .= $plugin['name'] . ' ';
        $reporttext .= html_writer::start_tag('a', array('href' => $plugin['url']));
        $reporttext .= get_string('info_test4_rt3', 'qtype_wq');
        $reporttext .= html_writer::end_tag('a');
        $reporttext .= html_writer::end_tag('li');
    }
    $reporttext .= html_writer::end_tag('ul');
}
$output .= wrs_createTableRow($testname, $reporttext, $solutionlink, true);
$output .= html_writer::end_tag('tr');
echo $output;
// END TEST 4.

// BEGIN TEST 5.
$testname = get_string('info_test5_name', 'qtype_wq');
$solutionlink = 'http://www.wiris.com/quizzes/download';
$output = '';

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));

$dbman = $DB->get_manager();
$tables = array(
        'qtype_wq'
);
$ok = true;
foreach ($tables as $table) {
    if ($ok) {
        if (!$dbman->table_exists($table)) {
                $ok = false;
        }
    }
}
if ($ok) {
    $condition = true;
    $reporttext = get_string('info_test5_rt1', 'qtype_wq');
} else {
    $condition = false;
    $reporttext = get_string('info_test5_rt2', 'qtype_wq');
}
$output .= wrs_createTableRow($testname, $reporttext, $solutionlink, $condition);
$output .= html_writer::end_tag('tr');
echo $output;
// END TEST 5.

// BEGIN TEST 6.
$testname = get_string('info_test6_name', 'qtype_wq');
$solutionlink = null;
$output = '';

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));

$quizzesdisabled = get_config('question', 'wq_disabled');
if ($quizzesdisabled) {
    $reporttext = get_string('info_disabled', 'qtype_wq');
} else {
    $reporttext = get_string('info_enabled', 'qtype_wq');
}
$output .= wrs_createTableRow($testname, $reporttext, $solutionlink, !$quizzesdisabled);
$output .= html_writer::end_tag('tr');
echo $output;
// END TEST 6.

$output .= html_writer::end_tag('table');
$output .= html_writer::start_tag('br');
echo $output;

$output = '';
$output .= html_writer::start_tag('p', array('class' => 'wrs_filter wrs_plugin'));
$output .= html_writer::end_tag('br');
$output .= html_writer::start_tag('span', array('class' => 'wrs_filter wrs_plugin',
    'style' => 'font-size:14px; font-weight:normal;'));
$output .= get_string('info_information', 'qtype_wq');
$output .= " (<a href=\"mailto:support@wiris.com\">support@wiris.com</a>)";
$output .= html_writer::end_tag('span');
$output .= html_writer::end_tag('p');
echo $output;
