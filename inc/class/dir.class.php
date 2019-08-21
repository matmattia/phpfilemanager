<?php
/**
 * La classe Dir gestisce le cartelle
 */
class Dir extends File {
	/**
	 * @see File::__construct()
	 */
	public function __construct($path) {
		if (is_string($path) && trim($path) !== '' && is_dir($path)) {
			$this->path = realpath($path).DIRECTORY_SEPARATOR;
		} else {
			throw new Exception('Wrong directory path');
		}
	}
	
	/**
	 * Stabilisce se si tratta della cartella principale
	 * @access public
	 * @return boolean
	 */
	public function isMain() {
		$dir = new Dir(Config::get('directory'));
		return $this->getFullPath() == $dir->getFullPath();
	}
	
	/**
	 * @see File::getFAIconClass()
	 */
	public function getFAIconClass() {
		return 'far fa-folder';
	}
	
	/**
	 * @see File::rename()
	 */
	public function rename($name, &$msg = array()) {
		$res = false;
		if (!is_array($msg)) {
			$msg = array();
		}
		if ($this->isMain()) {
			$msg[] = Lang::get('You can’t rename the main directory.');
		} else {
			$res = parent::rename($name, $msg);
		}
		return $res;
	}
	
	/**
	 * @see File::delete()
	 */
	public function delete(&$msg = array()) {
		$res = false;
		if (!is_array($msg)) {
			$msg = array();
		}
		if ($this->isMain()) {
			$msg[] = Lang::get('You can’t delete the main directory.');
		} else {
			$files = $this->getFiles();
			$counter = count($files);
			for ($i = 0; $i < $counter; $i++) {
				try {
					$file = File::getObject($files[$i]);
					$file->delete();
					unset($file);
				} catch (Exception $e) {
				}
			}
			unset($i, $counter, $files);
			$res = @rmdir($this->getFullPath());
		}
		return $res;
	}
	
	/**
	 * @see File::canDownload()
	*/
	public function canDownload() {
		return false;
	}
	
	/**
	 * Restituisce i file della cartella
	 * @access public
	 * @param array $params parametri vari
	 * @return array
	 */
	public function getFiles($params = array()) {
		if (!is_array($params)) {
			$params = array();
		}
		$files = glob($this->path.'*');
		if (!is_array($files)) {
			$files = array();
		}
		$params['info'] = isset($params['info']) && $params['info'];
		$params['order'] = isset($params['order']) && is_array($params['order']) ? $params['order'] : array();
		if ($params['info']) {
			$order_values = $order_types = array();
			if (isset($params['order']['field']) && is_string($params['order']['field']) && trim($params['order']['field']) !== '') {
				$do_order = true;
				$order_values[$params['order']['field']] = array();
				$order_types[$params['order']['field']] = isset($params['order']['type']) && is_scalar($params['order']['type']) && $params['order']['type'] == SORT_DESC ? SORT_DESC : SORT_ASC;
			} else {
				$do_order = false;
			}
			$counter = count($files);
			for ($i = 0; $i < $counter; $i++) {
				$full_path = realpath($files[$i]);
				$file = File::getObject($files[$i]);
				$files[$i] = array(
					'full_path' => $file->getFullPath(),
					'path' => $file->getPath(),
					'public_path' => $file->getPublicPath(),
					'directory' => $file->getDirPath(),
					'directory_name' => $file->getDirName(),
					'name' => $file->getName(),
					'size' => $file->getSize(),
					'print_size' => $file->printSize(),
					'is_dir' => $file instanceof Dir,
					'fa_icon' => $file->getFAIcon()
				);
				unset($file);
				if (!isset($order_values['is_dir'])) {
					$order_values['is_dir'] = array();
					$order_types['is_dir'] = SORT_DESC;
				}
				$order_values['is_dir'][$i] = $files[$i]['is_dir'] ? 1 : 0;
				if ($do_order) {
					$order_values[$params['order']['field']][$i] = isset($files[$i][$params['order']['field']]) ? $files[$i][$params['order']['field']] : null;
				}
			}
			unset($i, $counter, $do_order);
			if (!empty($order_values)) {
				foreach ($order_values as $k => $v) {
					array_multisort($v, isset($order_types[$k]) ? $order_types[$k] : SORT_ASC, $files);
					unset($k, $v);
				}
			}
			unset($order_values, $order_types);
		} else if (!empty($params['order'])) {
			if (isset($params['order']['type']) && $params['order']['type'] == SORT_DESC) {
				rsort($files);
			} else {
				sort($files);
			}
		}
		return $files;
	}
	
	/**
	 * Stampa l'elenco dei file della cartella
	 * @access public
	 * @param array $params parametri vari
	 * @return string
	 */
	public function printFiles($params = array()) {
		if (!is_array($params)) {
			$params = array();
		}
		$params['info'] = true;
		$files = $this->getFiles($params);
		$breadcrumbs = array();
		$home = array(
			'label' => 'Home',
			'fa_icon' => '<span class="fas fa-home"></span>'
		);
		$path = $this->getPath();
		if ($path === '') {
			$breadcrumbs[] = $home;
		} else {
			do {
				$path = dirname($path);
				if ($path == '.') {
					$path = '';
				}
				array_unshift($breadcrumbs, array_merge(array(
					'href' => 'index.php?dir='.rawurlencode($path),
					'label' => basename($path)
				), $path === '' ? $home : array()));
			} while ($path !== '');
			$breadcrumbs[] = array('label' => $this->getName());
		}
		unset($path, $home);
		return Theme::printTpl('file_list.php', array(
			'path' => $this->getPath(),
			'files' => $files,
			'num_files' => count($files),
			'breadcrumbs' => $breadcrumbs
		));
	}
	
	/**
	 * Carica un file nella cartella
	 * @access public
	 * @param array $file dati del file
	 * @param array $msg messaggi
	 * @return boolean
	 */
	public function upload($file, &$msg = array()) {
		$res = false;
		if (!is_array($msg)) {
			$msg = array();
		}
		if (is_array($file) && isset($file['tmp_name']) && is_string($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
			if (!isset($file['name']) || !is_string($file['name']) || trim($file['name']) === '') {
				$file['name'] = basename($file['tmp_name']);
			}
			$num = 0;
			do {
				if ($num > 0) {
					$ext = strrchr($file['name'], '.');
					if ($ext === false || $ext == $file['name']) {
						$filename = $file['name'].'-'.$num;
					} else {
						$filename = substr($file['name'], 0, -strlen($ext)).'-'.$num.$ext;
					}
					unset($ext);
				} else {
					$filename = $file['name'];
				}
				$path = $this->getFullPath().$filename;
				unset($filename);
				$num++;
			} while (is_file($path));
			unset($num);
			$res = @move_uploaded_file($file['tmp_name'], $path);
			unset($path);
		}
		return $res;
	}
	
	/**
	 * Crea una sottocartella
	 * @access public
	 * @param string $name nome della sottocartella
	 * @param array $msg messaggi
	 * @return boolean
	 */
	public function newDir($name, &$msg = array()) {
		$res = false;
		if (!is_array($msg)) {
			$msg = array();
		}
		$name = self::cleanName($name);
		if ($name === '') {
			$msg[] = Lang::get('You must enter a name.');
		} else {
			$res = @mkdir($this->getFullPath().$name);
		}
		return $res;
	}
}