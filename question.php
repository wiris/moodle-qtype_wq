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

defined('MOODLE_INTERNAL') || die();

class qtype_wq_question extends question_graded_automatically {
    /**
     * @var question_definition
     *   The base question.
     * **/
    public $base;

    /**
     * @var string
     *  The question definition, in WirisQuizzes XML format.
     */
    public $wirisquestionxml;


    /**
     * @var string
     *  The question instance information, in WirisQuizzes XML format.
     */
    public $wirisquestioninstancexml;

    /**
     * @var int
     * Number of lines of the auxiliar text field.
     * 10 by default.
     */
    public $auxiliartextfieldlines = 10;

    public function __construct(question_definition $base = null) {
        $this->base = $base;
    }
    /**
     * Initializes Wiris Quizzes question calling the service in order to get the value
     * of the variables to render the question.
     *
     * @param question_attempt_step $step
     *   The attempt step.
     * @param int $variant
     *   The random seed to be used in this question.
     * **/
    public function start_attempt(question_attempt_step $step, $variant) {
        global $USER;
        $this->base->start_attempt($step, $variant);

        // Get variables from Wiris Quizzes service.
        $this->wirisquestioninstancexml = $this->create_question_instance($variant);

        $text = $this->all_text_array();
        $response = $this->call_display_service($text);
        $this->wirisquestioninstancexml = $response->{'instance'};

        // Save the result.
        $step->set_qt_var('_qi', $this->wirisquestioninstancexml);
        $step->set_qt_var('_sq', $response->{'studentQuestion'});
    }

    private function create_question_instance($variant) {
        global $USER;
        return "<questionInstance><userData>" .
               "<randomSeed>" . $variant . "</randomSeed>" .
               "<parameters><parameter name=\"user_id\" type=\"text\">" . $USER->id . "</parameter></parameters>" .
               "</userData></questionInstance>";
    }

    /**
     * Initializes a question from an intermediate state. It reads the question
     * instance form the saved XML and updates the plotter image cache if
     * necessary.
     * **/
    public function apply_attempt_state(question_attempt_step $step) {
        $this->base->apply_attempt_state($step);
        // Recover the questioninstance variable saved on start_attempt().
        $xml = $step->get_qt_var('_qi');
        $this->wirisquestioninstancexml = $xml;

        // On manual regrade, xml could change. We can't get xml from qt variable
        // So we need to recompute variables.
        // Each attempt builds on the last (question_attempt_step_read_only) shouldn't recompute variables.
        if ($step->get_state() instanceof question_state_complete && !($step instanceof question_attempt_step_read_only)) {
            $text = $this->all_text_array();
            $response = $this->call_display_service($text);
            $this->wirisquestioninstancexml = $response->{'instance'};
    
            // Save the result.
            $step->set_qt_var('_qi', $this->wirisquestioninstancexml);
        }
    }

    public function get_question_summary() {
        $text = $this->base->get_question_summary();
        return $this->expand_variables_text($text);
    }

    public function get_num_variants() {
        if (strpos($this->wirisquestionxml, 'wirisCasSession') != false) {
            return 65536;
        } else {
            return 1;
        }
    }

    public function get_min_fraction() {
        return $this->base->get_min_fraction();
    }

    public function get_max_fraction() {
        return $this->base->get_max_fraction();
    }

    public function clear_wrong_from_response(array $response) {
        return $this->base->clear_wrong_from_response($response);
    }

    public function get_num_parts_right(array $response) {
        return $this->base->get_num_parts_right($response);
    }

    public function get_expected_data() {
        $expected = $this->base->get_expected_data();
        $expected['_sqi'] = PARAM_RAW_TRIMMED;
        $expected['auxiliar_text'] = question_attempt::PARAM_RAW_FILES;
        $expected['attachments'] = question_attempt::PARAM_FILES;
        return $expected;
    }

