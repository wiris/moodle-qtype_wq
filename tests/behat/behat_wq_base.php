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
 * Methods related to Wiris Quizzes question types.
 * @package    question
 * @subpackage wq
 * @copyright  WIRIS Europe (Maths for more S.L)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode;

class behat_wq_base extends behat_base {

    /**
     * Checks that a response carrying an obsolete incoming SID cannot reset a newer browser session.
     *
     * @Then the WIRIS :service service does not replace an obsolete session cookie
     * @param string $service The resource or echo service to exercise.
     */
    public function the_wiris_service_does_not_replace_an_obsolete_session_cookie($service) {
        global $CFG;

        [$client, $body] = $this->request_wiris_service($service);
        if ($client->errno || $client->info['http_code'] !== 200 || empty($body)) {
            throw new Exception('The WIRIS service did not return a successful response.');
        }
        $cookiename = 'MoodleSession' . $CFG->sessioncookie;
        foreach ($client->get_raw_response() as $header) {
            if (preg_match('/^Set-Cookie:\s*' . preg_quote($cookiename, '/') . '=/i', $header)) {
                throw new Exception('The WIRIS service replaced the Moodle session cookie.');
            }
        }
    }

    /**
     * Confirms that the service still checks the incoming session when access control is enabled.
     *
     * @Then the WIRIS :service service rejects an obsolete session cookie
     * @param string $service The resource or echo service to exercise.
     */
    public function the_wiris_service_rejects_an_obsolete_session_cookie($service) {
        [$client] = $this->request_wiris_service($service);
        $headers = array_change_key_case($client->getResponse(), CASE_LOWER);
        $location = parse_url($headers['location'] ?? '', PHP_URL_PATH);
        if ($client->errno || $client->info['http_code'] !== 303 || !str_ends_with($location ?? '', '/login/index.php')) {
            throw new Exception('The protected WIRIS service did not redirect an obsolete session to login.');
        }
    }

    /**
     * Confirms that filtering response cookies does not discard a valid incoming login.
     *
     * @Then the authenticated WIRIS :service service is available
     * @param string $service The resource or echo service to exercise.
     */
    public function the_authenticated_wiris_service_is_available($service) {
        global $CFG;

        $cookie = $this->getSession()->getCookie('MoodleSession' . $CFG->sessioncookie);
        if (empty($cookie)) {
            throw new Exception('The browser has no authenticated Moodle session cookie.');
        }
        [$client, $body] = $this->request_wiris_service($service, $cookie);
        if ($client->errno || $client->info['http_code'] !== 200 || empty($body)) {
            throw new Exception('The protected WIRIS service did not accept the authenticated session.');
        }
    }

    /**
     * Requests a service with an obsolete SID unless an authenticated SID is supplied.
     *
     * @param string $service The resource or echo service to exercise.
     * @param string|null $cookie A valid session cookie, if testing authenticated access.
     * @return array The HTTP client and response body, without exposing cookie values.
     */
    private function request_wiris_service($service, $cookie = null) {
        global $CFG;

        $params = ['service' => $service];
        if ($service === 'resource') {
            $params['name'] = 'quizzes.js';
        } else if ($service === 'echo') {
            $params['data'] = 'WIRIS session regression';
        } else {
            throw new coding_exception('Unsupported service in the session regression test.');
        }
        $url = new moodle_url($CFG->behat_wwwroot . '/question/type/wq/quizzes/service.php', $params);
        $cookiename = 'MoodleSession' . $CFG->sessioncookie;
        $client = new curl();
        $client->setHeader('Cookie: ' . $cookiename . '=' . ($cookie ?? bin2hex(random_bytes(16))));
        $body = $client->get($url->out(false), [], ['CURLOPT_FOLLOWLOCATION' => false, 'CURLOPT_TIMEOUT' => 30]);
        return [$client, $body];
    }

    /**
     * @Then I choose the question type :questiontypename
     */
    public function i_choose_the_question_type($questiontypename) {
        $this->execute('behat_forms::i_set_the_field_to', array($this->escape($questiontypename), 1));
        $this->execute("behat_general::i_click_on", array('.submitbutton', "css_element"));
    }

