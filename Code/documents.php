<?php

    require_once "functions.php";
    if (!isLoggedIn()) {
        header("Location: login.php");
        die();  
    }
    $title = "Преглед на документи";
    $active = "documents";
    require_once "template/header.php";

?>

    <section>
        <header id="documents-header">
            <p></p>
            <h2>Документи</h2>
            <section class="sorting">
                <label for="sort"> Сортирай по: </label>
                <section class="selectBox">
                    <select id="sort">
                        <option value="status" selected>Статус</option>
                        <option value="id">Входящ номер</option>
                        <option value="uploaded">Дата на качване</option>
                        <option value="name">Име</option>
                        <?php if (isAdmin()) echo '<option value="dep.name">Отдел</option>'; ?>
                    </select>
                    <select id="sortType">
                        <option value="ASC" selected>Възходящ ред</option>
                        <option value="DESC">Низходящ ред</option>
                    </select>
                </section>
            </section>
        </header>

        <table id="docs">
            <thead>
                <tr>
                    <th>Номер</th>
                    <th>Дата на качване</th>
                    <th>Отдел</th>
                    <th>Име</th>
                    <th>Статус</th>
                    <th>Операции</th>
                </tr>
            </thead>
            <tbody id="tbody">
            </tbody>
        </table>
    </section>

<?php

    require_once 'template/footer.php';

?>