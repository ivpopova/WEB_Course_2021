<?php

    require_once "functions.php";

    if (!isLoggedIn() || !isset($_GET['id'])) {
        header("Location: documents.php");
        die();
    }

    $id = htmlentities($_GET['id']);
    $documentData = $db->selectDocumentByIdQuery($id);
    if (!$documentData['success'] || !$documentData['exists']) {
        header("Location: documents.php");
        die();
    }
    $documentData = $documentData['data'];
    if (preg_match("/\.pdf$/", $documentData['path'])) {
        $documentData['filetype'] = "pdf";
        $documentData['downloadPath'] = $documentData['path'];
    }
    else if (preg_match("/\.html$/", $documentData['path'])) {
        $documentData['filetype'] = "zip";
        $documentData['downloadPath'] = preg_replace("/index\.html$/", "archive.zip", $documentData['path']);
    }

    $title = "Преглед на документ - Система за документи";
    $active = "view";
    require_once "template/header.php";

    // We need to record the viewing of the document
    $role = "DEPT";
    if (isAdmin()) {
        $role = "ADMIN";
    }
    $db->updateDocumentViewTimesQuery($id, $documentData['timesOpened']);
    $db->insertStatusChangeQuery($id, "view", $role);
?>

<header class="sticky">
    <section>
        <p><b>Входящ номер:</b>&nbsp;<b><?php echo $documentData['id'] . " / " . date("d.m.Y", $documentData['uploaded']); ?></b></p>
        <p class="status"><b>Статус:</b><mark style="background-color: #<?php echo $documentData['color']; ?>;"><?php echo $documentData['status']; ?></mark></p>
    </section>
    <section>
        <p><b>Име на документа:</b>&nbsp;<b><?php echo $documentData['name']; ?></b></p>
        <p><b>Отдел:</b>&nbsp;<b><?php echo $documentData['department']; ?></b></p>
    </section>
    <section>
        <b>Описание:</b><p><?php echo $documentData['description']; ?></p>
    </section>
    <section class="changeStatus">
        <select style="background-color: #<?php echo $documentData['color']; ?>">
            <?php
                $statuses = $db->selectStatusesQuery();
                $statuses = $statuses['data'];
                foreach ($statuses as $status) {
                    echo '<option style="background-color: #';
                    if ($documentData['statusId'] == $status['id']) {
                        echo $status['color'] . ';" value="' . $status['id'] . '" selected>';
                    }
                    else {
                        echo $status['color'] . ';" value="' . $status['id'] . '">';
                    }
                    echo $status['status'] . '</option>';
                }
            ?>
        </select>
        <a class="button" id="change-btn">Смени статуса</a>
    </section>
    <section class="download">
        <a class="button download" href="<?php echo $documentData['downloadPath']; ?>" 
            download="File-<?php echo $documentData['id'] . "-" . date("d-m-Y", $documentData['uploaded']) . "." . $documentData['filetype']; ?>">Изтегли</a>
        <a class="button back"><i class="fas fa-chevron-left"></i>Назад</a>
    </section>
</header>

<section class="embed">
    <?php 
        if ($documentData['filetype'] == "pdf") {
    ?>
        <embed src="<?php echo $documentData['path']; ?>" type="application/pdf" frameBorder="0"></embed>
    <?php 
        }
        else if ($documentData['filetype'] == "zip") {
    ?>
        <iframe src="<?php echo $documentData['path']; ?>" frameBorder="0" sandbox></iframe>
    <?php
        }
    ?>
</section>

<?php

    require_once "template/footer.php";

?>