<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="styles/styles.css" rel="stylesheet">
    <link href="fontawesome/css/all.css" rel="stylesheet">
    <link rel="shortcut icon" href="template/icons/favicon.ico" type="image/x-icon">  

    <script defer src="scripts/main.js"></script>
    <?php if ($active == "documents") { ?>
    <script defer src="scripts/documents.js"></script>
    <?php } ?>

    <title><?php echo $title; ?></title>
</head>
<body>
<?php
    if (isset($active) && $active != "view") {
?>
    <header>
        <h1>DOCUMENTS UPLOAD SYSTEM</h1>
    </header>
    <nav>
        <ul>
        <?php 
            if (!isLoggedIn()) {
        ?>
            <li class="<?php if ($active == "home") echo 'active'; ?>"><a href="index.php">
                <img src="template/icons/home.png" />
                <span>Начало</span>
            </a></li>
            <li class="<?php if ($active == "upload") echo 'active'; ?>"><a href="upload.php">
                <img src="template/icons/upload.png" />
                <span>Качване</span>
            </a></li>
            <li class="<?php if ($active == "track") echo 'active'; ?>"><a href="track.php">
                <img src="template/icons/track.png" />
                <span>Проследяване</span>
            </a></li>
            <li class="<?php if ($active == "login") echo 'active'; ?>"><a href="login.php">
                <img src="template/icons/login.png" />
                <span>Вход</span>
            </a></li>
        <?php
            }
            else {
        ?>
            <li class="<?php if ($active == "documents") echo 'active'; ?>"><a href="documents.php">
                <img src="template/icons/documents.png" />
                <span>Документи</span>
            </a></li>
            <li><a href="logout.php">
                <img src="template/icons/logout.png" />
                <span>Изход</span>
            </a></li>
        <?php
            }
        ?>
        </ul>   
    </nav>
<?php
    }
?>
    <main>