    /**
     * Opens the Wiris Quizzes Studio when editing a question.
     *
     * @When I open Wiris Quizzes Studio
     */
    public function i_open_wiris_quizzes_studio() {
        $node = $this->get_text_selector_node(
            'xpath_element',
            "//*[@id='wrsUI_openStudio']"
        );
        $this->ensure_node_is_visible($node);
        $node->click();
    }

    /**
     * Goes back in the Wiris Quizzes Studio interface.
     *
     * @When I go back in Wiris Quizzes Studio
     */
    public function i_go_back_in_wiris_quizzes_studio() {
        $node = $this->get_text_selector_node(
            'xpath_element',
            "//*[@id='wrsUI_quizzesStudioBackButton']"
        );
        $this->ensure_node_is_visible($node);
        $node->click();
    }

    /**
     * Saves Wiris Quizzes Studio.
     *
     * @When I save Wiris Quizzes Studio
     */
    public function i_save_wiris_quizzes_studio() {
        $node = $this->get_text_selector_node(
            'xpath_element',
            "//*[@id='wrsUI_quizzesStudioHomeSaveButton']"
        );
        $this->ensure_node_is_visible($node);
        $node->click();
    }

    /**
     * Opens the n instance of Wiris Quizzes Studio when editing a question.
     *
     * @When I Open Wiris Quizzes Studio Instance :instance
     */
    public function i_open_wiris_quizzes_studio_instance($instance) {
        $node = $this->get_text_selector_node(
            'xpath_element',
            "//*[@id='wrsUI_openStudio_".$instance."']"
        );
        $this->ensure_node_is_visible($node);
        $node->click();
    }

    /**
     * Checks if there is a readonly input.
     *
     * @Then I should have a readonly input
     */
    public function i_should_have_a_readonly_input() {
        $session = $this->getSession();
        $readonly = $session->getPage()->find('css', '.wrsUI_readOnly');
        if (empty($readonly)) {
            $currentUrl = $session->getCurrentUrl();
            throw new Exception("Readonly field not found. Current URL: {$currentUrl}");
        }
    }

    /**
     * @When I add the variable :varname with value :value
     */
    public function i_add_the_variable_with_value($varname, $value) {
        $this->execute('behat_general::i_wait_seconds', 2);
        $this->execute('behat_general::i_type', $varname);
        $this->execute('behat_general::i_type', " = ");
        $this->execute('behat_general::i_type', $value);
        $this->execute('behat_general::i_press_named_key', ['', 'enter']);
    }

    /**
     * @Then Feedback should exist
     */
    public function feedback_should_exist() {
        $session = $this->getSession();
        $feedback = $session->getPage()->find('css', '.feedback');
        if (empty($feedback)) {
            $currentUrl = $session->getCurrentUrl();
            throw new Exception("Feedback element not found. Current URL: {$currentUrl}");
        }
    }

    /**
     * @Then Generalfeedback should exist
     */
    public function generalfeedback_should_exist() {
        $session = $this->getSession();
        $generalfeedback = $session->getPage()->find('css', '.generalfeedback');
        if (empty($generalfeedback)) {
            $currentUrl = $session->getCurrentUrl();
            throw new Exception("General feedback element not found. Current URL: {$currentUrl}");
        }
    }

    /**
     * Clears all the content in a focused field.
     *
     * @When I clear the field
     */
    public function i_clear_the_field() {
        $this->getSession()->executeScript('this.value=""');
    }

