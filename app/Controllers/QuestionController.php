<?php
require_once "../../config/database.php";

class QuestionController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    public function addQuestion() {
        if (isset($_POST['save_question'])) {
            $quiz_id = $_POST['quiz_id'];
            $text = $_POST['question_text'];
            $a = $_POST['option_a'];
            $b = $_POST['option_b'];
            $c = $_POST['option_c'];
            $d = $_POST['option_d'];
            $correct = $_POST['correct_option'];

            $query = "INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            
            if ($stmt->execute([$quiz_id, $text, $a, $b, $c, $d, $correct])) {
                header("Location: ../Views/teacher/manage_questions.php?quiz_id=$quiz_id&status=success");
                exit();
            }
        }
    }

    // បន្ថែមការ Check ក្នុង function deleteQuestion
    public function deleteQuestion() {
    if (isset($_GET['action']) && $_GET['action'] == 'delete') {
        session_start();
        $id = $_GET['id'];
        $quiz_id = $_GET['quiz_id'];
        $current_teacher_id = $_SESSION['user_id'];

        // ឆែកមើលថា តើសំណួរនេះស្ថិតក្នុង Quiz របស់គ្រូដែលកំពុងលុបមែនអត់?
        $check = $this->db->prepare("SELECT q.id FROM questions q 
                                     JOIN quizzes qz ON q.quiz_id = qz.id 
                                     WHERE q.id = ? AND qz.teacher_id = ?");
        $check->execute([$id, $current_teacher_id]);
        
        if ($check->fetch()) {
            $query = "DELETE FROM questions WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            header("Location: ../Views/teacher/manage_questions.php?quiz_id=$quiz_id&status=deleted");
            exit();
        } else {
            die("អ្នកគ្មានសិទ្ធិលុបសំណួរនេះទេ!");
        }
    }
}
    public function updateQuestion() {
    if (isset($_POST['update_question'])) {
        $id = $_POST['question_id']; // ID របស់សំណួរដែលត្រូវកែ
        $quiz_id = $_POST['quiz_id'];
        $text = $_POST['question_text'];
        $a = $_POST['option_a'];
        $b = $_POST['option_b'];
        $c = $_POST['option_c'];
        $d = $_POST['option_d'];
        $correct = $_POST['correct_option'];

        $query = "UPDATE questions SET 
                    question_text = ?, 
                    option_a = ?, 
                    option_b = ?, 
                    option_c = ?, 
                    option_d = ?, 
                    correct_option = ? 
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        
        if ($stmt->execute([$text, $a, $b, $c, $d, $correct, $id])) {
            header("Location: ../Views/teacher/manage_questions.php?quiz_id=$quiz_id&status=updated");
            exit();
        }
    }
}
public function getQuestionsByQuiz($quiz_id) {
    $query = "SELECT * FROM questions WHERE quiz_id = ? ORDER BY id DESC";
    $stmt = $this->db->prepare($query);
    $stmt->execute([$quiz_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}

// Routing Logic
$controller = new QuestionController();

// សម្រាប់ការរក្សាទុកសំណួរថ្មី
if (isset($_POST['save_question'])) {
    $controller->addQuestion();
}

// សម្រាប់ការលុបសំណួរ
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $controller->deleteQuestion();
}

// សម្រាប់ការកែសម្រួលសំណួរ (បន្ថែមផ្នែកនេះ)
if (isset($_POST['update_question'])) {
    $controller->updateQuestion();
}