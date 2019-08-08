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

	public static function printTpl($file,$vars = array()){
		self::init();
		$o = '';
		if(is_string($file) && trim($file)!=''){
			$file = File::cleanName($file);
			$base_path = THEMES_PATH.'{THEME}'.DIRECTORY_SEPARATOR.$file;
			$path = str_replace('{THEME}',self::$theme,$base_path);
			if(!is_file($path)){
				$path = str_replace('{THEME}',self::$default_theme,$base_path);
			}
			unset($base_path);
			if(is_file($path)){
				if(is_array($vars) && !empty($vars)){
					foreach($vars as $k=>$v){
						$$k = $v;
						unset($k,$v);
					}
				}
				ob_start();
				include($path);
				$o = ob_get_contents();
				ob_end_clean();
			}
			unset($path);
		}
		return $o;
	}
}