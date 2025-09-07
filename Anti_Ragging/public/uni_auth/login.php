<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';

// If already logged in, go to dashboard
if (!empty($_SESSION['ua_user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT ua.*, u.uni_name 
                                   FROM university_authorities ua 
                                   JOIN university u ON ua.university_id = u.uni_id 
                                   WHERE ua.email = ? AND ua.is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && isset($user['password_hash']) && password_verify($password, $user['password_hash'])) {
                $_SESSION['ua_user'] = [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'position' => $user['position'],
                    'department' => $user['department'],
                    'university_id' => (int)$user['university_id'],
                    'university_name' => $user['uni_name']
                ];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>University Authority Login</title>
    <link rel="icon" href="../../assets/images/favicon.ico">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .back-arrow {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 40px;
            height: 40px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #374151;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.2s;
            z-index: 10;
        }

        .back-arrow:hover {
            background: #f9fafb;
            transform: translateX(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .back-arrow svg {
            width: 20px;
            height: 20px;
        }

        .container {
            width: 100%;
            max-width: 420px;
            background: #fff;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            text-align: center;
            justify-content: center;
        }

        .brand img {
            height: 36px;
        }

        h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        p.sub {
            color: #6b7280;
            margin: 4px 0 0;
            font-size: 14px;
        }

        label {
            display: block;
            margin: 14px 0 6px;
            font-weight: 600;
            font-size: 14px;
        }

        input[type=email], input[type=password] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        input:focus {
            border-color: #0d6efd;
            outline: none;
        }

        button {
            margin-top: 20px;
            width: 100%;
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #0b5ed7;
        }

        .error {
            background: #fde8e8;
            color: #b42318;
            border: 1px solid #f3c5c5;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
            text-align: center;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
            margin-top: 16px;
            text-align: center;
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <a href="../index.php" class="back-arrow">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </a>

    <div class="container">
        <div class="brand">
            <img src="../../assets/images/logo.png" alt="Logo">
            <div>
                <h1>Authority Login</h1>
                <p class="sub">Sign in with your official university email</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label for="email">Official Email</label>
            <input id="email" name="email" type="email" placeholder="name@university.ac.lk" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Your password" required>

            <button type="submit">Continue</button>
        </form>

        <div class="muted">
            Your account must exist in the university authorities directory and be active.
        </div>
    </div>
</body>
</html>