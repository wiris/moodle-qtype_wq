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

require_once('../../../config.php');
require_once($CFG->dirroot . '/question/type/wq/config.php');
global $DB;

function wrs_assert_simple($condition) {
    if ($condition) {
        return '<span class="ok wrs_filter wrs_plugin">OK</span>';
    } else {
        return '<span class="error wrs_filter wrs_plugin">ERROR</span>';
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
            return '<span class="ok wrs_filter wrs_plugin">OK</span>';
    } else {
            return '<span class="error wrs_filter wrs_plugin">ERROR</span>';
    }
}

function wrs_createtablerow($testname, $reporttext, $solutionlink, $condition) {
    $output = '<td class="wrs_filter wrs_plugin">' . $testname . '</td>';
    $output .= '<td class="wrs_filter wrs_plugin">' . wrs_assert($condition, $reporttext, $solutionlink) . '</td>';
    $output .= '<td class="wrs_filter wrs_plugin">' . wrs_getstatus($condition) . '</td>';
    return $output;
}


$PAGE->set_context(context_system::instance());
$PAGE->set_title('Moodle 2.x WIRIS quizzes test page');
$PAGE->set_url('/wq/info.php', array());
echo $OUTPUT->header();

$output = '';
$output .= html_writer::start_tag('h1');
$output .= "Moodle 2.x WIRIS quizzes test page";
$output .= html_writer::end_tag('h1');

$output .= html_writer::start_tag('table', array('id' => 'wrs_filter_info_table', 'class' => 'wrs_filter wrs_plugin'));

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
$output .= html_writer::start_tag('th', array('class' => 'wrs_filter wrs_plugin'));
$output .= "Test";
$output .= html_writer::end_tag('th');
$output .= html_writer::start_tag('th', array('class' => 'wrs_filter wrs_plugin'));
$output .= "Report";
$output .= html_writer::end_tag('th');
$output .= html_writer::start_tag('th', array('class' => 'wrs_filter wrs_plugin'));
$output .= "Status";
$output .= html_writer::end_tag('th');
$output .= html_writer::end_tag('tr');

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));

echo $output;
$output = '';

$plugin = new stdClass();
require_once('version.php');
$testname = 'WIRIS quizzes version';
if (isset($plugin->release)) {
    $version = $plugin->release;
    $reporttext = 'WIRIS quizzes version is ' . $version;
    $condition = true;
} else {
    $reporttext = 'Impossible to find WIRIS quizzes version.';
    $condition = false;
}
$solutionlink = 'http://www.wiris.com/quizzes/download';
echo wrs_createTableRow($testname, $reporttext, $solutionlink, $condition);

$output .= html_writer::end_tag('tr');
$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

require_once('../../../filter/wiris/version.php');
$testname = 'WIRIS plugin version';
$link2plugininfo = '../../../filter/wiris/info.php';
$plugininfo = 'Check WIRIS plugin <a href="' . $link2plugininfo . '" target="_blank">info page</a>';
if (isset($plugin->release)) {
    $version = $plugin->release;
    if ($version >= '3.17.20') {
        $reporttext = 'WIRIS plugin is properly installed. ' . $plugininfo;
        $condition = true;
    } else {
        $reporttext = 'WIRIS quizzes requires WIRIS plugin 3.17.20 or greater. Your version is '. $version . ' ' . $plugininfo;
        $condition = false;
    }
} else {
    $reporttext = 'Impossible to find WIRIS plugin version file. ' . $plugininfo;
    $condition = false;
}
$solutionlink = 'http://www.wiris.com/plugins/moodle/download';
echo wrs_createTableRow($testname, $reporttext, $solutionlink, $condition);

$output .= html_writer::end_tag('tr');
$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

require_once('../../../version.php');
$testname = 'Moodle version';
if (isset($version)) {
    if ($version >= '2011060313') {
        $reporttext = 'Your moodle version is sufficiently new.';
        $condition = true;
    } else {
        $reporttext = 'Your Moodle version is ' . $version .
        '. WIRIS quizzes could not work correctly with Moodle version prior to 2011060313';
        $condition = false;
    }
} else {
    $reporttext = 'Impossible to find Moodle version file.';
    $condition = false;
}
$solutionlink = '';
echo wrs_createTableRow($testname, $reporttext, $solutionlink, $condition);

