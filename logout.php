<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/auth.php';

session_destroy();
header('Location: ' . BASE_URL . '/login.php');
exit;
