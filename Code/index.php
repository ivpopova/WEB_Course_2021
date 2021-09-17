<?php

    require_once "functions.php";

    if (isLoggedIn()) {
        header("Location: documents.php");
        die();
    }
    $title = "Начало - Система за документи";
    $active = "home";
    require_once "template/header.php";

?>

<h2>Добре дошли в системата за управление на документи.</h2>
<section id="main-section">
    <a class="button" href="upload.php">Качете документ</a>
    <h3>или</h3>
    <a class="button" href="login.php">Влезте</a>
</section>

<?php

    require_once "template/footer.php";

?>