$output .= html_writer::end_tag('tr');

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

$testname = 'Files';
global $CFG;
$questiontypefolders = array(
        $CFG->dirroot . '/question/type/essaywiris',
        $CFG->dirroot . '/question/type/matchwiris',
        $CFG->dirroot . '/question/type/truefalsewiris',
        $CFG->dirroot . '/question/type/multianswerwiris',
        $CFG->dirroot . '/question/type/multichoicewiris',
        $CFG->dirroot . '/question/type/shortanswerwiris',
        $CFG->dirroot . '/question/type/wq'
);
$ok = true;
foreach ($questiontypefolders as $folder) {
    if ($ok) {
        if (!is_dir($folder)) {
            $ok = false;
        }
    }
}
if ($ok) {
    $condition = true;
    $reporttext = 'All WIRIS question type folders are present.';
} else {
    $condition = false;
    $reporttext = 'One or more of WIRIS question type folders are missing.';
}
$solutionlink = 'http://www.wiris.com/quizzes/download';
echo wrs_createTableRow($testname, $reporttext, $solutionlink, $condition);

$output .= html_writer::end_tag('tr');

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

global $DB;
$dbman = $DB->get_manager();
$testname = 'Database';
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
    $reporttext = 'All WIRIS tables are present.';
} else {
    $condition = false;
    $reporttext = 'One or more of WIRIS tables are missing.';
}
$solutionlink = 'http://www.wiris.com/quizzes/download';
echo wrs_createTableRow($testname, $reporttext, $solutionlink, $condition);

$output .= html_writer::end_tag('tr');

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

$testname = 'WIRIS quizzes';
$solutionlink = '';
$quizzesdisabled = get_config('question', 'wq_disabled');
if ($quizzesdisabled) {
    $reporttext = 'DISABLED';
} else {
    $reporttext = 'ENABLED';
}
echo wrs_createTableRow($testname, $reporttext, $solutionlink, !$quizzesdisabled);

$output .= html_writer::end_tag('tr');

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

$wrap = com_wiris_system_CallWrapper::getInstance();
$testname = 'Checking WIRIS configuration';
$solutionlink = '';
$wrap->start();
$configuration = com_wiris_quizzes_impl_QuizzesBuilderImpl::getInstance()->getConfiguration();
// @codingStandardsIgnoreStart
$reporttext = 'PROXY_URL: ' . $configuration->get(com_wiris_quizzes_api_ConfigurationKeys::$PROXY_URL) . '<br>';
$reporttext .= 'CACHE_DIR: ' . $configuration->get(com_wiris_quizzes_api_ConfigurationKeys::$CACHE_DIR) . '<br>';
$reporttext .= 'SERVICE_URL: ' . $configuration->get(com_wiris_quizzes_api_ConfigurationKeys::$SERVICE_URL) . '<br>';
// @codingStandardsIgnoreEnd
$wrap->stop();
echo wrs_createTableRow($testname, $reporttext, $solutionlink, true);

$output .= html_writer::end_tag('tr');

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

$wrap = com_wiris_system_CallWrapper::getInstance();
$testname = 'Checking if WIRIS server is reachable';
$solutionlink = '';
$wrap->start();
$configuration = com_wiris_quizzes_impl_QuizzesBuilderImpl::getInstance()->getConfiguration();
// @codingStandardsIgnoreStart
$parsedurl = parse_url($configuration->get(com_wiris_quizzes_api_ConfigurationKeys::$SERVICE_URL));
// @codingStandardsIgnoreEnd
$wrap->stop();
if (!isset($parsedurl['port'])) {
    $parsedurl['port'] = 80;
}
$reporttext = 'Connecting to ' . $parsedurl['host'] . ' at port ' . $parsedurl['port'];
echo wrs_createTableRow($testname, $reporttext, $solutionlink, fsockopen($parsedurl['host'], $parsedurl['port']));

$output .= html_writer::end_tag('tr');

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

$wrap = com_wiris_system_CallWrapper::getInstance();
$testname = 'WIRIS quizzes service';
$solutionlink = '';
$wrap->start();
$configuration = com_wiris_quizzes_impl_QuizzesBuilderImpl::getInstance()->getConfiguration();
// @codingStandardsIgnoreStart
$reporttext = $configuration->get(com_wiris_quizzes_api_ConfigurationKeys::$SERVICE_URL);
// @codingStandardsIgnoreEnd
$wrap->stop();
echo wrs_createTableRow($testname, $reporttext, $solutionlink, true);

