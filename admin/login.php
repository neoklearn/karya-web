<?php
session_start();

// Gunakan __DIR__ untuk memastikan path yang benar ke file admin.json di root
$adminFile = __DIR__ . '/../admin.json';
if (!file_exists($adminFile)) {
    die("File kredensial admin tidak ditemukan.");
}
$adminData = json_decode(file_get_contents($adminFile), true);
$usernameStored = trim($adminData['username']);
$passwordStoredHash = trim($adminData['password']); // Ini adalah hash

$error = '';

// Inisialisasi counter kesalahan jika belum ada
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validasi password: maksimal 12 karakter dan tidak boleh mengandung simbol tertentu
    if (strlen($password) > 12 || preg_match('/[(){}\[\]><"\'`]/', $password)) {
        $_SESSION['attempts']++;
        if ($_SESSION['attempts'] >= 3) {
            echo "<script>alert('Maaf, terjadi kesalahan. Silahkan tulis username dan password dengan benar.'); window.location.href='../index.php';</script>";
            exit;
        } else {
            echo "<script>alert('Maaf, terjadi kesalahan. Silahkan tulis username dan password dengan benar.'); window.location.href='../index.php';</script>";
            exit;
        }
    }

    // Jika validasi password lolos, periksa kredensial
    if ($username === $usernameStored && password_verify($password, $passwordStoredHash)) {
        $_SESSION['attempts'] = 0; // reset counter jika login berhasil
        $_SESSION['loggedin'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        html,
        body {
            -webkit-user-select: none;
            /* Chrome, Safari, Opera */
            -moz-user-select: none;
            /* Firefox */
            -ms-user-select: none;
            /* Internet Explorer/Edge */
            user-select: none;
            /* Standard syntax */
        }

        /* Kecualikan elemen input dan textarea agar user masih bisa menyalin teks pada form */
        input,
        textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }

        body {
            font-family: Arial, sans-serif;
            background: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            max-width: 350px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            background: #f9f9f9;
            box-sizing: border-box;
            text-align: center;
        }

        .login-container h2 {
            margin-top: 0;
        }

        .login-container label {
            display: block;
            margin: 10px 0 5px;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .login-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .header {
            background: #007BFF;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
            position: fixed;
            top: 0;
            left: 0;
        }

        .header a.logout {
            background: #0056b3;
            color: #fff;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 3px;
        }

        .error {
            color: red;
            text-align: center;
        }
    </style>
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <script>
            // Jika sudah login, pencegahan back button:
            history.pushState(null, null, window.location.href);
            window.addEventListener('popstate', function(event) {
                window.location.href = "dashboard.php";
            });
        </script>
    <?php endif; ?>
</head>

<body>
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <div class="header">
            <h2>Login Admin</h2>
            <a class="logout" href="logout.php">Logout</a>
        </div>
        <div class="login-container" style="margin-top:60px;">
            <h2>Anda sudah login.</h2>
            <p>Klik di bawah untuk kembali ke Dashboard:</p>
            <a href="dashboard.php">Kembali ke Dashboard</a>
        </div>
    <?php else: ?>
        <div class="login-container">
            <h2>Login Admin</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
                <input type="submit" name="login" value="Login">
            </form>
        </div>
    <?php endif; ?>
</body>

</html>