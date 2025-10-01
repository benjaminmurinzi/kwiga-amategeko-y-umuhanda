<?php
/**
 * Session Management
 * Handle user sessions and authentication
 */

/**
 * Check if user is logged in
 * @return bool True if logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

/**
 * Get current user ID
 * @return int|null User ID or null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user type
 * @return string|null User type or null
 */
function getCurrentUserType() {
    return $_SESSION['user_type'] ?? null;
}

/**
 * Get current user data
 * @return array|null User data array
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'] ?? '',
        'first_name' => $_SESSION['user_first_name'] ?? '',
        'last_name' => $_SESSION['user_last_name'] ?? '',
        'user_type' => $_SESSION['user_type'],
        'language' => $_SESSION['language'] ?? DEFAULT_LANGUAGE
    ];
}

/**
 * Set user session
 * @param array $user User data from database
 */
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['language'] = $user['language_preference'] ?? DEFAULT_LANGUAGE;
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Destroy user session (logout)
 */
function destroyUserSession() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if user is admin
 * @return bool True if admin
 */
function isAdmin() {
    return isLoggedIn() && getCurrentUserType() === 'admin';
}

/**
 * Check if user is learner
 * @return bool True if learner
 */
function isLearner() {
    return isLoggedIn() && getCurrentUserType() === 'learner';
}

/**
 * Check if user is driving school
 * @return bool True if driving school
 */
function isDrivingSchool() {
    return isLoggedIn() && getCurrentUserType() === 'school';
}

/**
 * Require login - redirect if not logged in
 * @param string $redirectUrl URL to redirect to
 */
function requireLogin($redirectUrl = '/public/login.php') {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to access this page');
        redirect(BASE_URL . $redirectUrl);
        exit();
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlashMessage('error', 'Access denied. Admin privileges required.');
        redirect(BASE_URL . '/public/dashboard.php');
        exit();
    }
}

/**
 * Require learner - redirect if not learner
 */
function requireLearner() {
    requireLogin();
    if (!isLearner()) {
        setFlashMessage('error', 'Access denied. Learner account required.');
        redirect(BASE_URL . '/public/dashboard.php');
        exit();
    }
}

/**
 * Require driving school - redirect if not school
 */
function requireDrivingSchool() {
    requireLogin();
    if (!isDrivingSchool()) {
        setFlashMessage('error', 'Access denied. Driving school account required.');
        redirect(BASE_URL . '/public/dashboard.php');
        exit();
    }
}

/**
 * Require active subscription
 * @param PDO $db Database connection
 */
function requireActiveSubscription($db) {
    requireLogin();
    
    $userId = getCurrentUserId();
    
    // Admins bypass subscription check
    if (isAdmin()) {
        return;
    }
    
    // Check if user has active subscription
    if (!hasActiveSubscription($userId, $db)) {
        setFlashMessage('warning', 'Your subscription has expired. Please renew to continue.');
        
        if (isLearner()) {
            redirect(BASE_URL . '/learner/subscription.php');
        } elseif (isDrivingSchool()) {
            redirect(BASE_URL . '/school/subscription.php');
        }
        exit();
    }
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn() && isset($_SESSION['login_time'])) {
        $sessionLifetime = SESSION_LIFETIME;
        $currentTime = time();
        
        if (($currentTime - $_SESSION['login_time']) > $sessionLifetime) {
            destroyUserSession();
            setFlashMessage('warning', 'Your session has expired. Please login again.');
            redirect(BASE_URL . '/public/login.php');
            exit();
        }
        
        // Update last activity time
        $_SESSION['login_time'] = $currentTime;
    }
}

/**
 * Remember user login (using cookies)
 * @param int $userId User ID
 * @param string $token Remember token
 */
function setRememberMe($userId, $token) {
    $cookieName = 'remember_me';
    $cookieValue = $userId . ':' . $token;
    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
    
    setcookie($cookieName, $cookieValue, $expiry, '/', '', false, true);
}

/**
 * Check remember me cookie
 * @param PDO $db Database connection
 * @return bool True if remembered and logged in
 */
function checkRememberMe($db) {
    if (isLoggedIn()) {
        return false;
    }
    
    if (!isset($_COOKIE['remember_me'])) {
        return false;
    }
    
    $parts = explode(':', $_COOKIE['remember_me']);
    if (count($parts) !== 2) {
        return false;
    }
    
    list($userId, $token) = $parts;
    
    // Verify token from database (you need to store remember tokens)
    $query = "SELECT * FROM users WHERE id = :id AND status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
        setUserSession($user);
        return true;
    }
    
    // Invalid cookie, delete it
    setcookie('remember_me', '', time() - 3600, '/');
    return false;
}

/**
 * Clear remember me cookie
 */
function clearRememberMe() {
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
}

// Check session timeout on every page load
checkSessionTimeout();
?>
