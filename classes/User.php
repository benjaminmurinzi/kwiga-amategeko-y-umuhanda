<?php
/**
 * User Class
 * Handle all user-related operations
 */

class User {
    private $conn;
    private $table = 'users';
    
    public $id;
    public $email;
    public $password;
    public $user_type;
    public $first_name;
    public $last_name;
    public $phone;
    public $language_preference;
    public $status;
    public $email_verified;
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create new user
     * @return bool|int User ID on success, false on failure
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET email = :email,
                      password = :password,
                      user_type = :user_type,
                      first_name = :first_name,
                      last_name = :last_name,
                      phone = :phone,
                      language_preference = :language_preference,
                      status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $hashed_password = hashPassword($this->password);
        
        // Bind parameters
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':user_type', $this->user_type);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':language_preference', $this->language_preference);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return $this->id;
        }
        
        return false;
    }
    
    /**
     * Read user by ID
     * @return array|false User data or false
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Read user by email
     * @return array|false User data or false
     */
    public function readByEmail() {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Read all users with pagination
     * @param int $page Page number
     * @param int $limit Items per page
     * @param string $userType Filter by user type
     * @return array Users data
     */
    public function readAll($page = 1, $limit = ITEMS_PER_PAGE, $userType = null) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM " . $this->table;
        
        if ($userType) {
            $query .= " WHERE user_type = :user_type";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if ($userType) {
            $stmt->bindParam(':user_type', $userType);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Update user
     * @return bool Success status
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET first_name = :first_name,
                      last_name = :last_name,
                      phone = :phone,
                      language_preference = :language_preference
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':language_preference', $this->language_preference);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Update password
     * @param string $newPassword New password
     * @return bool Success status
     */
    public function updatePassword($newPassword) {
        $query = "UPDATE " . $this->table . " SET password = :password WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = hashPassword($newPassword);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Update user status
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus($status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Delete user
     * @return bool Success status
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Check if email exists
     * @return bool True if exists
     */
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Check if phone exists
     * @return bool True if exists
     */
    public function phoneExists() {
        $query = "SELECT id FROM " . $this->table . " WHERE phone = :phone LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Authenticate user
     * @param string $email Email
     * @param string $password Password
     * @return array|false User data or false
     */
    public function authenticate($email, $password) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE email = :email AND status = 'active' LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            if (verifyPassword($password, $user['password'])) {
                return $user;
            }
        }
        
        return false;
    }
    
    /**
     * Count users by type
     * @param string $userType User type
     * @return int Count
     */
    public function countByType($userType = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        
        if ($userType) {
            $query .= " WHERE user_type = :user_type";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($userType) {
            $stmt->bindParam(':user_type', $userType);
        }
        
        $stmt->execute();
        $row = $stmt->fetch();
        
        return $row['total'];
    }
    
    /**
     * Get recent users
     * @param int $limit Number of users
     * @return array Users data
     */
    public function getRecent($limit = 5) {
        $query = "SELECT * FROM " . $this->table . " 
                  ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Search users
     * @param string $searchTerm Search term
     * @param string $userType User type filter
     * @return array Users data
     */
    public function search($searchTerm, $userType = null) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)";
        
        if ($userType) {
            $query .= " AND user_type = :user_type";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        $searchParam = "%{$searchTerm}%";
        $stmt->bindParam(':search', $searchParam);
        
        if ($userType) {
            $stmt->bindParam(':user_type', $userType);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Verify email
     * @return bool Success status
     */
    public function verifyEmail() {
        $query = "UPDATE " . $this->table . " SET email_verified = TRUE WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Get user full name
     * @param int $userId User ID
     * @return string Full name
     */
    public function getFullName($userId = null) {
        $id = $userId ?? $this->id;
        
        $query = "SELECT first_name, last_name FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            return $user['first_name'] . ' ' . $user['last_name'];
        }
        
        return '';
    }
}
?>
