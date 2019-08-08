<?php
class Dir extends File {
	/**
	 * @see File::__construct
	 */
	public function __construct($path) {
		if (is_string($path) && trim($path) !== '' && is_dir($path)) {
			$this->path = realpath($path).DIRECTORY_SEPARATOR;
		} else {
			throw new Exception('Wrong directory path');
		}
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
			if (isset($params['order']['field']) && is_string($params['order']['field']) && trim($params['order']['field']) !== '') {
				$do_order = true;
				$order_values = array();
				$order_type = isset($params['order']['type']) && $params['order']['type'] == SORT_DESC ? SORT_DESC : SORT_ASC;
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
					'directory' => $file->getDirPath(),
					'directory_name' => $file->getDirName(),
					'name' => $file->getName(),
					'size' => $file->getSize(),
					'print_size' => $file->printSize(),
					'is_dir' => $file instanceof Dir
				);
				unset($file);
				if ($do_order) {
					$order_values[$i] = isset($files[$i][$params['order']['field']]) ? $files[$i][$params['order']['field']] : null;
				}
			}
			unset($i, $counter);
			if ($do_order) {
				array_multisort($order_values, $order_type, $files);
				unset($order_values, $order_type);
			}
			unset($do_order);
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
		return Theme::printTpl('file_list.php', array('files' => $files, 'num_files' => count($files)));
	}
}