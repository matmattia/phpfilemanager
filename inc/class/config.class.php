<?php
class Config{
	private static $config = array();
	private static $default_config = array();
	
	private static function init(){
		if(!is_array(self::$config) || empty(self::$config)){
			include(INC_PATH.'default_config.php');
			self::$default_config = $default_config;
			$config = array();
			include(BASE_PATH.'config.php');
			self::$config = isset($config) && is_array($config) ? array_merge($default_config,$config) : $default_config;
		}
	}

	public static function get($name){
		self::init();
		return self::checkConfigValue($name,isset(self::$config[$name]) ? self::$config[$name] : null);
	}

	public static function getDefault($name){
		self::init();
		return self::checkConfigValue($name,isset(self::$default_config[$name]) ? self::$default_config[$name] : null);
	}
	
	private static function checkConfigValue($name,$value){
		if(is_scalar($name)){
			switch($name){
				case 'directory':
					if(is_string($value) && trim($value)!='' && file_exists($value) && is_dir($value)){
						$value = realpath($value).DIRECTORY_SEPARATOR;
					} else {
						$value = null;
					}
				break;
			}
		}
		return $value;
	}
}