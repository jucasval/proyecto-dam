<?php
// logout.php — en la raiz del proyecto /proyecto/
session_start();
session_destroy();
header('Location: login.php');
exit;
