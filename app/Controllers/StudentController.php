<?php
require_once "../../config/database.php";
require_once __DIR__ . '/../Models/Student.php';

class StudentController {
    private $db;
    private $studentModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->studentModel = new Student($this->db);
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'add') {
                $this->addStudent();
            } elseif ($action === 'edit') {
                $this->updateStudent();
            }
        } elseif ($action === 'delete') {
            $this->deleteStudent();
        }
    }

    private function addStudent() {
        $check = $this->db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->execute([$_POST['email']]);
        if ($check->fetch()) {
            header("Location: ../Views/admin/manage_student.php?status=duplicate");
            exit();
        }

        $data = $this->getPostData();
        $data['password'] = password_hash($_POST['password'] ?? '123456', PASSWORD_DEFAULT);
        
        if ($this->studentModel->create($data)) {
            header("Location: ../Views/admin/manage_student.php?status=success");
        } else {
            header("Location: ../Views/admin/manage_student.php?status=error");
        }
        exit();
    }

    private function updateStudent() {
        $id = $_POST['id'] ?? null;
        if (!$id) exit("Missing ID");

        $data = $this->getPostData();
        if ($this->studentModel->update($id, $data)) {
            header("Location: ../Views/admin/manage_student.php?status=success");
        } else {
            header("Location: ../Views/admin/manage_student.php?status=error");
        }
        exit();
    }

    private function deleteStudent() {
        $id = $_GET['id'] ?? null;
        if ($id && $this->studentModel->delete($id)) {
            header("Location: ../Views/admin/manage_student.php?status=success");
        } else {
            header("Location: ../Views/admin/manage_student.php?status=error");
        }
        exit();
    }
    private function getPostData() {
        return [
            'username'  => $_POST['username'] ?? '',
            'email'     => $_POST['email'] ?? '',
            'full_name' => $_POST['username'] ?? '', 
            'gender'    => $_POST['gender'] ?? '',
            'dob'       => $_POST['dob'] ?? null,
            'address'   => $_POST['address'] ?? '',
            'role'      => 'student',
            'class_id'  => $_POST['class_id'] ?? null 
        ];
    }
}

$controller = new StudentController();
$controller->handleRequest();