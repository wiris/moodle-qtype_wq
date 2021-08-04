<?php

class com_wiris_util_json_parser_JType extends Enum {
	public static $ARRAY;
	public static $CONSTANT;
	public static $HEURISTIC;
	public static $NAME;
	public static $NUMBER;
	public static $OBJECT;
	public static $STRING;
	public static $__constructors = array(0 => 'ARRAY', 6 => 'CONSTANT', 2 => 'HEURISTIC', 3 => 'NAME', 5 => 'NUMBER', 1 => 'OBJECT', 4 => 'STRING');
	}
com_wiris_util_json_parser_JType::$ARRAY = new com_wiris_util_json_parser_JType("ARRAY", 0);
com_wiris_util_json_parser_JType::$CONSTANT = new com_wiris_util_json_parser_JType("CONSTANT", 6);
com_wiris_util_json_parser_JType::$HEURISTIC = new com_wiris_util_json_parser_JType("HEURISTIC", 2);
com_wiris_util_json_parser_JType::$NAME = new com_wiris_util_json_parser_JType("NAME", 3);
com_wiris_util_json_parser_JType::$NUMBER = new com_wiris_util_json_parser_JType("NUMBER", 5);
com_wiris_util_json_parser_JType::$OBJECT = new com_wiris_util_json_parser_JType("OBJECT", 1);
com_wiris_util_json_parser_JType::$STRING = new com_wiris_util_json_parser_JType("STRING", 4);
