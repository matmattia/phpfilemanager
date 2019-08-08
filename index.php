<?php
error_reporting(-1);
require_once('inc/includer.php');

$error = null;

if (isset($_GET['dir']) && is_string($_GET['dir']) && trim($_GET['dir']) !== '') {
	try {
		$dir = new Dir(Config::get('directory').str_replace('..', '', $_GET['dir']));
		$body = $dir->printFiles();
	} catch (Exception $e) {
		$error = 'Directory not found.';
	}
} else {
	$dir = new Dir(Config::get('directory'));
	$body = $dir->printFiles();
}

if ($error) {
	$body = Theme::printTpl('error.php', array('error' => $error));
}

echo Theme::printTpl('page.php', array('body' => $body));