
<?php
session_start();
include_once('config.php');

// Only admins can access this page
if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Count total users
$userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Count charities
$charityCount = $conn->query("SELECT COUNT(*) FROM charity_profiles")->fetchColumn();

// Count clothing requests
$requestCount = $conn->query("SELECT COUNT(*) FROM clothing_needed")->fetchColumn();

// Count donations
$donationCount = $conn->query("SELECT COUNT(*) FROM donation")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kind Society | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="admin-layout">
<aside class="admin-sidebar">
    <a class="side-brand" href="admin.php"><span class="brand-mark">K</span><span>Kind Society<small>ADMIN CONSOLE</small></span></a>
    <div class="side-caption">WORKSPACE</div>
    <a class="side-link active" href="admin.php">▦ <span>Overview</span></a>
    <a class="side-link" href="manageUsers.php">♙ <span>Users</span></a>
    <a class="side-link" href="manageCharities.php">♡ <span>Charities</span></a>
    <a class="side-link" href="manageRequests.php">▤ <span>Clothing requests</span></a>
    <a class="side-link" href="manageDonations.php">♧ <span>Donations</span></a>
    <div class="sidebar-bottom"><div class="side-user"><span class="avatar">A</span><span><strong><?= htmlspecialchars($_SESSION['emri']) ?></strong><small>Administrator</small></span></div><a class="side-logout" href="logout.php">Log out ↗</a></div>
</aside>
<main class="admin-main">
    <header class="admin-topline"><div><span class="eyebrow-dark">ADMINISTRATION</span><h1>Dashboard overview</h1><p>Monitor activity and manage the Kind Society community.</p></div><span class="admin-date">Kind Society · Admin</span></header>
    <section class="admin-stat-grid">
        <article class="admin-stat"><span class="stat-icon">♙</span><p>Total users</p><strong><?= $userCount ?></strong><small>Registered accounts</small></article>
        <article class="admin-stat"><span class="stat-icon">♡</span><p>Charities</p><strong><?= $charityCount ?></strong><small>Community partners</small></article>
        <article class="admin-stat"><span class="stat-icon">▤</span><p>Clothing requests</p><strong><?= $requestCount ?></strong><small>Active needs listed</small></article>
        <article class="admin-stat"><span class="stat-icon">♧</span><p>Donations</p><strong><?= $donationCount ?></strong><small>Contributions recorded</small></article>
    </section>
    <section class="admin-section-head"><div><span class="eyebrow-dark">CONTROL CENTER</span><h2>Manage your platform</h2><p>Choose an area to view and manage its records.</p></div></section>
    <section class="admin-management-grid">
        <a class="management-tile" href="manageUsers.php"><span class="tile-icon">♙</span><span class="tile-arrow">↗</span><h3>User accounts</h3><p>View and manage donor, charity, and administrator accounts.</p><span class="tile-action">Manage users</span></a>
        <a class="management-tile" href="manageCharities.php"><span class="tile-icon">♡</span><span class="tile-arrow">↗</span><h3>Charity profiles</h3><p>Maintain charity information and community partners.</p><span class="tile-action">Manage charities</span></a>
        <a class="management-tile" href="manageRequests.php"><span class="tile-icon">▤</span><span class="tile-arrow">↗</span><h3>Clothing requests</h3><p>Review, create, update, and remove clothing requests.</p><span class="tile-action">Manage requests</span></a>
        <a class="management-tile" href="manageDonations.php"><span class="tile-icon">♧</span><span class="tile-arrow">↗</span><h3>Donations</h3><p>Review and manage donations submitted by donors.</p><span class="tile-action">Manage donations</span></a>
    </section>
    <footer class="admin-footer">Kind Society · Making generosity easier, one donation at a time.</footer>

<style>
    /* Make dark text white throughout admin management pages */
.admin-content,
.admin-content h1,
.admin-content h2,
.admin-content h3,
.admin-content h4,
.admin-content h5,
.admin-content p,
.admin-content label,
.admin-content span,
.admin-content th,
.admin-content td,
.admin-content a,
.admin-content strong,
.admin-content small {
    color: #ffffff !important;
}

/* Form labels and input text */
.admin-content input,
.admin-content select,
.admin-content textarea {
    color: #ffffff !important;
}

/* Dropdown options */
.admin-content select option {
    background-color: #1f2937;
    color: #ffffff;
}

/* Placeholder text */
.admin-content input::placeholder,
.admin-content textarea::placeholder {
    color: #d1d5db !important;
}

/* Table text */
.admin-content table {
    color: #ffffff !important;

    
}

/* FORCE WHITE TEXT ON ALL ADMIN MANAGEMENT PAGES */

body,
body h1,
body h2,
body h3,
body h4,
body h5,
body h6,
body p,
body label,
body span,
body strong,
body small,
body th,
body td,
body a,
body li,
body legend,
body .form-label,
body .card-title,
body .card-text,
body .modal-title {
    color: #ffffff !important;
}

/* Form fields */
body input,
body select,
body textarea {
    color: #ffffff !important;
}

/* Dropdown options */
body select option {
    background: #1e293b !important;
    color: #ffffff !important;
}

/* Placeholders */
body input::placeholder,
body textarea::placeholder {
    color: #d1d5db !important;
}

/* Bootstrap table text */
body .table,
body .table th,
body .table td {
    color: #ffffff !important;
}</style>
</main>
</body>
</html>
