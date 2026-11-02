<?php
require __DIR__ . '/conexiones/conexionPDO.php';

$_SESSION = [];
session_destroy();

header('Location: index.php');
exit;
?>