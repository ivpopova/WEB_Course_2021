<?php

    require_once "functions.php";
    $title = "Вход - Система за документи";
    $active = "login";
    require_once "template/header.php";

    if (isset($_POST['username']) && isset($_POST['password'])) {
        $errors = array();

        if (empty($_POST['username'])) {
            $errors[] = "Моля, въведете потребителско име или имейл адрес.";
        }

        if (empty($_POST['password'])) {
            $errors[] = "Моля, въведете парола.";
        }

        $userdata = $db->selectUserByUsernameQuery(htmlentities($_POST['username']));

        if (!$userdata['exists']) {
            $errors[] = "Грешно потребителско име или имейл адрес.";
        }
        else {
            $userdata = $userdata['data'];

            if (!password_verify(htmlentities($_POST['password']), $userdata['password'])) {
                $errors[] = "Грешна парола.";
            }

            unset($userdata['password']);
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<h3 class='error'>" . $error . "</h3>";
            }

            echo "<a class='button' href='login.php'>Опитай отново</a>";
        }
        else {
            $_SESSION['user'] = $userdata;

            header("Location: documents.php");
            die();
        }
    }
    else {
?>

<form action="login.php" method="POST" id="login-form">
    <label for="username">Потребителско име / Имейл: *</label>
    <input type="text" name="username" id="username" placeholder="Име / имейл..." required />
    <label for="password">Парола: *</label>
    <input type="password" name="password" id="password" placeholder="Парола..." required />
    <a class="button" id="login-btn">Вход</a>
</form>

<?php
    }

    require_once "template/footer.php";
?>