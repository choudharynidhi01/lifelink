<?php
session_start();

// Check file location to handle correct relative path
if (file_exists('db_connect.php')) {
    include 'db_connect.php';
} elseif (file_exists('../db_connect.php')) {
    include '../db_connect.php';
}

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = "";
$max_attempts = 5;
$lockout_time = 15 * 60; // 15 minutes

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

if (time() - $_SESSION['last_attempt_time'] > $lockout_time) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_SESSION['login_attempts'] >= $max_attempts) {
        $error = "Too many failed attempts. Please try again in 15 minutes.";
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (!empty($username) && !empty($password)) {
            $stmt = $conn->prepare("SELECT id, username, password, role FROM admins WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $admin = $result->fetch_assoc();

                if (password_verify($password, $admin['password']) || $password === $admin['password']) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['login_attempts'] = 0;

                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                    $error = "Invalid credentials. Attempts left: " . ($max_attempts - $_SESSION['login_attempts']);
                }
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                $error = "No admin account found with those credentials.";
            }
            $stmt->close();
        } else {
            $error = "Please fill in all required fields.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - LifeLink</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #ffe4e6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Light Ambient Watermarks */
        .bg-watermark {
            position: absolute;
            color: #e11d48;
            opacity: 0.04;
            pointer-events: none;
            z-index: 0;
        }
        .wm-left { top: -50px; left: -50px; font-size: 320px; }
        .wm-right { bottom: -50px; right: -50px; font-size: 350px; }

        /* Card Container */
        .login-card {
            width: 100%;
            max-width: 450px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 42px 36px;
            box-shadow: 0 20px 40px -15px rgba(225, 29, 72, 0.08), 0 10px 25px -5px rgba(15, 23, 42, 0.05);
            position: relative;
            z-index: 10;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 22px;
                border-radius: 20px;
            }
        }

        /* Brand Header */
        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-icon {
            width: 60px;
            height: 60px;
            background: #ffe4e6;
            border: 2px solid #fecdd3;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #e11d48;
            box-shadow: 0 8px 16px rgba(225, 29, 72, 0.12);
            margin-bottom: 14px;
        }

        .brand-header h2 {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .brand-header p {
            font-size: 14px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Inputs */
        .input-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .input-group label {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i.field-icon {
            position: absolute;
            left: 16px;
            color: #94a3b8;
            font-size: 16px;
            transition: color 0.2s;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 44px 14px 46px;
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            font-size: 14px;
            color: #0f172a;
            outline: none;
            transition: all 0.25s ease;
        }

        .input-wrapper input:focus {
            background: #ffffff;
            border-color: #e11d48;
            box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.12);
        }

        .input-wrapper input:focus ~ i.field-icon {
            color: #e11d48;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            color: #94a3b8;
            cursor: pointer;
            font-size: 15px;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: #334155;
        }

        /* Error Alert */
        .alert-error {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Action Button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(225, 29, 72, 0.25);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(225, 29, 72, 0.35);
        }

        .login-footer {
            text-align: center;
            margin-top: 26px;
            font-size: 13px;
            color: #64748b;
        }

        .login-footer a {
            color: #e11d48;
            font-weight: 600;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <i class="fa-solid fa-shield-halved bg-watermark wm-left"></i>
    <i class="fa-solid fa-heart-pulse bg-watermark wm-right"></i>

    <div class="login-card animate__animated animate__fadeInDown">
        <div class="brand-header">
            <div class="brand-icon">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h2>Admin Portal</h2>
            <p>LifeLink Management Operations</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error animate__animated animate__shakeX">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <div class="input-group">
                <label><i class="fa-solid fa-user-gear" style="color: #e11d48;"></i> Username or Email</label>
                <div class="input-wrapper">
                    <input type="text" name="username" placeholder="admin@lifelink.com" required autofocus>
                    <i class="fa-solid fa-user field-icon"></i>
                </div>
            </div>

            <div class="input-group">
                <label><i class="fa-solid fa-key" style="color: #e11d48;"></i> Security Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="passwordInput" placeholder="••••••••••••" required>
                    <i class="fa-solid fa-lock field-icon"></i>
                    <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-right-to-bracket"></i> Login to Portal
            </button>
        </form>

        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> LifeLink • <a href="index.php"><i class="fa-solid fa-house"></i> Public Site</a></p>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#passwordInput');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>