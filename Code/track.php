<?php

    require_once "functions.php";
    $title = "Проследяване - Система за документи";
    $active = "track";
    require_once "template/header.php";

    if (isset($_GET['code']) && !empty($_GET['code'])) {
        $code = htmlentities($_GET['code']);

        $errors = array();
        $document = $db->selectDocumentByCodeQuery($code);

        if (!$document['exists']) {
            $errors[] = "Грешен код за достъп.";
        }
        else {
            $document = $document['data'];
?>
        <section id="document-info">
            <table>
                <tr><td>Име на документа:</td><td><b><?php echo $document['name']; ?></b></td></tr>
                <tr><td>Входящ номер:</td><td><b><?php echo $document['id'] . " / " . date("d.m.Y", $document['uploaded']); ?></b></td></tr>
                <tr><td>Текущ статус:</td><td><mark style="background-color: #<?php echo $document['color']; ?>;"><?php echo $document['status']; ?></mark></td></tr>
            </table>
            <section id="additional-info">
                <p><b>Документът е отварян:</b><b class="bottom"><?php echo $document['timesOpened']; ?> пъти</b></p>
                <p><b>Първо отворен на:</b><b class="bottom"><?php echo (($document['firstOpened'] == null) ? "-" : date("d.m.Y, H:i:s", $document['firstOpened'])); ?></b></p>
                <p><b>Последно отворен на:</b><b class="bottom"><?php echo (($document['lastOpened'] == null) ? "-" : date("d.m.Y, H:i:s", $document['lastOpened'])); ?></b></p>
            </section>  
        </section>
        <section id="status-changes">
            <h3>Проследяване на документа:</h3>
            <table>
                <thead>
                    <tr>
                        <td>Дата</td>
                        <td>Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo date("d.m.Y, H:i:s", $document['uploaded']); ?></td>
                        <td>Документът е качен в системата.</td>
                    </tr>
                    <?php
                        $statusChanges = $db->selectStatusChangesQuery($document['id'])['data'];
                        foreach ($statusChanges as $change) {
                            echo '<tr><td>' . date("d.m.Y, H:i:s", $change['changed']) . '</td><td>';
                            if ($change['status'] == "download") {
                                echo "Документът е <b>свален</b> от " . (($change['role'] == "ADMIN") ? "администратор" : "отдел") . ".";
                            }
                            else if ($change['status'] == "view") {
                                echo "Документът е <b>прегледан</b> от " . (($change['role'] == "ADMIN") ? "администратор" : "отдел") . ".";
                            }
                            else {
                                echo "Статусът на документа е променен на <mark style='background-color: #" . $change['color'] . ";'>" . $change['statusName'] . "</mark>.";
                            }
                            echo "</td></tr>";
                        }
                    ?>
                </tbody>
        </section>
<?php
        }
        if (!empty($errors)) {
            foreach ($errors as $error) {
?>
            <h3 class="error"><?php echo $error; ?></h3>
<?php
            }
?>
            <a class="button" href="track.php?codeEntered=<?php echo $code; ?>">Опитай отново</a>
<?php
        }
    }
    else {
?>

<form action="track.php" method="GET" id="track-form">
    <label for="code">Код за достъп: *</label>
    <input type="text" name="code" id="code" required value="<?php if (isset($_GET['codeEntered'])) echo htmlentities($_GET['codeEntered']); ?>" placeholder="Код за достъп..." />
    <a class="button" id="track-btn">Проследи</a>
</form>

<?php
    }

    require_once "template/footer.php";
?>