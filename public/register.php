<?php
/**
 * Registration Page
 */

require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/public/dashboard.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = sanitize($_POST['user_type'] ?? '');
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $language = sanitize($_POST['language'] ?? DEFAULT_LANGUAGE);
    
    // School specific fields
    $schoolName = sanitize($_POST['school_name'] ?? '');
    $registrationNumber = sanitize($_POST['registration_number'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($userType) || !in_array($userType, ['learner', 'school'])) {
        $errors[] = trans('required_fields');
    }
    
    if (empty($firstName)) {
        $errors[] = trans('field_required') . ': ' . trans('first_name');
    }
    
    if (empty($lastName)) {
        $errors[] = trans('field_required') . ': ' . trans('last_name');
    }
    
    if (empty($email)) {
        $errors[] = trans('field_required') . ': ' . trans('email');
    } elseif (!validateEmail($email)) {
        $errors[] = trans('invalid_email');
    }
    
    if (empty($phone)) {
        $errors[] = trans('field_required') . ': ' . trans('phone');
    } elseif (!validatePhone($phone)) {
        $errors[] = trans('invalid_phone');
    }
    
    if (empty($password)) {
        $errors[] = trans('field_required') . ': ' . trans('password');
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = trans('password_too_short');
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = trans('passwords_not_match');
    }
    
    if ($userType === 'school') {
        if (empty($schoolName)) {
            $errors[] = trans('field_required') . ': ' . trans('school_name');
        }
    }
    
    if (empty($errors)) {
        $user = new User($db);
        $user->email = $email;
        
        // Check if email exists
        if ($user->emailExists()) {
            $errors[] = trans('email_exists');
        }
        
        // Check if phone exists
        $user->phone = $phone;
        if ($user->phoneExists()) {
            $errors[] = trans('phone_exists');
        }
        
        if (empty($errors)) {
            // Create user
            $user->user_type = $userType;
            $user->first_name = $firstName;
            $user->last_name = $lastName;
            $user->password = $password;
            $user->language_preference = $language;
            
            $userId = $user->create();
            
            if ($userId) {
                // If school, create school record
                if ($userType === 'school') {
                    $school = new DrivingSchool($db);
                    $school->user_id = $userId;
                    $school->school_name = $schoolName;
                    $school->registration_number = $registrationNumber;
                    $school->address = $address;
                    $school->contact_person = $firstName . ' ' . $lastName;
                    $school->contact_email = $email;
                    $school->contact_phone = $phone;
                    $school->create();
                }
                
                // Log activity
                logActivity('User Registration', $userId, "New {$userType} registered");
                
                setFlashMessage('success', trans('register_success'));
                redirect(BASE_URL . '/public/login.php');
            } else {
                $errors[] = trans('register_failed');
            }
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            setFlashMessage('error', $error);
            break;
        }
    }
}

