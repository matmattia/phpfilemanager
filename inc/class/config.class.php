<?php
/**
 * La classe Config gestisce le configurazioni
 */
class Config{
	/**
	 * Configurazioni in uso
	 * @access private
	 * @static
	 * @var array
	 */
	private static $config = array();
	
	/**
	 * Configurazioni di default
	 * @access private
	 * @static
	 * @var array
	 */
	private static $default_config = array();
	
	/**
	 * Inizializza le configurazioni
	 * @access private
	 * @static
	 */
	private static function init(){
		if (!is_array(self::$config) || empty(self::$config)) {
			include(INC_PATH.'default_config.php');
			self::$default_config = $default_config;
			$config = array();
			include(BASE_PATH.'config.php');
			self::$config = isset($config) && is_array($config) ? array_merge($default_config, $config) : $default_config;
		}
	}
	
	/**
	 * Restituisce il valore di una configurazione
	 * @access public
	 * @static
	 * @param string $name nome della configurazione
	 * @return mixed
	 */
	public static function get($name) {
		self::init();
		return self::checkConfigValue($name, is_scalar($name) && isset(self::$config[$name]) ? self::$config[$name] : null);
	}
	
	/**
	 * Restituisce il valore di default di una configurazione
	 * @access public
	 * @static
	 * @param string $name nome della configurazione
	 * @return mixed
	 */
	public static function getDefault($name) {
		self::init();
		return self::checkConfigValue($name, is_scalar($name) && isset(self::$default_config[$name]) ? self::$default_config[$name] : null);
	}
	
	/**
	 * Verifica il valore di una configurazione
	 * @access private
	 * @static
	 * @param string $name nome della configurazione
	 * @param mixed $value valore della configurazione
	 * @return mixed
	 */
	private static function checkConfigValue($name, $value) {
		switch(is_scalar($name) ? $name : null) {
			case 'directory':
				if (is_string($value) && trim($value) !== '' && is_dir($value)) {
					$value = realpath($value).DIRECTORY_SEPARATOR;
				} else {
					$value = null;
				}
			break;
		}
		return $value;
	}
}