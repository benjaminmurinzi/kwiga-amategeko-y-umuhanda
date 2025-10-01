<?php
/**
 * Helper Functions
 * Commonly used functions throughout the application
 */

/**
 * Sanitize input data
 * @param mixed $data Input data to sanitize
 * @return mixed Sanitized data
 */
function sanitize($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($value);
        }
        return $data;
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirect to another page
 * @param string $url URL to redirect to
 */
function redirect($url) {
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit();
    } else {
        echo '<script type="text/javascript">window.location.href="' . $url . '";</script>';
        exit();
    }
}

/**
 * Display flash message
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message text
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null Flash message array
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message HTML
 * @return string HTML for flash message
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        
        $class = $alertClass[$flash['type']] ?? 'alert-info';
        
        return '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($flash['message']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    }
    return '';
}

/**
 * Hash password
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random token
 * @param int $length Token length
 * @return string Random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRE) {
        $_SESSION['csrf_token'] = generateToken(32);
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    if ((time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRE) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate email
 * @param string $email Email address
 * @return bool True if valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Rwanda format)
 * @param string $phone Phone number
 * @return bool True if valid
 */
function validatePhone($phone) {
    // Rwanda phone format: +250XXXXXXXXX or 07XXXXXXXX
    $pattern = '/^(\+?250|0)?7[0-9]{8}$/';
    return preg_match($pattern, $phone);
}

/**
 * Format currency
 * @param float $amount Amount
 * @param string $currency Currency code
 * @return string Formatted amount
 */
function formatCurrency($amount, $currency = CURRENCY) {
    return number_format($amount, 0) . ' ' . $currency;
}

/**
 * Format date
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (!$date) return '';
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Time ago format
 * @param string $datetime Date time string
 * @return string Human readable time ago
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    $periods = [
        'year' => 31536000,
        'month' => 2592000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1
    ];
    
    foreach ($periods as $key => $value) {
        if ($difference >= $value) {
            $time = floor($difference / $value);
            return $time . ' ' . $key . ($time > 1 ? 's' : '') . ' ago';
        }
    }
    
    return 'just now';
}

/**
 * Generate slug from string
 * @param string $string Input string
 * @return string Slug
 */
function generateSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Upload file
 * @param array $file $_FILES array element
 * @param string $destination Upload directory
 * @param array $allowedTypes Allowed MIME types
 * @return array Result with status and message/filename
 */
function uploadFile($file, $destination, $allowedTypes = ALLOWED_IMAGE_TYPES) {
    // Check if file was uploaded
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['status' => false, 'message' => 'Invalid file upload'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'message' => 'Upload error occurred'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['status' => false, 'message' => 'File too large. Maximum size: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'];
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['status' => false, 'message' => 'Invalid file type'];
    }
    
    // Create destination directory if not exists
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $destination . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['status' => true, 'filename' => $filename, 'filepath' => $filepath];
    }
    
    return ['status' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Delete file
 * @param string $filepath File path
 * @return bool True if deleted
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Check if user has active subscription
 * @param int $userId User ID
 * @param PDO $db Database connection
 * @return bool True if has active subscription
 */
function hasActiveSubscription($userId, $db) {
    $query = "SELECT COUNT(*) as count FROM subscriptions 
              WHERE user_id = :user_id 
              AND status = 'active' 
              AND end_date >= CURDATE()";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    $result = $stmt->fetch();
    return $result['count'] > 0;
}

/**
 * Get user subscription details
 * @param int $userId User ID
 * @param PDO $db Database connection
 * @return array|null Subscription details
 */
function getUserSubscription($userId, $db) {
    $query = "SELECT s.*, sp.name_en, sp.name_rw, sp.duration_days 
              FROM subscriptions s 
              LEFT JOIN subscription_plans sp ON s.plan_id = sp.id
              WHERE s.user_id = :user_id 
              AND s.status = 'active' 
              AND s.end_date >= CURDATE()
              ORDER BY s.end_date DESC 
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    return $stmt->fetch();
}

/**
 * Calculate quiz score
 * @param array $userAnswers User's answers
 * @param array $correctAnswers Correct answers
 * @return array Score details
 */
function calculateQuizScore($userAnswers, $correctAnswers) {
    $totalQuestions = count($correctAnswers);
    $correctCount = 0;
    
    foreach ($correctAnswers as $questionId => $correctAnswer) {
        if (isset($userAnswers[$questionId]) && $userAnswers[$questionId] === $correctAnswer) {
            $correctCount++;
        }
    }
    
    $score = ($correctCount / $totalQuestions) * 100;
    $passed = $score >= PASSING_SCORE;
    
    return [
        'total_questions' => $totalQuestions,
        'correct_answers' => $correctCount,
        'wrong_answers' => $totalQuestions - $correctCount,
        'score' => round($score, 2),
        'passed' => $passed
    ];
}

/**
 * Send email notification
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message
 * @return bool True if sent
 */
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM . '>' . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Log activity
 * @param string $action Action performed
 * @param int $userId User ID
 * @param string $details Additional details
 */
function logActivity($action, $userId = null, $details = '') {
    $logFile = ROOT_PATH . '/logs/activity_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $logMessage = "[{$timestamp}] User: {$userId} | IP: {$ip} | Action: {$action} | Details: {$details}\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Pagination helper
 * @param int $totalItems Total number of items
 * @param int $currentPage Current page number
 * @param int $itemsPerPage Items per page
 * @return array Pagination data
 */
function paginate($totalItems, $currentPage = 1, $itemsPerPage = ITEMS_PER_PAGE) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Get current language
 * @return string Current language code
 */
function getCurrentLanguage() {
    return $_SESSION['language'] ?? DEFAULT_LANGUAGE;
}

/**
 * Switch language
 * @param string $lang Language code
 */
function switchLanguage($lang) {
    if (in_array($lang, AVAILABLE_LANGUAGES)) {
        $_SESSION['language'] = $lang;
        require_once ROOT_PATH . '/languages/' . $lang . '.php';
    }
}

/**
 * Get translation
 * @param string $key Translation key
 * @return string Translated text
 */
function trans($key) {
    global $lang;
    return $lang[$key] ?? $key;
}

/**
 * Alias for trans() function
 */
function __($key) {
    return trans($key);
}
?>