$output .= html_writer::end_tag('tr');

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

require_once($CFG->dirroot . '/lib/editor/tinymce/lib.php');
$tinyeditor = new tinymce_texteditor();

$rb = com_wiris_quizzes_api_QuizzesBuilder::getInstance();

$questionxml = '<question><wirisCasSession>&lt;session lang=&quot;en&quot; version=&quot;2.0&quot;' .
                '&gt;&lt;library closed=&quot;false&quot;&gt;&lt;mtext style=&quot;color:#ffc800&quot;' .
                ' xml:lang=&quot;es&quot;&gt;variables&lt;/mtext&gt;&lt;group&gt;&lt;command&gt;&lt;input&gt;' .
                '&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;apply&gt;&lt;' .
                'csymbol definitionURL=&quot;http://www.wiris.com/XML/csymbol&quot;&gt;repeat&lt;/csymbol&gt;' .
                '&lt;mtable&gt;&lt;mtr&gt;&lt;mtd&gt;&lt;mi&gt;a&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mi&gt;' .
                'random&lt;/mi&gt;&lt;mfenced&gt;&lt;mrow&gt;&lt;mo&gt;-&lt;/mo&gt;&lt;mn&gt;7&lt;/mn&gt;&lt;'.
                'mo&gt;,&lt;/mo&gt;&lt;mn&gt;7&lt;/mn&gt;&lt;/mrow&gt;&lt;/mfenced&gt;&lt;/mtd&gt;&lt;/mtr&gt;&lt;' .
                'mtr&gt;&lt;mtd&gt;&lt;mi&gt;b&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mi&gt;random&lt;/mi&gt;&lt;' .
                'mfenced&gt;&lt;mrow&gt;&lt;mo&gt;-&lt;/mo&gt;&lt;mn&gt;7&lt;/mn&gt;&lt;mo&gt;,&lt;/mo&gt;&lt;mn&gt;' .
                '7&lt;/mn&gt;&lt;/mrow&gt;&lt;/mfenced&gt;&lt;/mtd&gt;&lt;/mtr&gt;&lt;/mtable&gt;&lt;mrow&gt;&lt;' .
                'mi&gt;a&lt;/mi&gt;&lt;mo&gt;&amp;ne;&lt;/mo&gt;&lt;mn&gt;0&lt;/mn&gt;&lt;mo&gt;&amp;nbsp;&lt;/mo&gt;' .
                '&lt;mo&gt;&amp;and;&lt;/mo&gt;&lt;mo&gt;&amp;nbsp;&lt;/mo&gt;&lt;mi&gt;b&lt;/mi&gt;&lt;mo&gt;&amp;ne;' .
                '&lt;/mo&gt;&lt;mn&gt;0&lt;/mn&gt;&lt;/mrow&gt;&lt;/apply&gt;&lt;/math&gt;&lt;/input&gt;&lt;/command&gt;' .
                '&lt;command&gt;&lt;input&gt;&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;' .
                'mi&gt;c&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mi&gt;random&lt;/mi&gt;&lt;mfenced&gt;&lt;mrow&gt;' .
                '&lt;mo&gt;-&lt;/mo&gt;&lt;mn&gt;7&lt;/mn&gt;&lt;mo&gt;,&lt;/mo&gt;&lt;mn&gt;7&lt;/mn&gt;&lt;' .
                '/mrow&gt;&lt;/mfenced&gt;&lt;/math&gt;&lt;/input&gt;&lt;/command&gt;&lt;command&gt;&lt;input&gt;' .
                '&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;mi&gt;r&lt;/mi&gt;&lt;mo&gt;' .
                '=&lt;/mo&gt;&lt;mi&gt;line&lt;/mi&gt;&lt;mfenced&gt;&lt;mrow&gt;&lt;mi&gt;a&lt;/mi&gt;&lt;' .
                'mo&gt;*&lt;/mo&gt;&lt;mi&gt;x&lt;/mi&gt;&lt;mo&gt;+&lt;/mo&gt;&lt;mi&gt;b&lt;/mi&gt;&lt;mo&gt;' .
                '*&lt;/mo&gt;&lt;mi&gt;y&lt;/mi&gt;&lt;mo&gt;+&lt;/mo&gt;&lt;mi&gt;c&lt;/mi&gt;&lt;mo&gt;=&lt;' .
                '/mo&gt;&lt;mn&gt;0&lt;/mn&gt;&lt;/mrow&gt;&lt;/mfenced&gt;&lt;/math&gt;&lt;/input&gt;&lt;' .
                '/command&gt;&lt;command&gt;&lt;input&gt;&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;' .
                '&gt;&lt;apply&gt;&lt;csymbol definitionURL=&quot;http://www.wiris.com/XML/csymbol&quot;&gt;repeat&lt;' .
                '/csymbol&gt;&lt;mrow&gt;&lt;mi&gt;p&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mi&gt;point&lt;/mi&gt;&lt;' .
                'mfenced&gt;&lt;mrow&gt;&lt;mi&gt;random&lt;/mi&gt;&lt;mfenced&gt;&lt;mrow&gt;&lt;mo&gt;-&lt;/mo&gt;' .
                '&lt;mn&gt;7&lt;/mn&gt;&lt;mo&gt;,&lt;/mo&gt;&lt;mn&gt;7&lt;/mn&gt;&lt;/mrow&gt;&lt;/mfenced&gt;&lt;mo&gt;' .
                ',&lt;/mo&gt;&lt;mi&gt;random&lt;/mi&gt;&lt;mfenced&gt;&lt;mrow&gt;&lt;mo&gt;-&lt;/mo&gt;&lt;mn&gt;7&lt;/mn&gt;' .
                '&lt;mo&gt;,&lt;/mo&gt;&lt;mn&gt;7&lt;/mn&gt;&lt;/mrow&gt;&lt;/mfenced&gt;&lt;/mrow&gt;&lt;' .
                '/mfenced&gt;&lt;/mrow&gt;&lt;mrow&gt;&lt;mi&gt;p&lt;/mi&gt;&lt;mo&gt;&amp;cap;&lt;/mo&gt;&lt;' .
                'mi&gt;r&lt;/mi&gt;&lt;mo&gt;==&lt;/mo&gt;&lt;mfenced close=&quot;}&quot; open=&quot;{&quot;' .
                '&gt;&lt;mtable align=&quot;center&quot;&gt;&lt;mtr&gt;&lt;mtd/&gt;&lt;/mtr&gt;&lt;/mtable&gt;' .
                '&lt;/mfenced&gt;&lt;/mrow&gt;&lt;/apply&gt;&lt;/math&gt;&lt;/input&gt;&lt;/command&gt;&lt;' .
                'command&gt;&lt;input&gt;&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;' .
                'mi&gt;s&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mi&gt;perpendicular&lt;/mi&gt;&lt;mfenced&gt;&lt;' .
                'mrow&gt;&lt;mi&gt;r&lt;/mi&gt;&lt;mo&gt;,&lt;/mo&gt;&lt;mi&gt;p&lt;/mi&gt;&lt;/mrow&gt;&lt;/mfenced&gt;' .
                '&lt;/math&gt;&lt;/input&gt;&lt;/command&gt;&lt;command&gt;&lt;input&gt;&lt;math xmlns=&quot;' .
                'http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;mi&gt;q&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mi&gt;' .
                'plot&lt;/mi&gt;&lt;mo&gt;(&lt;/mo&gt;&lt;mi&gt;r&lt;/mi&gt;&lt;mo&gt;)&lt;/mo&gt;&lt;/math&gt;&lt;' .
                '/input&gt;&lt;/command&gt;&lt;command&gt;&lt;input&gt;&lt;math xmlns=&quot;' .
                'http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;mi&gt;q&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mi&gt;plot&lt;' .
                '/mi&gt;&lt;mo&gt;(&lt;/mo&gt;&lt;mi&gt;p&lt;/mi&gt;&lt;mo&gt;,&lt;/mo&gt;&lt;mo&gt;{&lt;/mo&gt;' .
                '&lt;mi&gt;label&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mi&gt;p&lt;/mi&gt;&lt;mo&gt;,&lt;/mo&gt;&lt;' .
                'mo&gt;&amp;nbsp;&lt;/mo&gt;&lt;mi&gt;show_label&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mi&gt;true&lt;' .
                '/mi&gt;&lt;mo&gt;}&lt;/mo&gt;&lt;mo&gt;)&lt;/mo&gt;&lt;/math&gt;&lt;/input&gt;&lt;/command&gt;&lt;' .
                '/group&gt;&lt;/library&gt;&lt;group&gt;&lt;command&gt;&lt;input&gt;&lt;' .
                'math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;mi&gt;p&lt;/mi&gt;&lt;/math&gt;&lt;' .
                '/input&gt;&lt;output&gt;&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;' .
                'mfenced&gt;&lt;mrow&gt;&lt;mn&gt;2&lt;/mn&gt;&lt;mo&gt;,&lt;/mo&gt;&lt;mo&gt;-&lt;/mo&gt;&lt;' .
                'mn&gt;5&lt;/mn&gt;&lt;/mrow&gt;&lt;/mfenced&gt;&lt;/math&gt;&lt;/output&gt;&lt;/command&gt;&lt;' .
                'command&gt;&lt;input&gt;&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;' .
                'mi&gt;r&lt;/mi&gt;&lt;/math&gt;&lt;/input&gt;&lt;output&gt;&lt;math xmlns=&quot;' .
                'http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;mi&gt;y&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mn&gt;' .
                '3&lt;/mn&gt;&lt;mo&gt;*&lt;/mo&gt;&lt;mi&gt;x&lt;/mi&gt;&lt;mo&gt;+&lt;/mo&gt;&lt;mfrac&gt;&lt;' .
                'mn&gt;1&lt;/mn&gt;&lt;mn&gt;2&lt;/mn&gt;&lt;/mfrac&gt;&lt;/math&gt;&lt;/output&gt;&lt;/command&gt;' .
                '&lt;command&gt;&lt;input&gt;&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;' .
                '&gt;&lt;mi&gt;s&lt;/mi&gt;&lt;/math&gt;&lt;/input&gt;&lt;output&gt;&lt;math xmlns=&quot;' .
                'http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;mi&gt;y&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mo&gt;' .
                '-&lt;/mo&gt;&lt;mfrac&gt;&lt;mn&gt;1&lt;/mn&gt;&lt;mn&gt;3&lt;/mn&gt;&lt;/mfrac&gt;&lt;mo&gt;' .
                '*&lt;/mo&gt;&lt;mi&gt;x&lt;/mi&gt;&lt;mo&gt;-&lt;/mo&gt;&lt;mfrac&gt;&lt;mn&gt;13&lt;/mn&gt;&lt;' .
                'mn&gt;3&lt;/mn&gt;&lt;/mfrac&gt;&lt;/math&gt;&lt;/output&gt;&lt;/command&gt;&lt;/group&gt;&lt;' .
                'group&gt;&lt;command&gt;&lt;input&gt;&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;' .
                '/&gt;&lt;/input&gt;&lt;/command&gt;&lt;/group&gt;&lt;/session&gt;</wirisCasSession><correctAn' .
                'swers><correctAnswer>#s</correctAnswer></correctAnswers></question>';

