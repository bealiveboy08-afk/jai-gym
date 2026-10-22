<?php
include 'db.php';

// Naya Gym Owner add karne ka logic
if (isset($_POST['add_owner'])) {
    $gym_name = $_POST['gym_name'];
    $owner_name = $_POST['owner_name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password

    $sql = "INSERT INTO gym_owners (gym_name, owner_name, username, password) VALUES ('$gym_name', '$owner_name', '$username', '$password')";
    if ($conn->query($sql)) {
        echo "<script>alert('Gym Owner successfully added!'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Error: Username might already exist.');</script>";
    }
}

// Account Status (ACTIVE/BLOCKED) change karne ka logic
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $current_status = $_GET['toggle_status'];
    $new_status = ($current_status == 'ACTIVE') ? 'BLOCKED' : 'ACTIVE';

    $conn->query("UPDATE gym_owners SET status='$new_status' WHERE id=$id");
    header("Location: admin.php");
}

// Saare owners fetch karna
$owners_result = $conn->query("SELECT * FROM gym_owners ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FitPulse - Super Admin Console</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f8fafc; }
        .card { background-color: #1e293b; border: 1px solid #334155; color: #f8fafc; }
        .table { color: #f8fafc; }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <h2 class="mb-4 text-warning">⚡ FitPulse Super Admin Console</h2>

        <!-- Add Owner Form -->
        <div class="card p-4 mb-4">
            <h4>Create New Gym Owner Account</h4>
            <form method="POST" class="row g-3 mt-1">
                <div class="col-md-3">
                    <input type="text" name="gym_name" class="form-control" placeholder="Gym Name (e.g. FitZone)" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="owner_name" class="form-control" placeholder="Owner Name" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="username" class="form-control" placeholder="Set Username" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="password" class="form-control" placeholder="Set Password"  required>
                    
                </div>
                <div class="col-12 text-end">
                    <button type="submit" name="add_owner" class="btn btn-warning fw-bold">+ Add Gym Owner</button>
                </div>
            </form>
        </div>

        <!-- Owners List Table -->
        <div class="card p-4">
            <h4>Registered Gym Owners</h4>
            <table class="table table-dark table-striped align-middle mt-3">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Gym Name</th>
                        <th>Owner Name</th>
                        <th>Username</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $owners_result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><strong><?= htmlspecialchars($row['gym_name']) ?></strong></td>
                        <td><?= htmlspecialchars($row['owner_name']) ?></td>
                        <td><code><?= htmlspecialchars($row['username']) ?></code></td>
                        <td>
                            <span class="badge <?= $row['status'] == 'ACTIVE' ? 'bg-success' : 'bg-danger' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="admin.php?toggle_status=<?= $row['status'] ?>&id=<?= $row['id'] ?>" 
                               class="btn btn-sm <?= $row['status'] == 'ACTIVE' ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                <?= $row['status'] == 'ACTIVE' ? 'Block Account' : 'Activate Account' ?>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>