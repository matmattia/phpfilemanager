<?php
/**
 * La classe Page gestisce le pagine
 */
class Page {
	/**
	 * JavaScript da aggiungere alla pagina
	 * @access private
	 * @var array
	 */
	private $javascript = array();
	
	/**
	 * Costruttore della classe
	 */
	public function __construct() {
		$this->addJavaScriptFile('js/scripts.js');
		$this->addJavaScript('phpfilebrowser_lang.texts = '.json_encode(Lang::getAll()).';');
		$this->initOpener();
	}
	
	/**
	 * Verifica che si abbiano i permessi per gestire i file
	 * @access private
	 * @return boolean
	 */
	private function checkAccessKey() {
		$res = true;
		$access_keys = Config::get('access_keys');
		if (!is_array($access_keys)) {
			$access_keys = array($access_keys);
		}
		foreach ($access_keys as $k => $v) {
			if (!is_scalar($v) || trim($v) === '') {
				unset($access_keys[$k]);
			}
			unset($k, $v);
		}
		if (!empty($access_keys)) {
			if (isset($_GET['akey']) && is_scalar($_GET['akey'])) {
				$_SESSION['akey'] = $_GET['akey'];
			} else if (!isset($_SESSION['akey'])) {
				$_SESSION['akey'] = null;
			}
			$res = in_array($_SESSION['akey'], $access_keys);
		}
		unset($access_keys);
		return $res;
	}
	
	/**
	 * Esegue le operazioni iniziali relative a chi ha aperto la pagina
	 * @access private
	 */
	private function initOpener() {
		if (isset($_GET['opener']) && is_string($_GET['opener']) && in_array($_GET['opener'], array('', 'field_id', 'tinymce5'))) {
			$_SESSION['opener'] = $_GET['opener'];
			$_SESSION['opener_params'] = array();
			switch ($_SESSION['opener']) {
				case 'field_id':
					$_SESSION['opener_params'] = array(
						'field_id' => isset($_GET['field_id']) && is_string($_GET['field_id']) ? $_GET['field_id'] : ''
					);
				break;
			}
		} else if (!isset($_SESSION['opener'])) {
			$_SESSION['opener'] = '';
		}
		if (!isset($_SESSION['opener_params'])) {
			$_SESSION['opener_params'] = array();
		}
		$this->addJavaScript('phpfilebrowser.opener = \''.html($_SESSION['opener']).'\';'
			.'phpfilebrowser.opener_params = '.json_encode($_SESSION['opener_params']).';');
	}
	
	/**
	 * Stampa la pagina
	 * @access public
	 * @return string
	 */
	public function __toString() {
		if (isset($_REQUEST['operation']) && is_string($_REQUEST['operation']) && trim($_REQUEST['operation']) !== '') {
			return $this->printOperation();
		}
		
		$body = $error = null;
		
		if ($this->checkAccessKey()) {
			try {
				$dir = new Dir(Config::get('directory').(isset($_GET['dir']) && is_string($_GET['dir']) && trim($_GET['dir']) !== ''
					? str_replace('..', '', $_GET['dir'])
					: ''));
				$body = $dir->printFiles();
			} catch (Exception $e) {
				$error = 'Directory not found.';
			}
		} else {
			$error = 'You don’t have permission to access this page.';
		}

		if ($error) {
			$body = Theme::printTpl('error.php', array('error' => $error));
		}

		$o = Theme::printTpl('page.php', array(
			'body' => $body
		));
		
		if (!empty($this->javascript)) {
			$o = preg_replace('/\<head\>(.+?)\<\/head\>/si', '<head>$1'.implode('', $this->javascript).'</head>', $o);
		}
		
		return $o;
	}
	
	/**
	 * Esegue un'operazione e stampa il risultato
	 * @access private
	 */
	private function printOperation() {
		$json = array('ok' => 0, 'msg' => array());
		if ($this->checkAccessKey()) {
			if (isset($_REQUEST['path']) && is_string($_REQUEST['path'])) {
				try {
					$file = File::getObject(Config::get('directory').str_replace('..', '', $_REQUEST['path']));
					if ($file) {
						$res = false;
						$dir = null;
						switch ($_REQUEST['operation']) {
							case 'check_download':
								if ($file->canDownload()) {
									$res = true;
									$json['path'] = $file->getPath();
								}
							break;
							case 'delete':
								$dir = new Dir($file->getDirPath());
								$res = $file->delete();
							break;
							case 'download':
								$file->download();
							break;
							case 'new_dir':
								$dir = $file;
								$res = $dir->newDir(isset($_REQUEST['name']) ? $_REQUEST['name'] : null, $json['msg']);
							break;
							case 'rename':
								$dir = new Dir($file->getDirPath());
								$res = $file->rename(isset($_REQUEST['name']) ? $_REQUEST['name'] : null, $json['msg']);
							break;
							case 'upload':
								$dir = $file;
								$res = $dir->upload(isset($_FILES['file']) ? $_FILES['file'] : null, $json['msg']);
							break;
						}
						if ($res) {
							$json['ok'] = 1;
							if ($dir) {
								$json['html'] = $dir->printFiles();
							}
						}
						unset($res, $dir);
					}
					unset($file);
				} catch (Exception $e) {
				}
			}
		} else {
			$json['msg'][] = Lang::get('You don’t have permission to perform this operation.');
		}
		return json_encode($json);
	}
	
	/**
	 * Aggiunge del codice JavaScript alla pagina
	 * @access private
	 * @param string $code codice
	 * @return boolean
	 */
	private function addJavaScript($code) {
		$res = false;
		if (is_string($code) && trim($code) !== '') {
			$this->javascript[] = '<script type="text/javascript">'.$code.'</script>';
			$res = true;
		}
		return $res;
	}
	
	/**
	 * Aggiunge un file JavaScript alla pagina
	 * @access private
	 * @param string $file file
	 * @return boolean
	 */
	private function addJavaScriptFile($file) {
		$res = false;
		if (is_string($file) && trim($file) !== '') {
			$this->javascript[] = '<script type="text/javascript" src="'.html($file).'"></script>';
			$res = true;
		}
		return $res;
	}
}