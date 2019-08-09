<?php
error_reporting(-1);
require_once('inc/includer.php');

$body = $error = null;

$directory_path = null;
if (isset($_GET['dir']) && is_string($_GET['dir']) && trim($_GET['dir']) !== '') {
	$directory_path = $_GET['dir'];
}
try {
	$dir = new Dir(Config::get('directory').str_replace('..', '', $directory_path));
	if (isset($_GET['upload'])) {
		if (isset($_FILES['file']) && $dir->upload($_FILES['file'])) {
			echo $dir->printFiles();
			exit();
		} else {
			http_response_code(500);
			exit();
		}
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