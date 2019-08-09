<?php
class Theme{
	private static $theme;
	private static $default_theme;
	
	private static function init(){
		if(!is_string(self::$theme)){
			self::$default_theme = Config::getDefault('theme');
			$theme = File::cleanName(Config::get('theme'));
			self::$theme = trim($theme)!='' && is_dir(THEMES_PATH.$theme) ? $theme : $this->default_theme;
			unset($theme);
		}
	}
	
	/**
	 * Stampa un template
	 * @access public
	 * @static
	 * @param string $file file del template
	 * @param array $__vars variabili da passare al template
	 * @return string
	 */
	public static function printTpl($file, $__vars = array()) {
		self::init();
		$o = '';
		if (is_string($file) && trim($file) !== '') {
			$file = File::cleanName($file);
			$base_path = THEMES_PATH.'{THEME}'.DIRECTORY_SEPARATOR.$file;
			$___path = str_replace('{THEME}', self::$theme, $base_path);
			if (!is_file($___path)) {
				$___path = str_replace('{THEME}', self::$default_theme, $base_path);
			}
			unset($base_path);
			if (is_file($___path)) {
				if (is_array($__vars) && !empty($__vars)) {
					foreach ($__vars as $__k => $__v) {
						if (!in_array($__k, array('this', '___path', '__vars', '__k', '__v'))) {
							$$__k = $__v;
						}
						unset($__k, $__v);
					}
				}
				ob_start();
				include($___path);
				$o = ob_get_contents();
				ob_end_clean();
			}
			unset($___path);
		}
		return $o;
	}
}