<?php

class com_wiris_quizzes_impl_ui_QuizzesContext {
	public function __construct() {
		if(!php_Boot::$skip_constructor) {
		$this->language = com_wiris_quizzes_impl_ui_QuizzesContext::$DEFAULT_LANG;
		$this->graphLanguage = com_wiris_quizzes_impl_ui_QuizzesContext::$DEFAULT_LANG;
		$this->translatorContainer = com_wiris_util_lang_WordsTranslatorContainer::newTranslatorContainerFromWords(new com_wiris_util_lang_Words("strings_quizzes", $this->getAvailableLanguagesFromList(com_wiris_quizzes_impl_ui_QuizzesContext::$AVAILABLE_LANGS)), com_wiris_quizzes_impl_ui_QuizzesContext::$DEFAULT_LANG);
		$this->graphTranslatorContainer = com_wiris_util_lang_WordsTranslatorContainer::newTranslatorContainerFromWords(new com_wiris_util_lang_Words("strings_graph", $this->getAvailableLanguagesFromList(com_wiris_quizzes_impl_ui_QuizzesContext::$GRAPH_AVAILABLE_LANGS)), com_wiris_quizzes_impl_ui_QuizzesContext::$DEFAULT_LANG);
	}}
	public function buildUrl($url) {
		$hashPosition = _hx_index_of($url, "#", null);
		$postHash = "";
		if($hashPosition !== -1) {
			$postHash = _hx_substr($url, $hashPosition, null);
			$url = _hx_substr($url, 0, $hashPosition);
		}
		return str_replace("\${lang}", "en", $url) . (((_hx_index_of($url, "?", null) > -1) ? "&" : "?")) . com_wiris_quizzes_impl_ui_QuizzesContext::$UTM_SUFFIX . $postHash;
	}
	public function getTranslatorFromLang($lang) {
		if(com_wiris_system_ArrayEx::contains($this->translatorContainer->getAvailableLanguages(), com_wiris_util_lang_WordsTranslatorContainer::normalizeLangString($lang))) {
			return $this->translatorContainer->getTranslator(com_wiris_util_lang_WordsTranslatorContainer::normalizeLangString($lang));
		}
		return $this->translatorContainer->getTranslator(com_wiris_util_lang_WordsTranslatorContainer::normalizeLangString(com_wiris_quizzes_impl_ui_QuizzesContext::$DEFAULT_LANG));
	}
	public function getGraphTranslator() {
		return $this->graphTranslatorContainer->getTranslator($this->graphLanguage);
	}
	public function getTranslator() {
		return $this->translatorContainer->getTranslator($this->language);
	}
	public function setLanguage($language) {
		$this->language = $this->translatorContainer->getAvailableLang($language);
		$this->graphLanguage = $this->graphTranslatorContainer->getAvailableLang($language);
	}
	public function getLanguage() {
		return $this->language;
	}
	public function getAvailableLanguages() {
		return $this->translatorContainer->getAvailableLanguages();
	}
	public function graphT($code) {
		return $this->getGraphTranslator()->t($code);
	}
	public function t($code) {
		return $this->getTranslator()->t($code);
	}
	public function getAvailableLanguagesFromList($list) {
		$available = _hx_explode(",", $list)->copy();
		$i = 0;
		while($i < $available->length) {
			$available[$i] = com_wiris_util_lang_WordsTranslatorContainer::normalizeLangString($available[$i++]);
		}
		return $available;
	}
	public $graphTranslatorContainer;
	public $translatorContainer;
	public $graphLanguage;
	public $language;
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
	static $DEFAULT_LANG = "en";
	static $AVAILABLE_LANGS = "ca,da,de,el,en,es,fr,it,no,nn,pt,pt_br,ru,zh";
	static $GRAPH_AVAILABLE_LANGS = "ca,da,de,el,en,es,fr,it,nl,no,pt";
	static $UTM_SUFFIX = "utm_source=Product&utm_medium=WQStudio&utm_campaign=N/A";
	static $instance;
	static function getInstance() {
		if(com_wiris_quizzes_impl_ui_QuizzesContext::$instance === null) {
			com_wiris_quizzes_impl_ui_QuizzesContext::$instance = new com_wiris_quizzes_impl_ui_QuizzesContext();
		}
		return com_wiris_quizzes_impl_ui_QuizzesContext::$instance;
	}
	function __toString() { return 'com.wiris.quizzes.impl.ui.QuizzesContext'; }
}