    public function get_correct_response() {
        return $this->base->get_correct_response();
    }

    public function prepare_simulated_post_data($simulatedresponse) {
        return $this->base->prepare_simulated_post_data($simulatedresponse);
    }

    public function format_text($text, $format, $qa, $component, $filearea, $itemid, $clean = false) {
        if ($format == FORMAT_PLAIN) {
            $text = $this->base->format_text($text, $format, $qa, $component, $filearea, $itemid, $clean);
            $format = FORMAT_HTML;
        }
        $text = $this->expand_variables($text);
        return $this->base->format_text($text, $format, $qa, $component, $filearea, $itemid, $clean);
    }

    public function expand_variables($text, $type = "html") {
        if (isset($this->wirisquestioninstancexml)) {
            $response = $this->call_display_service(array(array(
                "value" => $text,
                "type" => $type    
            )));
            $text = $response->{'texts'}[0];
        }
        
        return $this->filtercodes_compatibility($text);
    }

    public function call_display_service($statements) {
        $payload = array();
        $payload['question'] = $this->wirisquestionxml;
        $payload['instance'] = $this->wirisquestioninstancexml;
        $payload['texts'] = $statements;
        return $this->call_service($payload, "/display/v1");
    }

    public function call_grade_service($slots) {
        $payload = array();
        $payload['question'] = $this->wirisquestionxml;
        $payload['instance'] = $this->wirisquestioninstancexml;
        $payload['slots'] = $slots;
        return $this->call_service($payload, "/grade/v1");
    }

    private function call_service($payload, $uri) {
        $ch = curl_init();

        $headers = array('Referer: ' . $this->get_referer());

        curl_setopt($ch, CURLOPT_URL, get_config('qtype_wq', 'quizzesapiurl') . $uri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (get_config('qtype_wq', 'debug_mode_enabled')) {
            print_object($payload);
            print_object($response);
        }

        $error = null;
        if ($response === false) {
            $error = "" . curl_error($ch) . curl_errno($ch);
        } else {
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode > 399) {
                $error = "Error with code " . $httpcode . ":\n" . $response;
            }
        }

        if ($error != null) {
            // Handle error.
        }

        curl_close($ch);

        return json_decode($response);
    }

    private function get_referer() {
        global $COURSE, $CFG;
        $query = '';
        if (isset($COURSE->id)) {
            $query .= '?course=' . $COURSE->id;
        }
        if (isset($COURSE->category)) {
            $query .= empty($query) ? '?' : '&';
            $query .= 'category=' . $COURSE->category;
        }
        return $CFG->wwwroot . $query;
    }

    private function filtercodes_compatibility($text) {
        $configfiltercodes = get_config('qtype_wq', 'filtercodes_compatibility');
        if (isset($configfiltercodes) && $configfiltercodes == '1') {
            $text = str_replace('[{', '[[{', $text);
            $text = str_replace('}]', '}]]', $text);
        }
        return $text;
    }

    public function expand_variables_text($text) {
        return $this->expand_variables($text, "text");
    }

    public function expand_variables_mathml($text) {
        return $this->expand_variables($text, "mathml");
    }

    public function html_to_text($text, $format) {
        return $this->base->html_to_text($text, $format);
    }

    public function get_local_data_from_question($name) {
        return $this->get_local_data_impl($this->wirisquestionxml, $name);
    }

    public function get_local_data_from_question_instance($name) {
        return $this->get_local_data_impl($this->wirisquestioninstancexml, $name);
    }

