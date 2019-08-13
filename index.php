<?php
error_reporting(-1);
require_once('inc/includer.php');

$body = $error = null;

if (isset($_REQUEST['operation']) && is_string($_REQUEST['operation']) && trim($_REQUEST['operation']) !== '') {
	$json = array('ok' => 0, 'msg' => array());
	if (isset($_REQUEST['path']) && is_string($_REQUEST['path'])) {
		try {
			$file = File::getObject(Config::get('directory').str_replace('..', '', $_REQUEST['path']));
			if ($file) {
				$res = false;
				$dir = null;
				switch ($_REQUEST['operation']) {
					case 'delete':
						$dir = new Dir($file->getDirPath());
						$res = $file->delete();
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
	echo json_encode($json);
	exit();
}

try {
	$dir = new Dir(Config::get('directory').(isset($_GET['dir']) && is_string($_GET['dir']) && trim($_GET['dir']) !== ''
		? str_replace('..', '', $_GET['dir'])
		: ''));
	$body = $dir->printFiles();
} catch (Exception $e) {
	$error = 'Directory not found.';
}

if ($error) {
	$body = Theme::printTpl('error.php', array('error' => $error));
}

echo Theme::printTpl('page.php', array(
	'lang_values' => Lang::getAll(),
	'body' => $body
));