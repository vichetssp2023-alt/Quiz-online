<?php
class Student {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db; // Use the connection passed from the Controller
    }

    // Create a new student record
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password, role, full_name, gender, dob, address, class_id) 
                  VALUES (:username, :email, :password, :role, :full_name, :gender, :dob, :address, :class_id)";

        $stmt = $this->conn->prepare($query);

        // Bind all parameters to prevent SQL injection and errors
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':class_id', $data['class_id']);

        return $stmt->execute();
    }

    // Update an existing student record
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET 
                  username = :username, 
                  email = :email, 
                  full_name = :full_name, 
                  gender = :gender, 
                  dob = :dob, 
                  address = :address, 
                  class_id = :class_id 
                  WHERE id = :id AND role = 'student'";
                  
        $stmt = $this->conn->prepare($query);

        // Explicitly bind parameters to fix the "Invalid parameter number" error
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':class_id', $data['class_id']);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    // Delete a student record
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND role = 'student'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>