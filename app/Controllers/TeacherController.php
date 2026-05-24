<?php
session_start();
require_once "../../config/database.php";

class TeacherController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function addTeacher() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // ១. ចាប់យកតម្លៃដែលផ្ញើមកពី Form (Add Modal)
            $username  = $_POST['username'];
            $email     = $_POST['email'];
            $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $gender    = $_POST['gender'];
            $dob       = $_POST['dob'];
            $address   = $_POST['address'];
            $role      = 'teacher';
            $full_name = $_POST['username']; // ដាក់ឈ្មោះដូចគ្នាជាបណ្តោះអាសន្ន

            try {
                // ២. កែ SQL Query បន្ថែម Column: gender, dob, address, full_name
                $query = "INSERT INTO users (username, email, password, role, gender, dob, address, full_name) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($query);
                
                // ៣. បញ្ជូនតម្លៃទៅតាមលំដាប់លំដោយ ? ខាងលើ
                if ($stmt->execute([$username, $email, $password, $role, $gender, $dob, $address, $full_name])) {
                    header("Location: ../Views/admin/manage_teacher.php?status=success");
                    exit();
                }
            } catch (PDOException $e) {
                // បើមាន Error បង្ហាញ Error នោះតែម្តងដើម្បីស្រួល Check
                die("Error Adding: " . $e->getMessage());
            }
        }
    }

    public function editTeacher() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // ១. ចាប់យកតម្លៃដែលផ្ញើមកពី Form (Edit Modal)
            $id       = $_POST['id'];
            $username = $_POST['username'];
            $email    = $_POST['email'];
            $gender   = $_POST['gender'];
            $dob      = $_POST['dob'];
            $address  = $_POST['address'];
            $full_name = $_POST['username']; 

            try {
                // ២. កែ SQL Query ឱ្យមាន SET ចំពោះ Column ថ្មីៗ
                $query = "UPDATE users 
                          SET username = ?, email = ?, gender = ?, dob = ?, address = ?, full_name = ? 
                          WHERE id = ? AND role = 'teacher'";
                $stmt = $this->db->prepare($query);
                
                // ៣. បញ្ជូន Array ទៅតាមលំដាប់ ?
                if ($stmt->execute([$username, $email, $gender, $dob, $address, $full_name, $id])) {
                    header("Location: ../Views/admin/manage_teacher.php?status=updated");
                    exit();
                }
            } catch (PDOException $e) {
                die("Error Updating: " . $e->getMessage());
            }
        }
    }
    public function deleteTeacher($id) {
        try {
            $query = "DELETE FROM users WHERE id = ? AND role = 'teacher'";
            $stmt = $this->db->prepare($query);
            
            if ($stmt->execute([$id])) {
                header("Location: ../Views/admin/manage_teacher.php?status=deleted");
                exit();
            }
        } catch (Exception $e) {
            header("Location: ../Views/admin/manage_teacher.php?status=error");
            exit();
        }
    }
}

// Route Handling
$controller = new TeacherController();
$action = $_GET['action'] ?? '';

if ($action == 'add') {
    $controller->addTeacher();
} elseif ($action == 'edit') {
    $controller->editTeacher();
} elseif ($action == 'delete' && isset($_GET['id'])) {
    $controller->deleteTeacher($_GET['id']);
}