<?php
session_start();
// Set timezone to match your local time for accurate open/due dates
date_default_timezone_set('Asia/Phnom Penh'); 

require_once "../../config/database.php";

class QuizController {
    private $db;
    private $targetDir = "../../public/uploads/quizzes/";

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        if (!is_dir($this->targetDir)) { 
            mkdir($this->targetDir, 0777, true); 
        }
    }

    public function createQuiz($data, $file) {
        try {
            $fileName = $this->uploadFile($file);
            
            // Determine status based on current time
            $currentTime = date('Y-m-d H:i:s');
            $initialStatus = 'not_open';
            if ($currentTime >= $data['open_date'] && $currentTime < $data['due_date']) {
                $initialStatus = 'active';
            } elseif ($currentTime >= $data['due_date']) {
                $initialStatus = 'inactive';
            }

            // Added attempts_limit to the query
            $query = "INSERT INTO quizzes (teacher_id, class_id, title, description, duration, attempts_limit, open_date, due_date, image_url, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $params = [
                $_SESSION['user_id'], 
                $data['class_id'], 
                $data['title'], 
                $data['description'], 
                $data['duration'], 
                $data['attempts_limit'], // New column
                $data['open_date'], 
                $data['due_date'], 
                $fileName,
                $initialStatus
            ];

            if ($stmt->execute($params)) {
                $this->sendNotifications($data);
                header("Location: ../Views/teacher/manage_quiz.php?status=success");
                exit();
            }
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function updateQuiz($data, $file) {
        $stmt = $this->db->prepare("SELECT image_url FROM quizzes WHERE id = ?");
        $stmt->execute([$data['quiz_id']]);
        $oldFile = $stmt->fetchColumn();
        $fileName = $oldFile;

        if (!empty($file['quiz_file']['name'])) {
            $newFileName = $this->uploadFile($file);
            if ($newFileName) {
                $fileName = $newFileName;
                if ($oldFile && file_exists($this->targetDir . $oldFile)) { 
                    unlink($this->targetDir . $oldFile); 
                }
            }
        }

        // Updated to include attempts_limit
        $query = "UPDATE quizzes SET class_id = ?, title = ?, description = ?, duration = ?, attempts_limit = ?, open_date = ?, due_date = ?, image_url = ? 
                  WHERE id = ? AND teacher_id = ?";
        
        $stmt = $this->db->prepare($query);
        $params = [
            $data['class_id'],
            $data['title'],
            $data['description'],
            $data['duration'],
            $data['attempts_limit'], // New column
            $data['open_date'],
            $data['due_date'],
            $fileName,
            $data['quiz_id'],
            $_SESSION['user_id']
        ];

        if ($stmt->execute($params)) {
            header("Location: ../Views/teacher/manage_quiz.php?status=updated");
            exit();
        }
    }

    public function deleteQuiz($id) {
        // Get file first
        $stmt = $this->db->prepare("SELECT image_url FROM quizzes WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetchColumn();
        
        // Delete file from folder
        if ($file && file_exists($this->targetDir . $file)) { 
            unlink($this->targetDir . $file); 
        }

        // Delete from database (no teacher_id restriction)
        $stmt = $this->db->prepare("DELETE FROM quizzes WHERE id = ?");
        if ($stmt->execute([$id])) {
            // Redirect based on role
            if ($_SESSION['role'] === 'admin') {
                header("Location: ../Views/admin/manage_quiz.php?status=deleted");
            } else {
                header("Location: ../Views/teacher/manage_quiz.php?status=deleted");
            }
            exit();
        }
    }

    private function uploadFile($file) {
        if (empty($file['quiz_file']['name'])) return "";
        $fileTmpPath = $file['quiz_file']['tmp_name'];
        $fileName = $file['quiz_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
            if(move_uploaded_file($fileTmpPath, $this->targetDir . $newFileName)) {
                return $newFileName;
            }
        }
        return ""; 
    }

    private function sendNotifications($data) {
        $notifQuery = "INSERT INTO notifications (user_id, title, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())";
        $notifStmt = $this->db->prepare($notifQuery);

        // 1. Notify Students
        $studentStmt = $this->db->prepare("SELECT id FROM users WHERE role = 'student' AND class_id = ?");
        $studentStmt->execute([$data['class_id']]);
        $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($students as $student) {
            $notifStmt->execute([$student['id'], "វិញ្ញាសាថ្មី!", "មេរៀន៖ " . htmlspecialchars($data['title'])]);
        }

        // 2. Notify Admins
        $senderName = $_SESSION['full_name'] ?? "គ្រូបង្រៀន";
        $adminStmt = $this->db->query("SELECT id FROM users WHERE role = 'admin'");
        $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($admins as $admin) {
            $notifStmt->execute([$admin['id'], "មានវិញ្ញាសាថ្មី!", "លោកគ្រូ $senderName បានដាក់វិញ្ញាសាថ្មី។"]);
        }
    }
}

// Route handling
$controller = new QuizController();
if (isset($_POST['save_quiz'])) $controller->createQuiz($_POST, $_FILES);
if (isset($_POST['update_quiz'])) $controller->updateQuiz($_POST, $_FILES);
if (isset($_GET['delete_id'])) $controller->deleteQuiz($_GET['delete_id']);