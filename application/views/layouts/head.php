<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$chrome = isset($chrome) ? $chrome : TRUE;
?><!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo html_escape(isset($title) ? $title : 'Copyright Management'); ?></title>
	<link rel="stylesheet" href="<?php echo base_url('assets/css/app.css'); ?>">
</head>
<body>
<?php if ($chrome): ?>
<div class="app-shell">
<?php endif; ?>
