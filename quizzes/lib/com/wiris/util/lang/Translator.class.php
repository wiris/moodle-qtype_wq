<?php

class com_wiris_util_lang_Translator implements com_wiris_util_lang_WordsListener{
	public function __construct($lang, $strings) {
		if(!php_Boot::$skip_constructor) {
		$this->lang = $lang;
		$this->strings = $strings;
	}}
	public function keys() {
		return $this->strings->keys();
	}
	public function isEmpty() {
		return !$this->strings->keys()->hasNext();
	}
	public function getLang() {
		return $this->lang;
	}
	public function t($code) {
		if($code !== null && $this->strings->exists($code)) {
			$code = $this->strings->get($code);
		}
		return $code;
	}
	public function languageLoaded($words) {
		$keys = $words->getTypes($this->lang);
		while($keys->hasNext()) {
			$type = $keys->next();
			if($this->types === null || com_wiris_util_type_Arrays::containsArray($this->types, $type)) {
				com_wiris_util_lang_Translator::addAll($words->getWords($this->lang, $type), $this->strings);
			}
			unset($type);
		}
	}
	public $types;
	public $lang;
	public $strings;
	public function __call($m, $a) {
		if(isset($this->$m) && is_callable($this->$m))
			return call_user_func_array($this->$m, $a);
		else if(isset($this->»dynamics[$m]) && is_callable($this->»dynamics[$m]))
			return call_user_func_array($this->»dynamics[$m], $a);
		else if('toString' == $m)
			return $this->__toString();
		else
			throw new HException('Unable to call «'.$m.'»');
	}
	static function newTranslatorFromArray($lang, $source) {
		$strings = new Hash();
		$i = 0;
		while($i < $source->length && !($source[$i][0][0][0] === "lang" && $source[$i][0][0][1] === $lang)) {
			$i++;
		}
		if($i < $source->length) {
			$langarrays = $source[$i];
			{
				$_g1 = 0; $_g = $langarrays->length;
				while($_g1 < $_g) {
					$i1 = $_g1++;
					$langarray = $langarrays[$i1];
					$j = null;
					{
						$_g3 = 0; $_g2 = $langarray->length;
						while($_g3 < $_g2) {
							$j1 = $_g3++;
							$strings->set($langarray[$j1][0], $langarray[$j1][1]);
							unset($j1);
						}
						unset($_g3,$_g2);
					}
					unset($langarray,$j,$i1);
				}
			}
		}
		return new com_wiris_util_lang_Translator($lang, $strings);
	}
	static function newTranslatorFromWords($lang, $words) {
		return com_wiris_util_lang_Translator::newTranslatorFromWordsWithTypes($lang, $words, null);
	}
	static function newTranslatorFromWordsWithTypes($lang, $words, $types) {
		$strings = new Hash();
		$t = new com_wiris_util_lang_Translator($lang, $strings);
		$t->types = $types;
		$words->loadLanguage($lang, $t);
		return $t;
	}
	static function newTranslatorFromHash($lang, $hash) {
		return new com_wiris_util_lang_Translator($lang, $hash);
	}
	static function addAll($from, $to) {
		$i = $from->keys();
		while($i->hasNext()) {
			$key = $i->next();
			$to->set($key, $from->get($key));
			unset($key);
		}
	}
	function __toString() { return 'com.wiris.util.lang.Translator'; }
}
