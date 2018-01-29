<?php

interface com_wiris_quizzes_api_Configuration {
	function loadFile($file);
	function set($key, $value);
	function get($key);
}