$q = $rb->readQuestion($questionxml);
$qi = $rb->newQuestionInstance();

$variables = '#q #r';
$function = '#r';

$vqr = $rb->newVariablesRequest($variables, $q, $qi);
$quizzes = $rb->getQuizzesService();
$vqs = $quizzes->execute($vqr);
$qi->update($vqs);

$function = $qi->expandVariables($function);

global $PAGE;
$context = context_course::instance(SITEID);
$PAGE->set_context($context);
$function = format_text($function, FORMAT_HTML, array('noclean' => true));
$testname = 'Checking WIRIS quizzes functionality (variable)';
$solutionlink = '';
echo wrs_createTableRow($testname, $function, $solutionlink, true);

$output .= html_writer::end_tag('tr');

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

$testname = 'Checking WIRIS quizzes functionality (plot)';
$plot = '#q';
$plot = $qi->expandVariables($plot);
echo wrs_createTableRow($testname, $plot, $solutionlink, true);

$output .= html_writer::end_tag('tr');

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

$testname = 'Max server connections';
$wrap = com_wiris_system_CallWrapper::getInstance();
$wrap->start();
try {
    $p = new com_wiris_quizzes_impl_SharedVariables();
    $p->lockVariable('wiris_maxconnections');
    $data = $p->getVariable('wiris_maxconnections');

    if ($data == null) {
        $e = new Exception("There not exists the file");
        $p->unlockVariable('wiris_maxconnections');
        throw $e;
    }

    $connections = haxe_Unserializer::run($data);
    $stamp = Math::floor(haxe_Timer::stamp());
    $maxconnections = $connections->length;
    $configmaxconnections = com_wiris_quizzes_impl_QuizzesBuilderImpl::
    // @codingStandardsIgnoreStart
    getInstance()->getConfiguration()->get(com_wiris_quizzes_api_ConfigurationKeys::$MAXCONNECTIONS);
    // @codingStandardsIgnoreEnd
    $count = 0;
    $it = $connections->iterator();
    while ($it->hasNext()) {
        $time = $it->next();
        // @codingStandardsIgnoreStart
        if (($stamp - $time <= com_wiris_quizzes_impl_MaxConnectionsHttpImpl::$CONNECTION_TIMEOUT)
        // @codingStandardsIgnoreEnd
                && ($stamp - $time >= 0)) {
            $count++;
        }
    }
    $p->unlockVariable('wiris_maxconnections');
    echo wrs_createTableRow($testname, 'There are currently '
      . $count . ' active concurrent connections out of a maximum of '
      . $configmaxconnections . '. Greatest number of concurrent connections is ' . $maxconnections . '.', '', true);
} catch (Exception $e) {
    echo wrs_createTableRow($testname, 'Error with the maximum connections security system. See details: <br/><pre>' .
                            $e->getMessage() . "<br/><pre>" . $e->getTraceAsString() . '</pre>', '', false);
}
$wrap->stop();

