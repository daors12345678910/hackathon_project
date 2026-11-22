
<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Database statistics
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCharities = $conn->query("SELECT COUNT(*) FROM charity_profiles")->fetchColumn();
$totalRequests = $conn->query("SELECT COUNT(*) FROM clothing_needed")->fetchColumn();
$totalDonations = $conn->query("SELECT COUNT(*) FROM donation")->fetchColumn();

// All users
$users = $conn->query(
    "SELECT id, emri, username, email, role FROM users ORDER BY id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// All clothing requests
$requests = $conn->query(
    "SELECT cn.*, cp.charity_name
     FROM clothing_needed cn
     INNER JOIN charity_profiles cp ON cn.charity_id = cp.id
     ORDER BY cn.request_id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// All donations
$donations = $conn->query(
    "SELECT d.*, u.username
     FROM donation d
     INNER JOIN users u ON d.donor_id = u.id
     ORDER BY d.donation_id DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ThreadKind | Admin Dashboard</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="topbar">
    <a href="index.php" class="brand">ThreadKind</a>
    <nav>
        <a class="active" href="admin.php">Administration</a>
        <a href="#users">Users</a>
        <a href="#requests">Requests</a>
        <a href="#donations">Donations</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="container">

    <section class="welcome">
        <p class="eyebrow">ADMINISTRATION PORTAL</p>
        <h1>Welcome, <?= e($_SESSION['emri'] ?? $_SESSION['username']) ?>.</h1>
        <p>Monitor the activity of your Clothes Donation Match platform.</p>
    </section>

    <section class="admin-stats">

        <article class="stat-card">
            <span>Total users</span>
            <strong><?= e($totalUsers) ?></strong>
        </article>

        <article class="stat-card">
            <span>Charity profiles</span>
            <strong><?= e($totalCharities) ?></strong>
        </article>

        <article class="stat-card">
            <span>Clothing requests</span>
            <strong><?= e($totalRequests) ?></strong>
        </article>

        <article class="stat-card">
            <span>Donations</span>
            <strong><?= e($totalDonations) ?></strong>
        </article>

    </section>

    <section id="users" class="panel">
        <h2>Registered users</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= e($user['id']) ?></td>
                            <td><?= e($user['emri']) ?></td>
                            <td><?= e($user['username']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><?= e($user['role']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section id="requests" class="panel">
        <h2>Clothing requests</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Charity</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Urgency</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= e($request['request_id']) ?></td>
                            <td><?= e($request['charity_name']) ?></td>
                            <td><?= e($request['category']) ?></td>
                            <td><?= e($request['description']) ?></td>
                            <td><?= e($request['quantity']) ?></td>
                            <td><?= e($request['urgency']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section id="donations" class="panel">
        <h2>All donations</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Donation ID</th>
                        <th>Donor</th>
                        <th>Request ID</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donations as $donation): ?>
                        <tr>
                            <td><?= e($donation['donation_id']) ?></td>
                            <td><?= e($donation['username']) ?></td>
                            <td><?= e($donation['request_id']) ?></td>
                            <td><?= e($donation['description']) ?></td>
                            <td><?= e($donation['quantity']) ?></td>
                            <td><?= e($donation['date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>
</body>
</html>
