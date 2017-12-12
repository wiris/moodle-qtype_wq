<?php

interface com_wiris_quizzes_api_ui_MathViewer {
	function filter($root);
	function plot($contruction);
	function render($mathml);
}
