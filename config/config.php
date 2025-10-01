<?php
/**
 * General Application Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Kigali');

// Site settings
define('SITE_NAME', 'Traffic Learning Platform');
define('SITE_URL', 'http://localhost/traffic-learning-platform'); // Update this
define('SITE_EMAIL', 'info@trafficlearning.com');

// Directory paths
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// URL paths
define('BASE_URL', SITE_URL);
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('CSRF_TOKEN_EXPIRE', 3600); // 1 hour

// Pagination
define('ITEMS_PER_PAGE', 10);
define('LESSONS_PER_PAGE', 12);
define('STUDENTS_PER_PAGE', 20);

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);

// Payment settings
define('CURRENCY', 'RWF');
define('MTN_API_URL', 'https://sandbox.momodeveloper.mtn.com'); // Update for production
define('AIRTEL_API_URL', 'https://openapiuat.airtel.africa'); // Update for production

// Quiz settings
define('QUIZ_TIME_LIMIT', 30); // minutes
define('PASSING_SCORE', 70); // percentage
define('MAX_QUIZ_ATTEMPTS', 3);

// Subscription settings
define('TRIAL_PERIOD_DAYS', 7);
define('SUBSCRIPTION_GRACE_PERIOD', 3); // days after expiry

// Language settings
define('DEFAULT_LANGUAGE', 'en');
define('AVAILABLE_LANGUAGES', ['en', 'rw']);

// Email settings (for notifications)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', ''); // Your email
define('SMTP_PASS', ''); // Your email password
define('SMTP_FROM', SITE_EMAIL);
define('SMTP_FROM_NAME', SITE_NAME);

// Include required files
require_once ROOT_PATH . '/config/database.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/session.php';

// Auto-load classes
