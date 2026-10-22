<?php
session_start();
include 'db.php';

// Check if Gym Owner is logged in
if (!isset($_SESSION['gym_id'])) {
    header("Location: index.php");
    exit();
}

$gym_id = $_SESSION['gym_id'];
$gym_name = $_SESSION['gym_name'];
$msg = "";
$error = "";

// Member Delete Karne Ka Logic
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $delete_sql = "DELETE FROM members WHERE id='$delete_id' AND gym_id='$gym_id'";
    if ($conn->query($delete_sql)) {
        header("Location: owner_dashboard.php?deleted=1");
        exit();
    } else {
        $error = "Failed to delete member.";
    }
}

// Handle URL messages for redirects
if (isset($_GET['success'])) {
    $msg = "Member successfully added!";
}
if (isset($_GET['deleted'])) {
    $msg = "Member successfully cancelled/deleted!";
}

// Member Add karne ka Logic
if (isset($_POST['add_member'])) {
    $name = trim($_POST['name']);
    $age = (int)$_POST['age'];
    $weight = (float)$_POST['weight'];
    $plan_months = (int)$_POST['plan_months'];
    $has_trainer = isset($_POST['has_trainer']) ? 1 : 0;
    $wants_protein = isset($_POST['wants_protein']) ? 1 : 0;
    $protein_brand = isset($_POST['protein_brand']) ? $_POST['protein_brand'] : 'None';

    // Age Check Validation (Min 15 Years)
    if ($age < 15) {
        $error = "Member age must be at least 15 years!";
    } else {
        // Price Calculation Logic
        $monthly_rate = 1000; // Base Fee per month
        $base_fee = $plan_months * $monthly_rate;
        
        $trainer_fee = $has_trainer ? ($plan_months * 500) : 0; // ₹500/month for Personal Trainer
        
        $protein_fee = 0;
        if ($wants_protein) {
            if ($protein_brand == 'Whey Protein') $protein_fee = 3000;
            else if ($protein_brand == 'Isolate') $protein_fee = 4500;
            else if ($protein_brand == 'Mass Gainer') $protein_fee = 2500;
            else if ($protein_brand == 'Creatine') $protein_fee = 1000;
            else if ($protein_brand == 'clax') $protein_fee = 2000;
        } else {
            $protein_brand = 'None';
        }

        $total_amount = $base_fee + $trainer_fee + $protein_fee;

        $join_date = date('Y-m-d');
        $expiry_date = date('Y-m-d', strtotime("+$plan_months months"));

        $stmt = $conn->prepare("INSERT INTO members (gym_id, name, age, weight, plan_months, has_trainer, trainer_fee, wants_protein, protein_brand, protein_fee, total_amount, join_date, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdiiiisiiiss", $gym_id, $name, $age, $weight, $plan_months, $has_trainer, $trainer_fee, $wants_protein, $protein_brand, $protein_fee, $total_amount, $join_date, $expiry_date);

        if ($stmt->execute()) {
            header("Location: owner_dashboard.php?success=1");
            exit();
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}

// Fetch Members for this Gym
$members = $conn->query("SELECT * FROM members WHERE gym_id='$gym_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($gym_name) ?> - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #1e1e2e; color: #f8fafc; }
        .card { background-color: #1e293b; border: 1px solid #334155; color: #f8fafc; }
        .table { color: #f8fafc; }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-warning">🏋️ <?= htmlspecialchars($gym_name) ?> Console</h2>
            <a href="index.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Add Member Form -->
        <div class="card p-4 mb-4">
            <h4 class="mb-3">➕ Add New Gym Member</h4>
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Member Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Full Name">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Age (Min 15)</label>
                    <input type="number" name="age" class="form-control" min="15" required placeholder="Age">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Weight (kg)</label>
                    <input type="number" step="0.1" name="weight" class="form-control" required placeholder="65.5">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Plan Duration</label>
                    <select name="plan_months" class="form-select" required>
                        <option value="1">1 Month (₹1000)</option>
                        <option value="3">3 Months (₹3000)</option>
                        <option value="6">6 Months (₹6000)</option>
                        <option value="12">12 Months (₹12000)</option>
                    </select>
                </div>
                
                <div class="col-md-6 mt-4">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="has_trainer" id="trainerSwitch" value="1">
                        <label class="form-check-label" for="trainerSwitch">Personal Trainer (+₹500/month)</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="wants_protein" id="proteinSwitch" value="1" onchange="toggleProtein(this)">
                        <label class="form-check-label" for="proteinSwitch">Add Protein Supplement</label>
                    </div>
                </div>

                <div class="col-md-6 mt-3" id="proteinSelectDiv" style="display: none;">
                    <label class="form-label">Select Protein Supplement</label>
                    <select name="protein_brand" class="form-select">
                        <option value="Whey Protein">Whey Protein (+₹3000)</option>
                        <option value="Isolate">Isolate Protein (+₹4500)</option>
                        <option value="Mass Gainer">Mass Gainer (+₹2500)</option>
                        <option value="Creatine">Creatine (+₹1000)</option>
                        <option value="clax">clax (+₹1700)</option>
                    </select>
                </div>

                <div class="col-12 text-end mt-3">
                    <button type="submit" name="add_member" class="btn btn-warning fw-bold px-4">+ Save & Calculate Bill</button>
                </div>
            </form>
        </div>

        <!-- Members List -->
        <div class="card p-4">
            <h4 class="mb-3">📋 Active Members & Subscriptions</h4>
            <table class="table table-dark table-striped align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Age / Weight</th>
                        <th>Plan</th>
                        <th>Add-ons</th>
                        <th>Total Amount</th>
                        <th>Expiry Date</th>
                        <th>Countdown</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($m = $members->fetch_assoc()): 
                        $today = new DateTime();
                        $expiry = new DateTime($m['expiry_date']);
                        $interval = $today->diff($expiry);
                        $days_left = (int)$interval->format("%r%a");
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                        <td><?= $m['age'] ?> yrs / <?= $m['weight'] ?> kg</td>
                        <td><span class="badge bg-info text-dark"><?= $m['plan_months'] ?> Months</span></td>
                        <td>
                            <?php if ($m['has_trainer']): ?>
                                <span class="badge bg-primary">PT</span>
                            <?php endif; ?>
                            <?php if ($m['wants_protein'] && $m['protein_brand'] != 'None'): ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($m['protein_brand']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-warning fw-bold">₹<?= number_format($m['total_amount']) ?></td>
                        <td><?= $m['expiry_date'] ?></td>
                        <td>
                            <?php if ($days_left > 0): ?>
                                <span class="badge bg-success"><?= $days_left ?> Days Left</span>
                            <?php elseif ($days_left == 0): ?>
                                <span class="badge bg-warning text-dark">Expires Today</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Expired</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="owner_dashboard.php?delete_id=<?= $m['id'] ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Kya aap sach me is member ko delete/cancel karna chahte hain?');">
                               🗑️ Cancel / Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleProtein(checkbox) {
            document.getElementById('proteinSelectDiv').style.display = checkbox.checked ? 'block' : 'none';
        }
    </script>
</body>
</html>