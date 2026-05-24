<?php
// ១. កំណត់ Header ដើម្បីឱ្យ Browser ដឹងថាជា JSON
header('Content-Type: application/json');

// ២. ទាញយក Controller មកប្រើ
require_once "../../app/Controllers/QuestionController.php";

// ៣. បង្កើត Object ពី Controller
$controller = new QuestionController();

// ៤. ទទួល quiz_id ពី URL (ឧទាហរណ៍៖ get_questions.php?quiz_id=22)
$quiz_id = $_GET['quiz_id'] ?? null;

if ($quiz_id) {
    $questions = $controller->getQuestionsByQuiz($quiz_id);
    
    if ($questions) {
        echo json_encode([
            "status" => "success",
            "data" => $questions
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "រកមិនឃើញសំណួរសម្រាប់ Quiz នេះឡើយ"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "សូមបញ្ជាក់ quiz_id"
    ]);
}