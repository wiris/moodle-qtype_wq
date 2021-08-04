<?php

class com_wiris_util_json_parser_JsonParse {
	public function __construct() { 
	}
	static $ALLOW_SINGLE_QUOTES = true;
	static function parse($jsonString) {
		$stateStack = new _hx_array(array());
		$currentJType = null;
		$expectingComma = false;
		$expectingColon = false;
		$fieldStart = 0;
		$singleQuoteString = false;
		$end = strlen($jsonString) - 1;
		$i = 0;
		$propertyName = null;
		$currentContainer = null;
		$value = null;
		$current = null;
		try {
			while(com_wiris_util_json_parser_JsonParse::isWhitespace($current = haxe_Utf8::charCodeAt($jsonString, $i))) {
				$i++;
			}
		}catch(Exception $»e) {
			$_ex_ = ($»e instanceof HException) ? $»e->e : $»e;
			$e = $_ex_;
			{
				throw new HException(com_wiris_util_json_parser_JsonParseException::newFromMessage("Provided JSON string did not contain a value"));
			}
		}
		if($current === 123) {
			$currentJType = com_wiris_util_json_parser_JType::$OBJECT;
			$currentContainer = new Hash();
			$i++;
		} else {
			if($current === 91) {
				$currentJType = com_wiris_util_json_parser_JType::$hARRAY;
				$currentContainer = new _hx_array(array());
				$propertyName = null;
				$i++;
			} else {
				if($current === 34 || com_wiris_util_json_parser_JsonParse::$ALLOW_SINGLE_QUOTES && $current === 39) {
					$currentJType = com_wiris_util_json_parser_JType::$STRING;
					$singleQuoteString = $current === 39;
					$fieldStart = $i;
				} else {
					if(com_wiris_util_json_parser_JsonParse::isLetter($current)) {
						$currentJType = com_wiris_util_json_parser_JType::$CONSTANT;
						$fieldStart = $i;
					} else {
						if(com_wiris_util_json_parser_JsonParse::isNumberStart($current)) {
							$currentJType = com_wiris_util_json_parser_JType::$NUMBER;
							$fieldStart = $i;
						} else {
							throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "Unexpected character \"" . _hx_string_rec($current, "") . "\" instead of root value"));
						}
					}
				}
			}
		}
		while($i <= $end) {
			$current = haxe_Utf8::charCodeAt($jsonString, $i);
			if($currentJType === com_wiris_util_json_parser_JType::$NAME) {
				try {
					$extracted = com_wiris_util_json_parser_JsonParse::extractString($jsonString, $i, $singleQuoteString);
					$i = $extracted->sourceEnd;
					$propertyName = $extracted->str;
					$singleQuoteString = false;
					unset($extracted);
				}catch(Exception $»e) {
					$_ex_ = ($»e instanceof HException) ? $»e->e : $»e;
					$e2 = $_ex_;
					{
						throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "String did not have ending quote"));
					}
				}
				$currentJType = com_wiris_util_json_parser_JType::$HEURISTIC;
				$expectingColon = true;
				$i++;
				unset($e2);
			} else {
				if($currentJType === com_wiris_util_json_parser_JType::$STRING) {
					try {
						$extracted = com_wiris_util_json_parser_JsonParse::extractString($jsonString, $i, $singleQuoteString);
						$i = $extracted->sourceEnd;
						$value = $extracted->str;
						$singleQuoteString = false;
						unset($extracted);
					}catch(Exception $»e) {
						$_ex_ = ($»e instanceof HException) ? $»e->e : $»e;
						$e2 = $_ex_;
						{
							throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "String did not have ending quote"));
						}
					}
					if($currentContainer === null) {
						return $value;
					} else {
						$expectingComma = true;
						if(com_wiris_system_TypeTools::isHash($currentContainer)) {
							$currentContainer->set($propertyName, $value);
							$currentJType = com_wiris_util_json_parser_JType::$OBJECT;
						} else {
							$currentContainer->push($value);
							$currentJType = com_wiris_util_json_parser_JType::$hARRAY;
						}
					}
					$i++;
					unset($e2);
				} else {
					if($currentJType === com_wiris_util_json_parser_JType::$NUMBER) {
						$withDecimal = false;
						$withE = false;
						do {
							$current = haxe_Utf8::charCodeAt($jsonString, $i);
							if(!$withDecimal && $current === 46) {
								$withDecimal = true;
							} else {
								if(!$withE && ($current === 101 || $current === 69)) {
									$withE = true;
								} else {
									if(!com_wiris_util_json_parser_JsonParse::isNumberStart($current) && $current !== 43) {
										break;
									}
								}
							}
						} while($i++ < $end);
						$valueString = _hx_substr($jsonString, $fieldStart, $i - $fieldStart);
						try {
							if($withDecimal || $withE) {
								$value = Std::parseFloat($valueString);
							} else {
								$value = Std::parseInt($valueString);
							}
						}catch(Exception $»e) {
							$_ex_ = ($»e instanceof HException) ? $»e->e : $»e;
							$e2 = $_ex_;
							{
								throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "\"" . $valueString . "\" expected to be a number, but wasn't"));
							}
						}
						if($currentContainer === null) {
							return $value;
						} else {
							$expectingComma = true;
							if(com_wiris_system_TypeTools::isHash($currentContainer)) {
								$currentContainer->set($propertyName, $value);
								$currentJType = com_wiris_util_json_parser_JType::$OBJECT;
							} else {
								$currentContainer->push($value);
								$currentJType = com_wiris_util_json_parser_JType::$hARRAY;
							}
						}
						unset($withE,$withDecimal,$valueString,$e2);
					} else {
						if($currentJType === com_wiris_util_json_parser_JType::$CONSTANT) {
							while(com_wiris_util_json_parser_JsonParse::isLetter($current) && $i++ < $end) {
								$current = haxe_Utf8::charCodeAt($jsonString, $i);
							}
							$valueString = _hx_substr($jsonString, $fieldStart, $i - $fieldStart);
							if("false" === $valueString) {
								$value = false;
							} else {
								if("true" === $valueString) {
									$value = true;
								} else {
									if("null" === $valueString) {
										$value = null;
									} else {
										if(com_wiris_system_TypeTools::isHash($currentContainer)) {
											$stateStack->push(new com_wiris_util_json_parser_State($propertyName, $currentContainer, com_wiris_util_json_parser_JType::$OBJECT));
										} else {
											if(com_wiris_system_TypeTools::isArray($currentContainer)) {
												$stateStack->push(new com_wiris_util_json_parser_State($propertyName, $currentContainer, com_wiris_util_json_parser_JType::$hARRAY));
											}
										}
										throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "\"" . $valueString . "\" is not a valid constant. Missing quotes?"));
									}
								}
							}
							if($currentContainer === null) {
								return $value;
							} else {
								$expectingComma = true;
								if(com_wiris_system_TypeTools::isHash($currentContainer)) {
									$currentContainer->set($propertyName, $value);
									$currentJType = com_wiris_util_json_parser_JType::$OBJECT;
								} else {
									$currentContainer->push($value);
									$currentJType = com_wiris_util_json_parser_JType::$hARRAY;
								}
							}
							unset($valueString);
						} else {
							if($currentJType === com_wiris_util_json_parser_JType::$HEURISTIC) {
								while(com_wiris_util_json_parser_JsonParse::isWhitespace($current) && $i++ < $end) {
									$current = haxe_Utf8::charCodeAt($jsonString, $i);
								}
								if($current !== 58 && $expectingColon) {
									$stateStack->push(new com_wiris_util_json_parser_State($propertyName, $currentContainer, com_wiris_util_json_parser_JType::$OBJECT));
									throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "wasn't followed by a colon"));
								}
								if($current === 58) {
									if($expectingColon) {
										$expectingColon = false;
										$i++;
									} else {
										$stateStack->push(new com_wiris_util_json_parser_State($propertyName, $currentContainer, com_wiris_util_json_parser_JType::$OBJECT));
										throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "was followed by too many colons"));
									}
								} else {
									if($current === 34 || com_wiris_util_json_parser_JsonParse::$ALLOW_SINGLE_QUOTES && $current === 39) {
										$currentJType = com_wiris_util_json_parser_JType::$STRING;
										$singleQuoteString = $current === 39;
										$fieldStart = $i;
									} else {
										if($current === 123) {
											$stateStack->push(new com_wiris_util_json_parser_State($propertyName, $currentContainer, com_wiris_util_json_parser_JType::$OBJECT));
											$currentJType = com_wiris_util_json_parser_JType::$OBJECT;
											$currentContainer = new Hash();
											$i++;
										} else {
											if($current === 91) {
												$stateStack->push(new com_wiris_util_json_parser_State($propertyName, $currentContainer, com_wiris_util_json_parser_JType::$OBJECT));
												$currentJType = com_wiris_util_json_parser_JType::$hARRAY;
												$currentContainer = new _hx_array(array());
												$i++;
											} else {
												if(com_wiris_util_json_parser_JsonParse::isLetter($current)) {
													$currentJType = com_wiris_util_json_parser_JType::$CONSTANT;
													$fieldStart = $i;
												} else {
													if(com_wiris_util_json_parser_JsonParse::isNumberStart($current)) {
														$currentJType = com_wiris_util_json_parser_JType::$NUMBER;
														$fieldStart = $i;
													} else {
														throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "unexpected character \"" . _hx_string_rec($current, "") . "\" instead of object value"));
													}
												}
											}
										}
									}
								}
							} else {
								if($currentJType === com_wiris_util_json_parser_JType::$OBJECT) {
									while(com_wiris_util_json_parser_JsonParse::isWhitespace($current) && $i++ < $end) {
										$current = haxe_Utf8::charCodeAt($jsonString, $i);
									}
									if($current === 44) {
										if($expectingComma) {
											$expectingComma = false;
											$i++;
										} else {
											$stateStack->push(new com_wiris_util_json_parser_State($propertyName, $currentContainer, com_wiris_util_json_parser_JType::$OBJECT));
											throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "followed by too many commas"));
										}
									} else {
										if($current === 34 || com_wiris_util_json_parser_JsonParse::$ALLOW_SINGLE_QUOTES && $current === 39) {
											if($expectingComma) {
												$stateStack->push(new com_wiris_util_json_parser_State($propertyName, $currentContainer, com_wiris_util_json_parser_JType::$OBJECT));
												throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "wasn't followed by a comma"));
											}
											$currentJType = com_wiris_util_json_parser_JType::$NAME;
											$singleQuoteString = $current === 39;
											$fieldStart = $i;
										} else {
											if($current === 125) {
												if($stateStack->length > 0) {
													$upper = $stateStack->pop();
													$upperContainer = $upper->container;
													$parentName = $upper->propertyName;
													$currentJType = $upper->type;
													if(com_wiris_system_TypeTools::isHash($upperContainer)) {
														$upperContainer->set($parentName, $currentContainer);
													} else {
														$upperContainer->push($currentContainer);
													}
													$currentContainer = $upperContainer;
													$expectingComma = true;
													$i++;
													unset($upperContainer,$upper,$parentName);
												} else {
													return $currentContainer;
												}
											} else {
												if(!com_wiris_util_json_parser_JsonParse::isWhitespace($current)) {
													throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "unexpected character '" . _hx_string_rec($current, "") . "' where a property name is expected. Missing quotes?"));
												}
											}
										}
									}
								} else {
									if($currentJType === com_wiris_util_json_parser_JType::$hARRAY) {
										while(com_wiris_util_json_parser_JsonParse::isWhitespace($current) && $i++ < $end) {
											$current = haxe_Utf8::charCodeAt($jsonString, $i);
										}
										if($current !== 44 && $current !== 93 && $current !== 125 && $expectingComma) {
											$stateStack->push(new com_wiris_util_json_parser_State(null, $currentContainer, com_wiris_util_json_parser_JType::$hARRAY));
											throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "wasn't preceded by a comma"));
										}
										if($current === 44) {
											if($expectingComma) {
												$expectingComma = false;
												$i++;
											} else {
												$stateStack->push(new com_wiris_util_json_parser_State(null, $currentContainer, com_wiris_util_json_parser_JType::$hARRAY));
												throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "preceded by too many commas"));
											}
										} else {
											if($current === 34 || com_wiris_util_json_parser_JsonParse::$ALLOW_SINGLE_QUOTES && $current === 39) {
												$currentJType = com_wiris_util_json_parser_JType::$STRING;
												$singleQuoteString = $current === 39;
												$fieldStart = $i;
											} else {
												if($current === 123) {
													$stateStack->push(new com_wiris_util_json_parser_State(null, $currentContainer, com_wiris_util_json_parser_JType::$hARRAY));
													$currentJType = com_wiris_util_json_parser_JType::$OBJECT;
													$currentContainer = new Hash();
													$i++;
												} else {
													if($current === 91) {
														$stateStack->push(new com_wiris_util_json_parser_State(null, $currentContainer, com_wiris_util_json_parser_JType::$hARRAY));
														$currentJType = com_wiris_util_json_parser_JType::$hARRAY;
														$currentContainer = new _hx_array(array());
														$i++;
													} else {
														if($current === 93) {
															if($stateStack->length > 0) {
																$upper = $stateStack->pop();
																$upperContainer = $upper->container;
																$parentName = $upper->propertyName;
																$currentJType = $upper->type;
																if(com_wiris_system_TypeTools::isHash($upperContainer)) {
																	$upperContainer->set($parentName, $currentContainer);
																} else {
																	$upperContainer->push($currentContainer);
																}
																$currentContainer = $upperContainer;
																$expectingComma = true;
																$i++;
																unset($upperContainer,$upper,$parentName);
															} else {
																return $currentContainer;
															}
														} else {
															if(com_wiris_util_json_parser_JsonParse::isLetter($current)) {
																$currentJType = com_wiris_util_json_parser_JType::$CONSTANT;
																$fieldStart = $i;
															} else {
																if(com_wiris_util_json_parser_JsonParse::isNumberStart($current)) {
																	$currentJType = com_wiris_util_json_parser_JType::$NUMBER;
																	$fieldStart = $i;
																} else {
																	$stateStack->push(new com_wiris_util_json_parser_State($propertyName, $currentContainer, com_wiris_util_json_parser_JType::$hARRAY));
																	throw new HException(com_wiris_util_json_parser_JsonParseException::newFromStack($stateStack, "Unexpected character \"" . _hx_string_rec($current, "") . "\" instead of array value"));
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		throw new HException(com_wiris_util_json_parser_JsonParseException::newFromMessage("Root element wasn't terminated correctly (Missing ']' or '}'?)"));
	}
	static function extractString($jsonString, $fieldStart, $singleQuote) {
		$builder = new StringBuf();
		$ret = null;
		while(true) {
			$i = com_wiris_util_json_parser_JsonParse::indexOfSpecial($jsonString, $fieldStart, $singleQuote);
			$c = haxe_Utf8::charCodeAt($jsonString, $i);
			if(!$singleQuote && $c === 34 || $singleQuote && $c === 39) {
				$builder->add(_hx_substr($jsonString, $fieldStart + 1, $i - $fieldStart - 1));
				$ret = new com_wiris_util_json_parser_ExtractedString($i, $builder->b);
				break;
			} else {
				if($c === 92) {
					$builder->add(_hx_substr($jsonString, $fieldStart + 1, $i - $fieldStart - 1));
					$c = haxe_Utf8::charCodeAt($jsonString, $i + 1);
					if($c === 34) {
						$builder->b .= chr(34);
					} else {
						if($c === 92) {
							$builder->b .= chr(92);
						} else {
							if($c === 47) {
								$builder->b .= chr(47);
							} else {
								if($c === 110) {
									$builder->b .= chr(10);
								} else {
									if($c === 114) {
										$builder->b .= chr(13);
									} else {
										if($c === 116) {
											$builder->b .= chr(9);
										} else {
											if($c === 117) {
												$builder->add(com_wiris_util_json_parser_JsonParse_0($builder, $c, $fieldStart, $i, $jsonString, $ret, $singleQuote));
												$fieldStart = $i + 5;
												continue;
											}
										}
									}
								}
							}
						}
					}
					$fieldStart = $i + 1;
				} else {
					throw new HException("Index out of bounds");
				}
			}
			unset($i,$c);
		}
		return $ret;
	}
	static function indexOfSpecial($str, $start, $singleQuote) {
		$i = $start;
		while(++$i < strlen($str)) {
			$c = haxe_Utf8::charCodeAt($str, $i);
			if(!$singleQuote && $c === 34 || com_wiris_util_json_parser_JsonParse::$ALLOW_SINGLE_QUOTES && $singleQuote && $c === 39 || $c === 92) {
				break;
			}
			unset($c);
		}
		return $i;
	}
	static function isWhitespace($c) {
		return $c === 32 || $c === 9 || $c === 10 || $c === 13;
	}
	static function isLetter($c) {
		return $c >= 97 && $c <= 122;
	}
	static function isNumberStart($c) {
		return $c >= 48 && $c <= 57 || $c === 45;
	}
	function __toString() { return 'com.wiris.util.json.parser.JsonParse'; }
}
function com_wiris_util_json_parser_JsonParse_0(&$builder, &$c, &$fieldStart, &$i, &$jsonString, &$ret, &$singleQuote) {
	{
		$s = new haxe_Utf8(null);
		$s->addChar(Std::parseInt("0x" . _hx_substr($jsonString, $i + 2, 4)));
		return $s->toString();
	}
}
