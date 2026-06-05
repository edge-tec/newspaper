<?php
require_once '../config/config.php';

session_unset();
session_destroy();

header("Location: " . SITE_URL . "/admin/login.php");
exit;
