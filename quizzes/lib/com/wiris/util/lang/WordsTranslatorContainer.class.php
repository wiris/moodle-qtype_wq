<?php

class com_wiris_util_lang_WordsTranslatorContainer implements com_wiris_util_lang_TranslatorProvider{
	public function __construct() {
		;
	}
	public function getTranslator($lang) {
		if($this->languages === null) {
			$this->languages = new Hash();
		}
		$lang = $this->getAvailableLang($lang);
		if(!$this->languages->exists($lang)) {
			$translator = com_wiris_util_lang_Translator::newTranslatorFromWordsWithTypes($lang, $this->words, $this->types);
			$this->languages->set($lang, $translator);
		}
		return $this->languages->get($lang);
	}
	public function getAvailableLang($lang) {
		$this->getAvailableLanguages();
		$lang = com_wiris_util_lang_WordsTranslatorContainer::normalizeLangString($lang);
		if(com_wiris_util_type_Arrays::contains($this->languageList, $lang)) {
			return $lang;
		}
		if(com_wiris_util_lang_WordsTranslatorContainer::hasRootLanguage($lang)) {
			$lang = com_wiris_util_lang_WordsTranslatorContainer::getRootLanguage($lang);
			if(com_wiris_util_type_Arrays::contains($this->languageList, $lang)) {
				return $lang;
			}
		}
		$lang = $this->defLang;
		if(com_wiris_util_type_Arrays::contains($this->languageList, $lang)) {
			return $lang;
		}
		throw new HException("Default language " . $this->defLang . " is not available.");
	}
	public function getAvailableLanguages() {
		if($this->languageList === null) {
			$this->languageList = $this->words->getAvailableLanguages();
		}
		return $this->languageList;
	}
	public function setDefLang($defLang) {
		$langs = $this->getAvailableLanguages();
		if(com_wiris_util_type_Arrays::contains($langs, $defLang)) {
			$this->defLang = $defLang;
		} else {
			if(com_wiris_util_type_Arrays::contains($langs, "en")) {
				$this->defLang = "en";
			} else {
				if($langs->length > 0) {
					$this->defLang = $langs[0];
				} else {
					throw new HException("There are no available languages.");
				}
			}
		}
	}
	public $words;
	public $types;
	public $defLang = null;
	public $languageList = null;
	public $languages;
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
	static function newTranslatorContainerFromWords($words, $defLang) {
		return com_wiris_util_lang_WordsTranslatorContainer::newTranslatorContainerFromWordsWithTypes($words, $defLang, null);
	}
	static function newTranslatorContainerFromWordsWithTypes($words, $defLang, $types) {
		$tc = new com_wiris_util_lang_WordsTranslatorContainer();
		$tc->words = $words;
		$tc->setDefLang($defLang);
		$tc->types = $types;
		return $tc;
	}
	static function normalizeLangString($lang) {
		return str_replace("-", "_", strtolower(trim($lang)));
	}
	static function hasRootLanguage($lang) {
		return _hx_index_of($lang, "_", null) !== -1;
	}
	static function getRootLanguage($lang) {
		return _hx_substr($lang, 0, _hx_index_of($lang, "_", null));
	}
	function __toString() { return 'com.wiris.util.lang.WordsTranslatorContainer'; }
}
