<?php
include_once __DIR__ . "/../config/config.php";
include_once __DIR__ . "/auth_check.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?php echo SITE_NAME; ?></title>

<!-- Google Font -->
<link rel="stylesheet"
href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">

<!-- Font Awesome -->
<link rel="stylesheet"
href="<?php echo BASE_URL; ?>/plugins/fontawesome-free/css/all.min.css">

<!-- AdminLTE -->
<link rel="stylesheet"
href="<?php echo BASE_URL; ?>/dist/css/adminlte.min.css">

<!-- Custom CSS -->
<link rel="stylesheet"
href="<?php echo BASE_URL; ?>/assets/css/style.css">

</head>

<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">

<?php include "topbar.php"; ?>

<?php include "sidebar.php"; ?>