<?php

    require_once "functions.php";
    $title = "Качване - Система за документи";
    $active = "upload";
    require_once "template/header.php";

    // Check if upload form is submitted
    if (isset($_FILES['document']) && isset($_POST['name']) && 
        isset($_POST['department']) && isset($_POST['description'])) {
        $errors = array();
        $filetype = null;
        $MAX_FILE_SIZE = 52428800;
        $ZIP_TYPES = array("application/zip", "application/x-zip", "application/x-zip-compressed");
        if (empty($_FILES['document']) || empty($_FILES['document']['tmp_name'])) {
            $errors[] = "Моля, изберете файл. Допустими формати са: PDF, ZIP (трябва да съдържа index.html файл).";
        }
        else {
            // Check file size
            if ($_FILES['document']['size'] > $MAX_FILE_SIZE) {
                $errors[] = "Файлът е твърде голям. Допустим размер: 50 MB.";
            }

            // Check file type
            if (mime_content_type($_FILES['document']['tmp_name']) == "application/pdf") {
                $filetype = "pdf";
            }
            else if (in_array(mime_content_type($_FILES['document']['tmp_name']), $ZIP_TYPES)) {
                $filetype = "zip";
            }
            else {
                $errors[] = "Невалиден файлов формат. Допустими формати са: PDF, ZIP (трябва да съдържа index.html файл).";
            }

            // Check file hash (if the file has already been uploaded) 
            if ($db->checkIfDocumentExistsByHashQuery(sha1_file($_FILES['document']['tmp_name']))['exists']) {
                $errors[] = "Този файл вече е качен в системата.";
            }

            if (empty($_POST['name']) || mb_strlen($_POST['name']) > 255) {
                $errors[] = "Моля, въведете име под 255 символа.";
            }

            $departments = $db->selectDepartmentsQuery()['data'];
            if (empty($_POST['department']) || 
                $_POST['department'] < $departments[0]['id'] || 
                $_POST['department'] > $departments[count($departments) - 1]['id']) {
                $errors[] = "Моля, въведете отдел.";
            }

            if (empty($_POST['description'])) {
                $errors[] = "Моля, въведете описание.";
            }

            // Validate the ZIP file, if it is a zip file
            if ($filetype == "zip") {
                $zip = new ZipArchive();
                if ($zip->open($_FILES['document']['tmp_name']) === true) {
                    $indexHtmlExists = false;
                    $unallowedFileExists = false;
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $file = $zip->statIndex($i);
                        if (basename($file['name']) == "index.html") {
                            $indexHtmlExists = true;
                        }
                        $finfo = new finfo(FILEINFO_MIME);
                        $mime = $finfo->buffer($zip->getFromIndex($i));
                        if (!preg_match("/(image\/|audio\/|video\/|text\/html|text\/css|text\/javascript|text\/plain|application\/x-empty).*/", $mime)) {
                            $unallowedFileExists = true;
                            break;
                        }
                    }

                    if (!$indexHtmlExists) {
                        $errors[] = "Вашият ZIP архив трябва да съдържа index.html файл.";
                    }
                    if ($unallowedFileExists) {
                        $errors[] = "Архивът съдържа непозволени файлови формати. Разрешени са: html, css, js, снимки, аудио, видео.";
                    }

                    $zip->close();
                }
                else {
                    $errors[] = "Невалиден ZIP архив.";
                }
            }

            // If there are no errors, continue 
            if (empty($errors)) {
                // Insert part of document in DB
                $id = $db->insertDocumentQuery(htmlentities($_POST['name']), 
                    htmlentities($_POST['description']), htmlentities($_POST['department']))['data'];

                // Generate an access code
                $code = substr(md5(random_int(0, PHP_INT_MAX)), 0, 15);

                // Move files and update DB
                if ($filetype == "pdf") {
                    $filename = "files/file" . (int)$id . ".pdf";
                    move_uploaded_file($_FILES['document']['tmp_name'], $filename);

                    // Update values in database
                    $db->updateDocumentAfterInsertionQuery($id, $code, sha1_file($filename), $filename);
                }
                else if ($filetype == "zip") {
                    mkdir("files/file" . (int)$id, 0755);
                    $zipLocation = "files/file" . (int)$id . "/archive.zip";
                    move_uploaded_file($_FILES['document']['tmp_name'], $zipLocation);

                    $zip = new ZipArchive();
                    if ($zip->open($zipLocation) === true) {
                        $zip->extractTo('files/file' . (int)$id . '/');
                        $zip->close();
                    }

                    $filename = "files/file" . (int)$id . '/index.html';
                    if (file_exists($filename)) {
                        $db->updateDocumentAfterInsertionQuery($id, $code, sha1_file($zipLocation), $filename);
                    }
                }

                // Send an email to the department
                $message = '<h1 style="width: 100%; text-align: center;">';
                $message .= 'Нов входиран документ</h1>';
                $message .= '<h3 style="width: 100%; text-align: left;">';
                $message .= 'Входящ номер: ' . $id . " / " . date("d.m.Y") . '</h3>';
                $message .= '<h3 style="width: 100%; text-align: left;">';
                $message .= 'Име на документа: ' . htmlentities($_POST['name']) . '</h3>';
                $message .= '<h3 style="width: 100%; text-align: left;">Описание:</h3>';
                $message .= '<p>' . htmlentities($_POST['description']) . '</p>';
                $message .= '<a style="color: white; padding: 10px 40px; background-color: #4472c4;';
                $message .= 'font-size: 18px; font-weight: bold; border-radius: 10px; text-decoration: none; text-align: center;';
                $message .= 'display: block; width: 300px; margin: 0 auto;" href="';
                $message .= 'https://'. $_SERVER['HTTP_HOST'];
                $message .= preg_replace("/upload\.php$/", "viewDocument.php?id=" . $id, $_SERVER['REQUEST_URI']);
                $message .= '">Прегледай документа</a>';

                $subject = 'Нов документ с номер ' . $id . ' - Система за документи';
                $to = $db->selectDepartmentEmailByIdQuery(htmlentities($_POST['department']))['data'];

                // Set content-type header for sending HTML email 
                $headers = "MIME-Version: 1.0" . "\r\n"; 
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
                // Additional headers 
                $headers .= 'From: Documents System <noreply@documents.kirilgolov.com>' . "\r\n"; 

                mb_send_mail($to, $subject, $message, $headers);
?>
                <section id='successful-upload'>
                    <section id='left-pane'>
                        <h1>Документът е успешно приет с входящ номер: <mark><?php echo $id . "/" . date("d.m.Y"); ?></mark></h1>
                        <h2>Вашият код за достъп е: <mark><?php echo $code; ?></mark></h2>
                        <p><i class='fas fa-lightbulb'></i> Запазете своят код за достъп или QR кода. Само чрез тях можете да достъпите статуса на своя документ.</p>
                        <p><i class='fas fa-lightbulb'></i> Сканирайте QR кода за лесен достъп до страницата за Проследяване. Кликнете, за да го запазите в PNG формат.</p>
                    </section>
                    <section id='right-pane'>
                        <a href='template/generateqrcode.php?code=<?php echo $code; ?>' download='QRCode.png'><img src='template/generateqrcode.php?code=<?php echo $code; ?>' /></a>
                        <a href='template/generateqrcode.php?code=<?php echo $code; ?>' download='QRCode.png' class='button'>Свали QR код</a>
                    </section>
                </section>
<?php
            }
            else {
                foreach ($errors as $error) {
                    echo "<h3 class='error'>" . $error . "</h3>";
                }
                echo "<a class='button' href='upload.php'>Опитай отново</a>";
            }
        }
    }
    else {
?>

<form action="upload.php" method="POST" enctype="multipart/form-data" id="upload-form">
    <section id="document-section">
        <label for="document">Документ: *</label>
        <p><i class="fas fa-lightbulb"></i>Допустими формати: PDF / ZIP. <i>(ZIP файлът трябва да съдържа index.html файл.)</i></p>
    </section>
    <section class="drop-zone">
        <p class="drop-zone__prompt">Пуснете файл тук или кликнете за избор</p>
        <input type="file" id="document" name="document" accept=".pdf,.zip" class="drop-zone__input" required />
    </section>
    <section class="section-form" id="name-section">
        <label for="name">Име: *</label>
        <input type="text" name="name" id="name" placeholder="Име..." required />
        <p></p>
    </section>
    <section class="section-form" id="department-section">
        <label for="department">Отдел: *</label>
        <select name="department" id="department" required>
        <?php
            $departments = $db->selectDepartmentsQuery()['data'];
            foreach ($departments as $department) {
                echo '<option value="' . $department['id'] . '">' . $department['name'] . '</option>';
            }
        ?>
        </select>
        <p></p>
    </section>
    <label for="description">Описание: *</label>
    <textarea id="description" name="description" placeholder="Въведете описание..." required></textarea>
    <a class="button" id="upload-btn">Качи документ</a>
</form>

<?php
    }

    require_once "template/footer.php";
?>