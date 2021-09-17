<?php

require_once "functions.php";

if (isLoggedIn() && isset($_POST['data'])) {
    $data = json_decode($_POST['data'], true);
    $operation = htmlentities($data['operation']);
    $id = htmlentities($data['id']);

    if ($operation == "change") {
        $status = htmlentities($data['status']);
        
        $response = $db->changeDocumentStatusQuery($id, $status);
        if ($response['success']) {
            $role = (isAdmin()) ? "ADMIN" : "DEPT";
            $response = $db->insertStatusChangeQuery($id, $status, $role);
        }

        echo json_encode($response);
    }
    else if ($operation == "download") {
        $role = (isAdmin()) ? "ADMIN" : "DEPT";
        $response = $db->insertStatusChangeQuery($id, "download", $role);
        
        echo json_encode($response);
    }
    else if ($operation == "archive") {
        if (!isAdmin()) {
            $response = ["success" => false, "error" => "User is not admin!"];
            return $response;
        }

        $archiveStatusId = $db->selectStatusIdByNameQuery("Архивиран")['data'];

        $response = $db->changeDocumentStatusQuery($id, $archiveStatusId);
        if ($response['success']) {
            $response = $db->insertStatusChangeQuery($id, $archiveStatusId, "ADMIN");
        }
        
        echo json_encode($response);
    }
    else if ($operation == "unarchive") {
        if (!isAdmin()) {
            $response = ["success" => false, "error" => "User is not admin!"];
            return $response;
        }

        $completedStatusId = (int)$db->selectStatusIdByNameQuery("Приключен")['data'];

        $response = $db->changeDocumentStatusQuery($id, $completedStatusId);
        if ($response['success']) {
            $response = $db->insertStatusChangeQuery($id, $completedStatusId, "ADMIN");
        }
        
        echo json_encode($response);
    }
}

?>