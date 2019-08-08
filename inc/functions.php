<?php
/**
 * Autoload delle classi
 * @param string $class_name nome della classe
 */
function autoloadClass($class_name) {
	if (is_string($class_name) && trim($class_name) !== '') {
		require_once(CLASS_PATH.'file.class.php');
		$file = CLASS_PATH.strtolower(File::cleanName($class_name)).'.class.php';
		if (is_file($file)) {
			include_once($file);
		}
		unset($file);
	}
}

spl_autoload_register('autoloadClass');

function html($t){
	return htmlentities($t, ENT_QUOTES, 'UTF-8');
}

function __($t){
	return Lang::get($t);
}