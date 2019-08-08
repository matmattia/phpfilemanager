<?php
class Lang{
	private static $lang;
	private static $values = array();
	private static $default_values = array();
	
	private static function init(){
		if(!is_string(self::$lang)){
			$default_lang = Config::getDefault('lang');
			if(isset($_SESSION['l']) && self::checkLang($_SESSION['l'])){
				self::$lang = $_SESSION['l'];
			} else {
				self::$lang = Config::get('lang');
				if(!self::checkLang(self::$lang)){
					self::$lang = $default_lang;
				}
			}
			$base_path = LANG_PATH.'{LANG}.php';
			$path = str_replace('{LANG}',$default_lang,$base_path);
			if(file_exists($path)){
				include_once($path);
				if(!isset($lang) || !is_array($lang)){
					$lang = array();
				}
				self::$default_values = $lang;
				unset($lang);
			}
			unset($path);
			if(self::$lang==$default_lang){
				self::$values = self::$default_values;
			} else {
				$path = str_replace('{LANG}',self::$lang,$base_path);
				if(file_exists($path)){
					include_once($path);
				}
				unset($path);
				if(isset($lang) && is_array($lang)){
					self::$values = $lang;
				}
			}
			unset($base_path);
		}
	}
	
	/**
	 * Restituisce il valore di un testo
	 * @access public
	 * @static
	 * @param string $t testo da tradurre
	 * @return string
	 */
	public static function get($t) {
		self::init();
		if (is_string($t) && trim($t) !== '') {
			if (isset(self::$values[$t]) && is_string(self::$values[$t])) {
				$t = self::$values[$t];
			} else if (isset(self::$default_values[$t]) && is_string(self::$default_values[$t])) {
				$t = self::$default_values[$t];
			}
		} else {
			$t = '';
		}
		return $t;
	}
	
	/**
	 * Verifica che una lingua esista
	 * @access private
	 * @static
	 * @param string $l lingua
	 * @return boolean
	 */
	private static function checkLang($l){
		return is_string($l) && trim($l) !== '' && is_file(LANG_PATH.File::cleanName($l).'.php');
	}
}