$output .= html_writer::end_tag('tr');

$output .= html_writer::end_tag('table');

$output .= html_writer::start_tag('br');
echo $output;
$output = '';

$output .= html_writer::start_tag('table', array('class' => 'wrs_filter wrs_plugin'));

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));
$output .= html_writer::start_tag('th', array('class' => 'wrs_filter wrs_plugin'));
$output .= "Test";
$output .= html_writer::end_tag('th');
$output .= html_writer::start_tag('th', array('class' => 'wrs_filter wrs_plugin'));
$output .= "Status";
$output .= html_writer::end_tag('th');
$output .= html_writer::end_tag('tr');

$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));

$output .= html_writer::start_tag('td', array('class' => 'wrs_filter wrs_plugin'));
$output .= 'mod_security1';
$output .= html_writer::end_tag('td');

$output .= html_writer::start_tag('td', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

set_error_handler('_hx_error_handler', E_ERROR);
$disabled = true;
@$result = file_get_contents('http://' . $_SERVER['SERVER_NAME'] . '/?test=<>');
if ($result == '') {
    $disabled = false;
}
echo wrs_assert_simple($disabled);

$output .= html_writer::end_tag('td');

$output .= html_writer::end_tag('tr');


$output .= html_writer::start_tag('tr', array('class' => 'wrs_filter wrs_plugin'));

$output .= html_writer::start_tag('td', array('class' => 'wrs_filter wrs_plugin'));
$output .= 'mod_security1';
$output .= html_writer::end_tag('td');

$output .= html_writer::start_tag('td', array('class' => 'wrs_filter wrs_plugin'));
echo $output;
$output = '';

$disabled = true;
@$result = file_get_contents('http://' . $_SERVER['SERVER_NAME'] . '/?test=><');
if ($result == '') {
    $disabled = false;
}
echo wrs_assert_simple($disabled);

$output .= html_writer::end_tag('td');

$output .= html_writer::end_tag('tr');

$output .= html_writer::end_tag('table');
$output .= html_writer::start_tag('p', array('class' => 'wrs_filter wrs_plugin'));
$output .= html_writer::end_tag('br');
$output .= html_writer::start_tag('span', array('class' => 'wrs_filter wrs_plugin',
                                    'style' => 'font-size:14px; font-weight:normal;'));
$output .= "For more information or if you have any doubt contact WIRIS Support:";
$output .= " (<a href=\"mailto:support@wiris.com\">support@wiris.com</a>)";
$output .= html_writer::end_tag('span');
$output .= html_writer::end_tag('p');