$pageTitle = trans('register');
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .register-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        .user-type-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }
        .user-type-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .user-type-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
        }
        .user-type-card.active {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .user-type-card i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 10px;
        }
        .school-fields {
            display: none;
        }
        .language-switch {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <div class="language-switch">
        <div class="btn-group">
            <a href="?lang=en" class="btn btn-sm <?php echo getCurrentLanguage() === 'en' ? 'btn-primary' : 'btn-outline-light'; ?>">English</a>
            <a href="?lang=rw" class="btn btn-sm <?php echo getCurrentLanguage() === 'rw' ? 'btn-primary' : 'btn-outline-light'; ?>">Kinyarwanda</a>
        </div>
    </div>

    <div class="register-container">
        <div class="register-card">
            <div class="text-center mb-4">
                <i class="bi bi-sign-stop text-primary" style="font-size: 48px;"></i>
                <h1 class="h3 text-primary"><?php echo trans('create_account'); ?></h1>
                <p class="text-muted"><?php echo trans('choose_account_type'); ?></p>
            </div>

            <?php echo displayFlashMessage(); ?>

            <form method="POST" action="" id="registerForm">
                <!-- User Type Selection -->
                <div class="user-type-selector">
                    <div class="user-type-card" data-type="learner">
                        <input type="radio" name="user_type" value="learner" id="type_learner" class="d-none" required>
                        <i class="bi bi-person"></i>
                        <h5><?php echo trans('individual_learner'); ?></h5>
                    </div>
                    <div class="user-type-card" data-type="school">
                        <input type="radio" name="user_type" value="school" id="type_school" class="d-none" required>
                        <i class="bi bi-building"></i>
                        <h5><?php echo trans('driving_school'); ?></h5>
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label"><?php echo trans('first_name'); ?> *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" 
                               value="<?php echo $_POST['first_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label"><?php echo trans('last_name'); ?> *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" 
                               value="<?php echo $_POST['last_name'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label"><?php echo trans('email'); ?> *</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo $_POST['email'] ?? ''; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label"><?php echo trans('phone'); ?> *</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?php echo $_POST['phone'] ?? ''; ?>" placeholder="07XXXXXXXX" required>
                </div>

                <!-- School Specific Fields -->
                <div class="school-fields">
                    <div class="mb-3">
                        <label for="school_name" class="form-label"><?php echo trans('school_name'); ?> *</label>
                        <input type="text" class="form-control" id="school_name" name="school_name" 
                               value="<?php echo $_POST['school_name'] ?? ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="registration_number" class="form-label"><?php echo trans('registration_number'); ?></label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" 
                               value="<?php echo $_POST['registration_number'] ?? ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label"><?php echo trans('address'); ?></label>
                        <textarea class="form-control" id="address" name="address" rows="2"><?php echo $_POST['address'] ?? ''; ?></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label"><?php echo trans('password'); ?> *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted"><?php echo trans('password_too_short'); ?></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label"><?php echo trans('confirm_password'); ?> *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="language" class="form-label"><?php echo trans('language_preference'); ?></label>
                    <select class="form-select" id="language" name="language">
                        <option value="en">English</option>
                        <option value="rw">Kinyarwanda</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus"></i> <?php echo trans('create_account'); ?>
                    </button>
                </div>

                <hr class="my-4">

                <div class="text-center">
                    <p class="mb-0"><?php echo trans('already_have_account'); ?></p>
                    <a href="login.php" class="btn btn-outline-primary mt-2">
                        <i class="bi bi-box-arrow-in-right"></i> <?php echo trans('sign_in_here'); ?>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Handle user type selection
        document.querySelectorAll('.user-type-card').forEach(card => {
            card.addEventListener('click', function() {
                const type = this.dataset.type;
                const radio = this.querySelector('input[type="radio"]');
                
                // Remove active class from all cards
                document.querySelectorAll('.user-type-card').forEach(c => c.classList.remove('active'));
                
                // Add active class to selected card
                this.classList.add('active');
                radio.checked = true;
                
                // Show/hide school fields
                const schoolFields = document.querySelector('.school-fields');
                if (type === 'school') {
                    schoolFields.style.display = 'block';
                    schoolFields.querySelectorAll('input').forEach(input => {
                        if (input.id === 'school_name') {
                            input.setAttribute('required', 'required');
                        }
                    });
                } else {
                    schoolFields.style.display = 'none';
                    schoolFields.querySelectorAll('input, textarea').forEach(input => {
                        input.removeAttribute('required');
                    });
                }
            });
        });

        // Handle language switch
        const urlParams = new URLSearchParams(window.location.search);
        const lang = urlParams.get('lang');
        if (lang) {
            fetch('switch-language.php?lang=' + lang)
                .then(() => window.location.href = 'register.php');
        }
    </script>
</body>
</html>

<?php
// Handle language switch
if (isset($_GET['lang'])) {
    switchLanguage($_GET['lang']);
    redirect(BASE_URL . '/public/register.php');
}
?>
