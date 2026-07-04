<?php

include_once __DIR__ . "/../config/config.php";

if(!isset($_SESSION['user_id'])){

    header("Location: ../auth/login.php");
    exit();

}