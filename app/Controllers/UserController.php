<?php
session_start();
require_once "../../config/database.php"; 

class UserController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // ==========================================
    // 1. LOGIN FUNCTION
    // ==========================================
    public function login() {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $query = "SELECT * FROM users WHERE username = :username AND password = :password LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':username' => $username, ':password' => $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = $user['id']; 
            $_SESSION['role'] = $user['role'];
            $_SESSION['user'] = $user['username'];
            
            // --- CRITICAL ADDITION HERE ---
            // This allows the dashboard to filter quizzes and notifications 
            // specifically for "Cambodia Class" or "Thailand Class"
            $_SESSION['class_id'] = $user['class_id']; 
            
            $_SESSION['email'] = $user['email'];
            $_SESSION['profile_image'] = $user['profile_image'];
            $_SESSION['gender'] = $user['gender'];
            $_SESSION['dob'] = $user['dob'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['full_name'] = $user['full_name'];

            $path = ($user['role'] === 'admin') ? 'admin' : (($user['role'] === 'teacher') ? 'teacher' : 'student');
            header("Location: ../Views/$path/dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "ឈ្មោះ ឬ លេខសម្ងាត់មិនត្រឹមត្រូវ!";
            header("Location: ../../public/index.php");
            exit();
        }
    }
}
    // ==========================================
    // 2. UPDATE PROFILE (FOR STUDENT/TEACHER)
    // ==========================================
    public function updateProfile() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../../public/index.php");
            exit();
        }

        $user_id = $_SESSION['user_id'];
        
        // Capture the new fields from POST
        $full_name = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $dob = $_POST['dob'] ?? '';
        $address = $_POST['address'] ?? '';
        $profile_image = $_SESSION['profile_image'] ?? ''; 

        // Handle Image Upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $target_dir = "../../public/images/profiles/"; 
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            $file_extension = pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION);
            $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_extension;
            
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $new_filename)) {
                // Delete old image if it exists
                if(!empty($_SESSION['profile_image']) && file_exists($target_dir . $_SESSION['profile_image'])) {
                    unlink($target_dir . $_SESSION['profile_image']);
                }
                $profile_image = $new_filename;
            }
        }

        try {
            // UPDATED SQL: Added full_name to the update list
            $sql = "UPDATE users SET full_name=?, email=?, gender=?, dob=?, address=?, profile_image=? WHERE id=?";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt->execute([$full_name, $email, $gender, $dob, $address, $profile_image, $user_id])) {
                
                // Update Session immediately so the UI reflects changes
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $_SESSION['gender'] = $gender;
                $_SESSION['dob'] = $dob;
                $_SESSION['address'] = $address;
                $_SESSION['profile_image'] = $profile_image; 
                    // Redirect based on role
                    $role = $_SESSION['role'];
                    if ($role === 'admin') {
                        header("Location: ../Views/admin/dashboard.php?update=success");
                    } elseif ($role === 'teacher') {
                        header("Location: ../Views/teacher/dashboard.php?update=success");
                    } else {
                        header("Location: ../Views/student/student_profile.php?update=success");
                    }
                exit();
            }
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    // ==========================================
    // 3. EDIT USER (ADMIN FUNCTION)
    // ==========================================
    public function editUser($data) {
        try {
            $query = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            if ($stmt->execute([$data['username'], $data['email'], $data['role'], $data['user_id']])) {
                header("Location: ../Views/admin/dashboard.php?status=updated");
                exit();
            }
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    // ==========================================
    // 4. DELETE USER (ADMIN FUNCTION)
    // ==========================================
    public function deleteUser($id) {
        try {
            if (empty($id)) die("Error: User ID is missing.");

            $stmt = $this->db->prepare("SELECT profile_image FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetchColumn();

            $image_path = "../../public/images/profiles/" . $image;
            if ($image && file_exists($image_path)) {
                unlink($image_path);
            }

            $query = "DELETE FROM users WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            if ($stmt->execute([$id])) {
                header("Location: ../Views/admin/dashboard.php?status=deleted");
                exit();
            }
        } catch (PDOException $e) {
            die("Database Error: " . $e->getMessage());
        }
    }
    public function register($data) {
    // 1. បញ្ចូលទិន្នន័យសិស្សថ្មី
    $query = "INSERT INTO users (username, password, email, role, class_id) VALUES (?, ?, ?, 'student', ?)";
    $stmt = $this->db->prepare($query);
    
    if ($stmt->execute([$data['username'], $data['password'], $data['email'], $data['class_id']])) {
        $new_student_name = $data['username'];

        // 2. ទាញយក ID របស់ Admin ទាំងអស់
        $adminStmt = $this->db->query("SELECT id FROM users WHERE role = 'admin'");
        $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. ផ្ញើ Notification ទៅ Admin គ្រប់រូប
        $notifQuery = "INSERT INTO notifications (user_id, title, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())";
        $notifStmt = $this->db->prepare($notifQuery);

        foreach ($admins as $admin) {
            $notifStmt->execute([
                $admin['id'], 
                "សិស្សចុះឈ្មោះថ្មី!", 
                "មានសិស្សថ្មីឈ្មោះ $new_student_name ទើបតែចុះឈ្មោះក្នុងប្រព័ន្ធ។"
            ]);
        }
        return true;
    }
    return false;
}
}

// ==========================================
// ROUTING LOGIC (ចាប់យក ACTION ទាំងអស់)
// ==========================================
$controller = new UserController();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if (isset($_POST['login'])) {
    $controller->login();
} elseif ($action === 'update_profile') {
    $controller->updateProfile();
} elseif ($action === 'update' || isset($_POST['btn_update'])) {
    $controller->editUser($_POST);
} elseif ($action === 'delete' && isset($_GET['id'])) {
    $controller->deleteUser($_GET['id']);
}