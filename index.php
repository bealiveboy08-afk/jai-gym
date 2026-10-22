<?php
session_start();
include 'db.php';

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM gym_owners WHERE username='$username'");
    
    if ($result->num_rows > 0) {
        $owner = $result->fetch_assoc();
        
        // Blocked Account Check
        if ($owner['status'] == 'BLOCKED') {
            $error = "Your account is BLOCKED by Super Admin!";
        } else if (password_verify($password, $owner['password'])) {
            $_SESSION['gym_id'] = $owner['id'];
            $_SESSION['gym_name'] = $owner['gym_name'];
            header("Location: owner_dashboard.php");
            exit();
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "Gym Owner not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gym Owner Login - FitPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f8fafc; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background-color: #1e293b; border: 1px solid #334155; width: 100%; max-width: 400px; }
    </style>
</head>
<body>
    <div class="card login-card p-4 shadow-lg">
        <h3 class="text-center text-warning mb-3">⚡ FitPulse Login</h3>
        <p class="text-center text-muted small mb-4">Enter your credentials to access Gym Panel</p>

        <?php if ($error): ?>
            <div class="alert alert-danger p-2 text-center mb-3"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <button type="submit" name="login" class="btn btn-warning w-100 fw-bold">Login to Portal</button>
        </form>
    </div>
</body>
</html>