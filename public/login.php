<?php
/**
 * Login Page
 */

require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $userType = getCurrentUserType();
    switch ($userType) {
        case 'admin':
            redirect(BASE_URL . '/admin/dashboard.php');
            break;
        case 'school':
            redirect(BASE_URL . '/school/dashboard.php');
            break;
        case 'learner':
            redirect(BASE_URL . '/learner/dashboard.php');
            break;
        default:
            redirect(BASE_URL . '/public/dashboard.php');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_me']);
    
    // Validation
    $errors = [];
    
    if (empty($email)) {
        $errors[] = trans('field_required') . ': ' . trans('email');
    } elseif (!validateEmail($email)) {
        $errors[] = trans('invalid_email');
    }
    
    if (empty($password)) {
        $errors[] = trans('field_required') . ': ' . trans('password');
    }
    
    if (empty($errors)) {
        $user = new User($db);
        $authenticatedUser = $user->authenticate($email, $password);
        
        if ($authenticatedUser) {
            // Set session
            setUserSession($authenticatedUser);
            
            // Set remember me cookie if checked
            if ($remember) {
                $token = generateToken();
                setRememberMe($authenticatedUser['id'], $token);
                // TODO: Store token in database for verification
            }
            
            // Log activity
            logActivity('User Login', $authenticatedUser['id'], 'Successful login');
            
            setFlashMessage('success', trans('login_success'));
            
            // Redirect based on user type
            switch ($authenticatedUser['user_type']) {
                case 'admin':
                    redirect(BASE_URL . '/admin/dashboard.php');
                    break;
                case 'school':
                    redirect(BASE_URL . '/school/dashboard.php');
                    break;
                case 'learner':
                    redirect(BASE_URL . '/learner/dashboard.php');
                    break;
            }
        } else {
            setFlashMessage('error', trans('login_failed'));
        }
    } else {
        foreach ($errors as $error) {
            setFlashMessage('error', $error);
            break; // Show only first error
        }
    }
}

$pageTitle = trans('login');
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo h1 {
            color: #667eea;
            font-weight: 700;
            font-size: 28px;
        }
        .language-switch {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
    </style>
</head>
<body>
    <!-- Language Switch -->
    <div class="language-switch">
        <div class="btn-group">
            <a href="?lang=en" class="btn btn-sm <?php echo getCurrentLanguage() === 'en' ? 'btn-primary' : 'btn-outline-light'; ?>">English</a>
            <a href="?lang=rw" class="btn btn-sm <?php echo getCurrentLanguage() === 'rw' ? 'btn-primary' : 'btn-outline-light'; ?>">Kinyarwanda</a>
        </div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <i class="bi bi-sign-stop text-primary" style="font-size: 48px;"></i>
                <h1><?php echo SITE_NAME; ?></h1>
                <p class="text-muted"><?php echo trans('welcome'); ?></p>
            </div>

            <?php echo displayFlashMessage(); ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope"></i> <?php echo trans('email'); ?>
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo $_POST['email'] ?? ''; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock"></i> <?php echo trans('password'); ?>
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">
                        <?php echo trans('remember_me'); ?>
                    </label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> <?php echo trans('login'); ?>
                    </button>
                </div>

                <div class="text-center mt-3">
                    <a href="forgot-password.php" class="text-decoration-none">
                        <?php echo trans('forgot_password'); ?>
                    </a>
                </div>

                <hr class="my-4">

                <div class="text-center">
                    <p class="mb-0"><?php echo trans('dont_have_account'); ?></p>
                    <a href="register.php" class="btn btn-outline-primary mt-2">
                        <i class="bi bi-person-plus"></i> <?php echo trans('sign_up_here'); ?>
                    </a>
                </div>
            </form>
        </div>

        <div class="text-center mt-3">
            <a href="index.php" class="text-white text-decoration-none">
                <i class="bi bi-arrow-left"></i> <?php echo trans('back'); ?> <?php echo trans('home'); ?>
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Handle language switch
        const urlParams = new URLSearchParams(window.location.search);
        const lang = urlParams.get('lang');
        if (lang) {
            fetch('switch-language.php?lang=' + lang)
                .then(() => window.location.href = 'login.php');
        }
    </script>
</body>
</html>

<?php
// Handle language switch
if (isset($_GET['lang'])) {
    switchLanguage($_GET['lang']);
    redirect(BASE_URL . '/public/login.php');
}
?>
