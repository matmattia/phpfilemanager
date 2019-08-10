<?php
error_reporting(-1);
require_once('inc/includer.php');

$body = $error = null;

if (isset($_POST['delete'])) {
	$json = array('ok' => 0);
	try {
		$file = File::getObject(Config::get('directory').str_replace('..', '', $_POST['delete']));
		if ($file) {
			$dir = new Dir($file->getDirPath());
			if ($file->delete()) {
				$json['ok'] = 1;
				$json['html'] = $dir->printFiles();
			}
		}
	} catch (Exception $e) {
	}
	echo json_encode($json);
	exit();
}

$directory_path = null;
if (isset($_GET['dir']) && is_string($_GET['dir']) && trim($_GET['dir']) !== '') {
	$directory_path = $_GET['dir'];
}
try {
	$dir = new Dir(Config::get('directory').str_replace('..', '', $directory_path));
	if (isset($_GET['upload'])) {
		$json = array('ok' => 0);
		if (isset($_FILES['file']) && $dir->upload($_FILES['file'])) {
			$json['ok'] = 1;
			$json['html'] = $dir->printFiles();
		}
		echo json_encode($json);
		exit();
	} else if (isset($_GET['new_dir'])) {
		$json = array('ok' => 0);
		if ($dir->newDir($_GET['new_dir'])) {
			$json['ok'] = 1;
			$json['html'] = $dir->printFiles();
		}
		echo json_encode($json);
		exit();
	}
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