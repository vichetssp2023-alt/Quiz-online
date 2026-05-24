<?php
header('Content-Type: application/json');

require_once "../../app/Controllers/ResultController.php";
$controller = new ResultController();

$results = $controller->getStudentResults();

if ($results) {
    echo json_encode([
        "status" => "success",
        "data" => $results
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "រកមិនឃើញទិន្នន័យឡើយ"
    ]);
}