    /**
     * Waits for TinyMCE to initialize on the initial quiz attempt load.
     *
     * @Then TinyMCE should be initialized on the first quiz attempt load
     */
    public function tinymce_should_be_initialized_on_the_first_quiz_attempt_load() {
        try {
            $this->spin(
                function($context) {
                    return $context->getSession()->evaluateScript(<<<'JS'
                    return !!(document.querySelector('textarea[name$="_answer"]') &&
                        document.querySelector('.tox-tinymce iframe'));
                    JS
                    );
                },
                false,
                self::get_extended_timeout(),
                new Exception('TinyMCE did not initialize on first attempt.')
            );
        } catch (Exception $exception) {
            $diagnostics = $this->getSession()->evaluateScript(<<<'JS'
            return JSON.stringify({
                readyState: document.readyState,
                tinyType: typeof window.tinymce,
                tinyKeys: window.tinymce ? Object.keys(window.tinymce).slice(0, 20) : [],
                tinyEditors: window.tinymce ? typeof window.tinymce.editors : null,
                tinyInit: window.tinymce ? typeof window.tinymce.init : null,
                upperTiny: typeof window.tinyMCE,
                iframeCount: document.querySelectorAll("iframe").length,
                toxCount: document.querySelectorAll(".tox-tinymce").length,
                serviceEntries: performance.getEntriesByType("resource").filter(function(entry) {
                    return entry.name.indexOf("question/type/wq/quizzes/service.php") >= 0;
                }).map(function(entry) {
                    return {duration: entry.duration, size: entry.decodedBodySize, status: entry.responseStatus};
                }),
                serviceScripts: Array.prototype.filter.call(document.scripts, function(script) {
                    return script.src.indexOf("question/type/wq/quizzes/service.php") >= 0;
                }).map(function(script) { return {async: script.async, readyState: script.readyState}; })
            });
            JS
            );
            throw new Exception('TinyMCE did not initialize on first attempt: ' . $diagnostics, 0, $exception);
        }
    }

    /**
     * Adds existing questions from the question bank to a quiz, resolving each
     * question by name but only matching top-level (parent = 0) questions.
     *
     * Mirrors the core "quiz ... contains the following questions:" step but is
     * safe for Wiris Cloze (multianswerwiris) questions. A Cloze stores its
     * embedded sub-questions as separate question records that copy the parent's
     * name and (on Moodle 4.x) carry their own question_bank_entries rows, so the
     * core step — which looks questions up by name with MUST_EXIST — raises a
     * dml_multiple_records_exception there. Filtering on parent = 0 selects the
     * Cloze itself and keeps the behaviour identical for every other qtype.
     *
     * @Given /^quiz "([^"]*)" contains the following Wiris questions:$/
     */
    public function quiz_contains_the_following_wiris_questions($quizname, TableNode $data) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $quiz = $DB->get_record('quiz', array('name' => $quizname), '*', MUST_EXIST);

        $lastpage = 0;
        foreach ($data->getHash() as $questiondata) {
            if (!array_key_exists('question', $questiondata) || !array_key_exists('page', $questiondata)) {
                throw new \Exception('When adding questions to a quiz, the "question" and "page" columns are required.');
            }

            // Resolve the question by name, restricting to top-level questions so a
            // Cloze does not collide with its identically named sub-questions.
            $sql = 'SELECT q.id AS id, q.qtype AS qtype, qv.version AS version
                      FROM {question} q
                      JOIN {question_versions} qv ON qv.questionid = q.id
                      JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                     WHERE q.name = :name AND q.parent = 0
                  ORDER BY qv.version DESC';
            $records = $DB->get_records_sql($sql, array('name' => $questiondata['question']));
            if (empty($records)) {
                throw new \Exception('Question "' . $questiondata['question'] .
                    '" not found in the question bank.');
            }
            $question = reset($records);

            $page = clean_param($questiondata['page'], PARAM_INT);
            if ($page < $lastpage || $page > $lastpage + 1) {
                throw new \Exception('Invalid page number "' . $questiondata['page'] .
                    '" for question "' . $questiondata['question'] . '".');
            }
            $lastpage = $page;

            if (!array_key_exists('maxmark', $questiondata) || $questiondata['maxmark'] === '') {
                $maxmark = null;
            } else {
                $maxmark = clean_param($questiondata['maxmark'], PARAM_LOCALISEDFLOAT);
            }

            quiz_add_quiz_question($question->id, $quiz, $page, $maxmark);
        }

        // Keep the quiz grade totals consistent, like the core step does.
        if (class_exists('\\mod_quiz\\quiz_settings')) {
            $quizobj = \mod_quiz\quiz_settings::create($quiz->id);
            if (method_exists($quizobj, 'get_grade_calculator')) {
                $quizobj->get_grade_calculator()->recompute_quiz_sumgrades();
            }
        }
    }
}
