<?php
// api/signout.php
require_once '../includes/auth.php';

session_destroy();
header('Location: ' . BASE_URL . 'signin.html');
exit;