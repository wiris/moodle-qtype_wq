<?php

class com_wiris_quizzes_telemetry_QuizzesTracker extends com_wiris_util_telemetry_Tracker {
	public function __construct($service) { if(!php_Boot::$skip_constructor) {
		parent::__construct($service);
	}}
	public function sendInformation($topic, $parameters) {
		$index = com_wiris_quizzes_telemetry_QuizzesTracker::getIndex($topic);
		if($index >= 0) {
			$allowedParameters = com_wiris_quizzes_telemetry_QuizzesTracker::getParameters();
			$this->filterParameters($parameters, $allowedParameters);
			$this->sendInformationImpl($index, $parameters);
		}
	}
	static $TOPIC_KEY = "topic";
	static $AUXILIARY_TEXT_INPUT_KEY = "auxiliary-text-input";
	static $AUXILIARY_TEXT_KEY = "auxiliary-text";
	static $CAS_SESSION_KEY = "cas-session";
	static $CAS_KEY = "cas";
	static $CAS_FALSE_VALUE = "false";
	static $CAS_ADD_VALUE = "add";
	static $CAS_REPLACE_VALUE = "replace";
	static $SYNTAX_KEY = "syntax";
	static $SYNTAX_MATH_VALUE = "math";
	static $SYNTAX_TEXT_VALUE = "text";
	static $SYNTAX_GRAPHIC_VALUE = "graphic";
	static $LOCAL_DATA_ALLOWED_KEYS;
	static $BOOLEAN_INPUTS;
	static $parameters;
	static function getParameters() {
		if(com_wiris_quizzes_telemetry_QuizzesTracker::$parameters === null) {
			com_wiris_quizzes_telemetry_QuizzesTracker::$parameters = new Hash();
			com_wiris_quizzes_telemetry_QuizzesTracker::$parameters->set(com_wiris_quizzes_telemetry_QuizzesTracker::$TOPIC_KEY, new _hx_array(array()));
			com_wiris_quizzes_telemetry_QuizzesTracker::$parameters->set(com_wiris_quizzes_telemetry_QuizzesTracker::$AUXILIARY_TEXT_INPUT_KEY, com_wiris_quizzes_telemetry_QuizzesTracker::$BOOLEAN_INPUTS);
			com_wiris_quizzes_telemetry_QuizzesTracker::$parameters->set(com_wiris_quizzes_telemetry_QuizzesTracker::$AUXILIARY_TEXT_KEY, com_wiris_quizzes_telemetry_QuizzesTracker::$BOOLEAN_INPUTS);
			com_wiris_quizzes_telemetry_QuizzesTracker::$parameters->set(com_wiris_quizzes_telemetry_QuizzesTracker::$CAS_SESSION_KEY, com_wiris_quizzes_telemetry_QuizzesTracker::$BOOLEAN_INPUTS);
			com_wiris_quizzes_telemetry_QuizzesTracker::$parameters->set(com_wiris_quizzes_telemetry_QuizzesTracker::$CAS_KEY, new _hx_array(array(com_wiris_quizzes_telemetry_QuizzesTracker::$CAS_FALSE_VALUE, com_wiris_quizzes_telemetry_QuizzesTracker::$CAS_ADD_VALUE, com_wiris_quizzes_telemetry_QuizzesTracker::$CAS_REPLACE_VALUE)));
			com_wiris_quizzes_telemetry_QuizzesTracker::$parameters->set(com_wiris_quizzes_telemetry_QuizzesTracker::$SYNTAX_KEY, new _hx_array(array(com_wiris_quizzes_telemetry_QuizzesTracker::$SYNTAX_MATH_VALUE, com_wiris_quizzes_telemetry_QuizzesTracker::$SYNTAX_TEXT_VALUE, com_wiris_quizzes_telemetry_QuizzesTracker::$SYNTAX_GRAPHIC_VALUE)));
		}
		return com_wiris_quizzes_telemetry_QuizzesTracker::$parameters;
	}
	static function getIndex($topic) {
		if($topic === com_wiris_quizzes_telemetry_QuizzesTrackingTopic::$STUDIO_SAVE) {
			return 1;
		}
		return -1;
	}
	function __toString() { return 'com.wiris.quizzes.telemetry.QuizzesTracker'; }
}
com_wiris_quizzes_telemetry_QuizzesTracker::$LOCAL_DATA_ALLOWED_KEYS = new _hx_array(array(com_wiris_quizzes_telemetry_QuizzesTracker::$AUXILIARY_TEXT_INPUT_KEY, com_wiris_quizzes_telemetry_QuizzesTracker::$CAS_KEY, com_wiris_quizzes_telemetry_QuizzesTracker::$SYNTAX_KEY));
com_wiris_quizzes_telemetry_QuizzesTracker::$BOOLEAN_INPUTS = new _hx_array(array("false", "true"));
