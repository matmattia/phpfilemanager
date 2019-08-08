<?php
class File {
	/**
	 * Percorso del file
	 * @access protected
	 * @var string
	 */
	protected $path;
	
	/**
	 * Costruttore della classe
	 * @access public
	 * @param string $path percorso del file
	 */
	public function __construct($path) {
		if (is_string($path) && trim($path) !== '' && is_file($path)) {
			$this->path = realpath($path);
		} else {
			throw new Exception('Wrong file path');
		}
	}
	
	/**
	 * Restituisce l'oggetto corretto in base al tipo di file
	 * @access public
	 * @static
	 * @param string $path percorso del file
	 * @return File
	 */
	public static function getObject($path) {
		if (is_string($path) && trim($path) !== '' && file_exists($path)) {
			if (is_dir($path)) {
				return new Dir($path);
			} else {
				return new File($path);
			}
		}
		throw new Exception('Wrong path');
	}
	
	/**
	 * Restituisce il percorso completo del file
	 * @access public
	 * @return string
	 */
	public function getFullPath() {
		return $this->path;
	}
	
	/**
	 * Restituisce il percorso relativo alla cartella principale
	 * @access public
	 * @return string
	 */
	public function getPath() {
		$path = $this->getFullPath();
		$dir = new Dir(Config::get('directory'));
		$dir_path = $dir->getFullPath();
		unset($dir);
		$dir_path_l = strlen($dir_path);
		if (strlen($path) > $dir_path_l  && substr($path, 0, $dir_path_l) == $dir_path) {
			$path = substr($path, $dir_path_l);
		}
		unset($dir_path, $dir_path_l);
		return $path;
	}
	
	/**
	 * Restituisce il nome del file
	 * @access public
	 * @return string
	 */
	public function getName() {
		return basename($this->getFullPath());
	}
	
	/**
	 * Restituisce il percorso della cartella in cui si trova il file
	 * @access public
	 * @return string
	 */
	public function getDirPath() {
		return dirname($this->getFullPath()).DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Restituisce il nome della cartella in cui si trova il file
	 * @access public
	 * @return string
	 */
	public function getDirName() {
		return basename(dirname($this->getFullPath()));
	}
	
	/**
	 * Restituisce la dimensione del file
	 * @access public
	 * @return integer
	 */
	public function getSize() {
		return filesize($this->getFullPath());
	}
	
	/**
	 * Stampa la dimensione del file
	 * @access public
	 * @return string
	 */
	public function printSize() {
		return self::printFilesize($this->getSize());
	}
	
	/**
	 * Stampa la dimensione specificata
	 * @access public
	 * @static
	 * @param integer $size dimensione del file
	 * @return string
	 */
	public static function printFilesize($size) {
		$size = is_numeric($size) && $size > 0 ? intval($size) : 0;
		$u = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$i = 0;
		$d = 1024;
		while ($d <= $size && isset($u[$i])) {
			$d *= 1024;
			$i++;
		}
		return round($size / $d * 1024).' '.$u[$i];
	}
	
	/**
	 * Pulisce il nome di un file da caratteri pericolosi
	 * @access public
	 * @static
	 * @param string $filename nome del file
	 * @return string
	 */
	public static function cleanName($filename) {
		return is_string($filename) ? preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $filename) : '';
	}
}