<?php

require_once "functions.php";

if (isLoggedIn() && isset($_POST['data'])) {
    $data = json_decode($_POST['data'], true);

    $sortOption = getSortOption(htmlentities($data['sortOption']));
    $orderType = htmlentities($data['orderType']);

    if (isAdmin()) {
        $response = $db->selectAllDocumentsQuery($sortOption, $orderType);
        $response = array_merge($response, array("admin" => true));
    }
    else {
        $response = $db->selectDocumentsByDepartmentIdQuery($_SESSION['user']['id'], $sortOption, $orderType);
        $response = array_merge($response, array("admin" => false));
    }

    echo json_encode($response);
}

function getSortOption($sortOption) {
    if ($sortOption === "id") {
        $sortOption = "doc.id"; 
    }
    else if ($sortOption === "status" ){
        $sortOption = "s.id";
    }
    else if ($sortOption === "uploaded") {
        $sortOption = "doc.uploaded";
    }
    else if ($sortOption === "name") {
        $sortOption = "doc.name"; 
    }

    return $sortOption;
}

?>