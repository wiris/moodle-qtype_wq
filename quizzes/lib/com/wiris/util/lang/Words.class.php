<?php

class com_wiris_util_lang_Words {
	public function __construct($resource, $available) {
		if(!php_Boot::$skip_constructor) {
		$this->resource = $resource;
		$this->available = $available;
		$this->init();
	}}
	public function getAvailableLanguages() {
		return $this->available;
	}
	public function getWordsResourceName($lang) {
		return $this->resource . "." . $lang . ".json";
	}
	public function getWords($lang, $key) {
		$words = com_wiris_util_lang_Words::$words->get($this->resource)->get($lang);
		if($words === null) {
			throw new HException("Language \"" . $lang . "\" not loaded.");
		}
		$hash = $words->get($key);
		return $hash;
	}
	public function getTypes($lang) {
		$words = com_wiris_util_lang_Words::$words->get($this->resource)->get($lang);
		if($words === null) {
			throw new HException("Language \"" . $lang . "\" not loaded.");
		}
		return $words->keys();
	}
	public function loadLanguage($lang, $wl) {
		if(!com_wiris_util_lang_Words::$words->get($this->resource)->exists($lang)) {
			$s = com_wiris_system_Storage::newResourceStorage($this->getWordsResourceName($lang));
			if($s->exists()) {
				$json = $s->read();
				$hash = com_wiris_util_json_JSon::decode($json);
				com_wiris_util_lang_Words::$words->get($this->resource)->set($lang, $hash);
			} else {
				throw new HException("Language resource \"" . $lang . "\" not found.");
			}
		}
		$wl->languageLoaded($this);
	}
	public function init() {
		if(!com_wiris_util_lang_Words::$words->exists($this->resource)) {
			com_wiris_util_lang_Words::$words->set($this->resource, new Hash());
		}
	}
	public $available;
	public $resource;
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
	static $words;
	static function thisLock() { $»args = func_get_args(); return call_user_func_array(self::$thisLock, $»args); }
	static $thisLock;
	function __toString() { return 'com.wiris.util.lang.Words'; }
}
com_wiris_util_lang_Words::$words = new Hash();
com_wiris_util_lang_Words::$thisLock = _hx_anonymous(array());
