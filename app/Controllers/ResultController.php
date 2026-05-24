<?php
require_once __DIR__ . "/../../config/database.php"; 

class ResultController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    // --- ផ្នែកទី ១៖ សម្រាប់បង្ហាញលទ្ធផលលើ Dashboard គ្រូ (កូដចាស់របស់អ្នក) ---
    public function getStudentResults() {
        $current_teacher_id = $_SESSION['user_id'] ?? 0; 

        $query = "SELECT 
            r.*, 
            q.title, 
            q.total_questions, 
            u.full_name, 
            u.username,    /* ត្រូវតែមាន Column នេះ */
            u.profile_image,
            c.class_name 
          FROM results r
          JOIN quizzes q ON r.quiz_id = q.id 
          JOIN users u ON r.user_id = u.id
          LEFT JOIN classes c ON u.class_id = c.id 
          WHERE q.teacher_id = :teacher_id
          ORDER BY r.created_at DESC";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['teacher_id' => $current_teacher_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            return [];
        }
    }

    // --- ផ្នែកទី ២៖ សម្រាប់រក្សាទុកលទ្ធផល និងផ្ញើដំណឹងទៅគ្រូ (បន្ថែមថ្មី) ---
    // ក្នុង ResultController.php
public function saveResult($quiz_id, $user_id, $score) {
    try {
        // ១. រក្សាទុកពិន្ទុ
        $query = "INSERT INTO results (quiz_id, user_id, score, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        
        if ($stmt->execute([$quiz_id, $user_id, $score])) {
            
            // ២. ទាញយក teacher_id និងឈ្មោះសិស្ស (សំខាន់៖ ត្រូវ JOIN ឱ្យត្រូវ Table)
            // យើងរកមើលថា Quiz ហ្នឹងជារបស់គ្រូណា
            $infoQuery = "SELECT q.teacher_id, q.title, u.full_name 
                          FROM quizzes q 
                          JOIN users u ON u.id = ? 
                          WHERE q.id = ?";
            $infoStmt = $this->db->prepare($infoQuery);
            $infoStmt->execute([$user_id, $quiz_id]);
            $data = $infoStmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $teacher_id = $data['teacher_id'];
                $quiz_title = $data['title'];
                $student_name = $data['full_name'];

                // ៣. បញ្ចូលទៅក្នុង notifications
                $notif_title = "សិស្សបានបញ្ជូនកិច្ចការ";
                $notif_msg = "សិស្ស $student_name បានបញ្ចប់វិញ្ញាសា '$quiz_title' ជាមួយពិន្ទុ $score។";

                $notifStmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, is_read, created_at) 
                                                 VALUES (?, ?, ?, 0, NOW())");
                return $notifStmt->execute([$teacher_id, $notif_title, $notif_msg]);
            }
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}
}