<?php

    require_once 'phpqrcode.php'; 

    $code = htmlspecialchars($_GET['code']);
    // QRCode::png("https://" . $_SERVER['HTTP_HOST'] . "/track.php?code=" . $code);
    QRCode::png("https://" . $_SERVER['HTTP_HOST'] . preg_replace("/\/template/", "", substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'],'/'))) . "/track.php?code=" . $code);
    
?>