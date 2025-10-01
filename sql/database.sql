-- Traffic Rules Learning Platform Database Schema
-- Create this database first: CREATE DATABASE traffic_learning;

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('learner', 'school', 'admin') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    language_preference ENUM('en', 'rw') DEFAULT 'en',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DRIVING SCHOOLS TABLE
-- ============================================
CREATE TABLE driving_schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    school_name VARCHAR(255) NOT NULL,
    registration_number VARCHAR(100) UNIQUE,
    address TEXT,
    contact_person VARCHAR(100),
    contact_phone VARCHAR(20),
    contact_email VARCHAR(255),
    logo_url VARCHAR(255),
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SCHOOL STUDENTS TABLE
-- ============================================
CREATE TABLE school_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    student_user_id INT NOT NULL,
    enrollment_number VARCHAR(50),
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'graduated') DEFAULT 'active',
    notes TEXT,
    FOREIGN KEY (school_id) REFERENCES driving_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_school_student (school_id, student_user_id),
    INDEX idx_school_id (school_id),
    INDEX idx_student_id (student_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SUBSCRIPTION PLANS TABLE
-- ============================================
CREATE TABLE subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(100) NOT NULL,
    name_rw VARCHAR(100) NOT NULL,
    description_en TEXT,
    description_rw TEXT,
    duration_days INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    user_type ENUM('learner', 'school') NOT NULL,
    max_students INT DEFAULT NULL, -- For schools
    features JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SUBSCRIPTIONS TABLE
-- ============================================
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    auto_renew BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- LESSON CATEGORIES TABLE
-- ============================================
CREATE TABLE lesson_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(100) NOT NULL,
    name_rw VARCHAR(100) NOT NULL,
    description_en TEXT,
    description_rw TEXT,
    icon VARCHAR(50),
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- LESSONS TABLE
-- ============================================
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    title_rw VARCHAR(255) NOT NULL,
    content_en TEXT NOT NULL,
    content_rw TEXT NOT NULL,
    summary_en TEXT,
    summary_rw TEXT,
    image_url VARCHAR(255),
    video_url VARCHAR(255),
    duration_minutes INT DEFAULT 0,
    display_order INT DEFAULT 0,
    is_premium BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'published', 'archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES lesson_categories(id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUIZZES TABLE
-- ============================================
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT,
    title_en VARCHAR(255) NOT NULL,
    title_rw VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_rw TEXT,
    quiz_type ENUM('practice', 'mock', 'final') NOT NULL,
    duration_minutes INT DEFAULT 30,
    passing_score INT DEFAULT 70,
    max_attempts INT DEFAULT 0, -- 0 = unlimited
    is_premium BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'published', 'archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    INDEX idx_lesson_id (lesson_id),
    INDEX idx_quiz_type (quiz_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUESTIONS TABLE
-- ============================================
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_en TEXT NOT NULL,
    question_rw TEXT NOT NULL,
    question_image VARCHAR(255),
    option_a_en VARCHAR(255) NOT NULL,
    option_a_rw VARCHAR(255) NOT NULL,
    option_b_en VARCHAR(255) NOT NULL,
    option_b_rw VARCHAR(255) NOT NULL,
    option_c_en VARCHAR(255),
    option_c_rw VARCHAR(255),
    option_d_en VARCHAR(255),
    option_d_rw VARCHAR(255),
    correct_answer ENUM('A', 'B', 'C', 'D') NOT NULL,
    explanation_en TEXT,
    explanation_rw TEXT,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    points INT DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_quiz_id (quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUIZ ATTEMPTS TABLE
-- ============================================
CREATE TABLE quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    total_questions INT NOT NULL,
    correct_answers INT NOT NULL,
    wrong_answers INT NOT NULL,
    started_at TIMESTAMP NOT NULL,
    completed_at TIMESTAMP,
    time_taken_seconds INT,
    passed BOOLEAN DEFAULT FALSE,
    answers JSON, -- Store user answers
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_quiz_id (quiz_id),
    INDEX idx_completed_at (completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROGRESS TRACKING TABLE
-- ============================================
CREATE TABLE user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    completion_percentage INT DEFAULT 0,
    time_spent_seconds INT DEFAULT 0,
    last_accessed_at TIMESTAMP,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_lesson (user_id, lesson_id),
    INDEX idx_user_id (user_id),
    INDEX idx_lesson_id (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PAYMENTS TABLE
-- ============================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subscription_id INT,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'RWF',
    payment_method VARCHAR(50) NOT NULL, -- MTN, Airtel, PayPal, Card
    transaction_id VARCHAR(255) UNIQUE,
    phone_number VARCHAR(20),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_data JSON, -- Store full payment response
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    title_rw VARCHAR(255) NOT NULL,
    message_en TEXT NOT NULL,
    message_rw TEXT NOT NULL,
    type VARCHAR(50), -- subscription_expiry, new_lesson, achievement, etc.
    is_read BOOLEAN DEFAULT FALSE,
    action_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ACHIEVEMENTS TABLE
-- ============================================
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(100) NOT NULL,
    name_rw VARCHAR(100) NOT NULL,
    description_en TEXT,
    description_rw TEXT,
    badge_icon VARCHAR(255),
    criteria JSON, -- {"lessons_completed": 10, "quizzes_passed": 5}
    points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- USER ACHIEVEMENTS TABLE
-- ============================================
CREATE TABLE user_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_achievement (user_id, achievement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SYSTEM SETTINGS TABLE
-- ============================================
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Insert default admin user (password: admin123 - change this!)
INSERT INTO users (email, password, user_type, first_name, last_name, language_preference, status, email_verified)
VALUES ('admin@trafficlearning.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Admin', 'en', 'active', TRUE);

-- Insert sample subscription plans
INSERT INTO subscription_plans (name_en, name_rw, description_en, description_rw, duration_days, price, user_type, max_students) VALUES
('Monthly Learner', 'Abanyeshuri ba buri kwezi', 'Access all lessons and quizzes for 30 days', 'Injira mu masomo yose na quizzes mu minsi 30', 30, 5000.00, 'learner', NULL),
('Annual Learner', 'Abanyeshuri ba buri mwaka', 'Access all lessons and quizzes for 365 days', 'Injira mu masomo yose na quizzes mu minsi 365', 365, 50000.00, 'learner', NULL),
('School Basic', 'Ikigo gisanzwe', 'Up to 50 students for 30 days', 'Abanyeshuri bagera kuri 50 mu minsi 30', 30, 100000.00, 'school', 50),
('School Premium', 'Ikigo kidasanzwe', 'Up to 200 students for 30 days', 'Abanyeshuri bagera kuri 200 mu minsi 30', 30, 300000.00, 'school', 200);

-- Insert lesson categories
INSERT INTO lesson_categories (name_en, name_rw, description_en, description_rw, icon, display_order) VALUES
('Traffic Signs', 'Ibimenyetso byo mu mahanga', 'Learn about road signs and their meanings', 'Wige ku bimenyetso byo mu mahanga n\'ibisobanuro byabyo', 'sign', 1),
('Traffic Rules', 'Amategeko y\'umuhanda', 'Understand traffic laws and regulations', 'Sobanukirwa amategeko n\'amabwiriza yo mu muhanda', 'rules', 2),
('Road Safety', 'Umutekano mu mahanga', 'Safety tips and best practices', 'Inama z\'umutekano n\'imikorere myiza', 'safety', 3),
('Driving Ethics', 'Imyitwarire y\'abagendeshwa', 'Professional driving behavior', 'Imyitwarire myiza y\'abagendeshwa', 'ethics', 4);

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'Traffic Learning Platform', 'Website name'),
('site_email', 'info@trafficlearning.com', 'Contact email'),
('currency', 'RWF', 'Default currency'),
('mtn_api_key', '', 'MTN Mobile Money API Key'),
('airtel_api_key', '', 'Airtel Money API Key'),
('paypal_client_id', '', 'PayPal Client ID');