    protected function get_local_data_impl($xml, $name) {
        // Use regexp to match localdata value in the first capture grup
        $localdataregexp = '<localData>.*<data name="' .  $name . '">(.*?)<\/data>.*<\/localData>';

        // Look first for slot-specific localdata.
        if (preg_match('/<slot.*' . $localdataregexp . '.*<\/slot>/s', $xml, $matches)) {
            return $matches[1];
        }
        // If there is no slot specific local data, look for question-wide local data
        if (preg_match('/' . $localdataregexp . '/s', $xml, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'response_auxiliar_text') {
            // Response attachments visible if the question has them.
            return true;
        } else {
            return $this->base->check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
        }
    }

    /**
     * question_response_answer_comparer interface.
     * **/
    public function compare_response_with_answer(array $response, question_answer $answer) {
        return $this->base->compare_response_with_answer($response, $answer);
    }
    public function get_answers() {
        return $this->base->get_answers();
    }
    /**
     * question_manually_gradable interface
     * **/
    public function is_complete_response(array $response) {
        return $this->base->is_complete_response($response);
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        $baseresponse = $this->base->is_same_response($prevresponse, $newresponse);
        $sqicompare = ((empty($newresponse['_sqi']) && empty($prevresponse['_sqi'])) || (!empty($prevresponse['_sqi']) &&
            !empty($newresponse['_sqi']) && $newresponse['_sqi'] == $prevresponse['_sqi']));
        $auxiliarcompare = ((empty($newresponse['auxiliar_text']) && empty($prevresponse['auxiliar_text'])) ||
            (!empty($prevresponse['auxiliar_text']) &&
            !empty($newresponse['auxiliar_text']) && $newresponse['auxiliar_text'] == $prevresponse['auxiliar_text']));
        return $baseresponse && $sqicompare && $auxiliarcompare;

    }

    public function summarise_response(array $response) {
        $text = $this->base->summarise_response($response);
        $text = $this->expand_variables_text($text);
        return $text;
    }

    public function classify_response(array $response) {
        return $this->base->classify_response($response);
    }
    /**
     * question_automatically_gradable interface
     * **/
    public function is_gradable_response(array $response) {
        return $this->base->is_gradable_response($response);
    }

    public function get_validation_error(array $response) {
        return $this->base->get_validation_error($response);
    }

    public function grade_response(array $response) {
        return $this->base->grade_response($response);
    }

    public function get_hint($hintnumber, question_attempt $qa) {
        return $this->base->get_hint($hintnumber, $qa);
    }

    public function get_right_answer_summary() {
        $text = $this->base->get_right_answer_summary();
        return $this->expand_variables_text($text);
    }
    public function format_hint(question_hint $hint, question_attempt $qa) {
        return $this->format_text($hint->hint, $hint->hintformat, $qa,
                'question', 'hint', $hint->id);
    }
    /**
     * interface question_automatically_gradable_with_countback
     * **/
    public function compute_final_grade($responses, $totaltries) {
        return $this->base->compute_final_grade($responses, $totaltries);
    }
    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        return $this->base->make_behaviour($qa, $preferredbehaviour);
    }

    /**
     * Custom interface.
     * **/

    /**
     * @return All the text of the question in a single string so Wiris Quizzes
     * can extract the variable placeholders.
     */
    public function join_all_text() {
        // Question text and general feedback.
        $text = $this->questiontext . ' ' . $this->generalfeedback;
        // Hints.
        foreach ($this->hints as $hint) {
            $text .= ' ' . $hint->hint;
        }

        return $text;
    }

    public function all_text_array() {
        $text = array();
        $text[] = array("value" => $this->questiontext);
        $text[] = array("value" => $this->generalfeedback);
        foreach ($this->hints as $hint) {
            $text[] = array("value" => $hint->hint);
        }
        return $text;
    }

    /**
     * @return String Return all the question text without feedback texts.
     */
    public function join_question_text() {
        $text = $this->questiontext;﻿
        foreach ($this->hints as $hint) {
            $tet .= ' ' . $hint->hint;
        }
        return $text;
    }

    /**
     *
     * @return String Return the general feedback text in a single string so Wiris
     * quizzes can extract the variable placeholders.
     */
    public function join_feedback_text() {
        return $this->generalfeedback;
    }
}
