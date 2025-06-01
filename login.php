<?php
session_start();
require_once 'app/config/database.php'; // Ensure this path is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim input
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']); // Trim password as well

    try {
        // Prepare the SQL statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT id, full_name, password, role FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch as associative array

        // Check if user exists and verify password
        if ($user) {
            // Debugging output
            // Uncomment the following lines for debugging
            // echo "User  found: " . $user['full_name'] . "<br>";
            // echo "Stored password: " . $user['password'] . "<br>";
            // echo "Input password: " . $password . "<br>";

            if (password_verify($password, $user['password'])) {
                $role = strtolower(trim($user['role'])); // Clean role

                // Store user information in session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'nama' => $user['full_name'],
                    'role' => $role
                ];

                // Redirect based on role
                switch ($role) {
                    case 'admin':
                        header('Location: public/admin/dashboard.php');
                        break;
                    case 'guru':
                        header('Location: public/guru/dashboard.php');
                        break;
                    case 'siswa':
                        header('Location: public/user/dashboard.php');
                        break;
                    default:
                        $_SESSION['error'] = 'Role tidak dikenali.';
                        header('Location: login.php');
                        exit();
                }
                exit();
            } else {
                $_SESSION['error'] = 'Email atau password salah!';
            }
        } else {
            $_SESSION['error'] = 'Email atau password salah!';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
    }

    // Redirect back to login page if authentication fails
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Learning School</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- Custom Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                    },
                    boxShadow: {
                        'custom': '0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -6px rgba(59, 130, 246, 0.1)',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .form-container {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }
        .input-field {
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
            transform: translateY(-2px);
        }
        .btn-primary {
            background: linear-gradient(90deg, #0ea5e9, #3b82f6);
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #0284c7, #2563eb);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }
        .form-icon {
            color: #0ea5e9;
            transition: all 0.3s;
        }
        .input-group:focus-within .form-icon {
            color: #3b82f6;
            transform: scale(1.1);
        }
        .decoration-element {
            position: absolute;
            z-index: 0;
            opacity: 0.5;
        }
        .logo-shine {
            position: relative;
            overflow: hidden;
        }
        .logo-shine::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(30deg);
            animation: shine 3s infinite;
        }
        @keyframes shine {
            0% {transform: translateX(-100%) rotate(30deg);}
            100% {transform: translateX(100%) rotate(30deg);}
        }
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0% {transform: translateY(0px);}
            50% {transform: translateY(-10px);}
            100% {transform: translateY(0px);}
        }
    </style>
</head>
<body>
    <div class="flex justify-center items-center min-h-screen">
        <div class="form-container p-8 w-96 rounded-lg">
            <h2 class="text-center text-2xl font-semibold mb-6">Login</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="text-red-500 text-center mb-4">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="input-field w-full p-2 mt-2 rounded-md" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" class="input-field w-full p-2 mt-2 rounded-md" required>
                </div>
                <button type="submit" class="btn-primary w-full p-2 rounded-md text-white">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
