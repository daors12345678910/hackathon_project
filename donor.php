
<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'donor') {
    header("Location: index.php");
    exit();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donate'])) {

    $request_id = (int)$_POST['request_id'];
    $quantity = (int)$_POST['quantity'];
    $size = trim($_POST['clothing_size']);

    if ($request_id > 0 && $quantity > 0 && $quantity <= 100 &&
        in_array($size, ['XS', 'S', 'M', 'L', 'XL', 'XXL'], true)) {

        $check = $conn->prepare(
            "SELECT category FROM clothing_needed WHERE request_id = :id"
        );
        $check->execute([':id' => $request_id]);
        $request = $check->fetch(PDO::FETCH_ASSOC);

        if ($request) {
            $sql = "INSERT INTO donation
                    (request_id, donor_id, description, date, image, quantity, clothing_size)
                    VALUES (:request_id, :donor_id, :description, :date, :image, :quantity, :size)";

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                ':request_id' => $request_id,
                ':donor_id' => $_SESSION['id'],
                ':description' => $request['category'],
                ':date' => date('Y-m-d'),
                ':image' => '',
                ':quantity' => $quantity,
                ':size' => $size
            ]);

            header("Location: donor.php?success=1");
            exit();
        }
    }

    header("Location: donor.php?error=1");
    exit();
}

// Search and filters
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$urgency = trim($_GET['urgency'] ?? '');

$sql = "SELECT cn.*, cp.charity_name, cp.city
        FROM clothing_needed cn
        INNER JOIN charity_profiles cp ON cn.charity_id = cp.id
        WHERE 1=1";

$params = [];

if ($search !== '') {
    $sql .= " AND (cn.category LIKE :search
              OR cn.description LIKE :search
              OR cp.charity_name LIKE :search
              OR cp.city LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($category !== '') {
    $sql .= " AND cn.category = :category";
    $params[':category'] = $category;
}

if ($urgency !== '') {
    $sql .= " AND cn.urgency = :urgency";
    $params[':urgency'] = $urgency;
}

$sql .= " ORDER BY cn.request_id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get donor's donations
$donationStmt = $conn->prepare(
    "SELECT COUNT(*) FROM donation WHERE donor_id = :id"
);
$donationStmt->execute([':id' => $_SESSION['id']]);
$donationCount = $donationStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ThreadKind | Donor Dashboard</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="topbar">
    <a class="brand" href="index.php">ThreadKind</a>

    <nav>
        <a class="active" href="donor.php">Discover</a>
        <a href="#myDonations">My donations (<?= e($donationCount) ?>)</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="container">

    <section class="welcome">
        <p class="eyebrow">DONOR PORTAL</p>
        <h1>Find a local need.</h1>
        <p>Give clothes a second life and help someone in your community.</p>
    </section>

    <?php if (isset($_GET['success'])): ?>
        <div class="message success">Your donation has been recorded!</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="message error">Please check your donation details and try again.</div>
    <?php endif; ?>

    <!-- SEARCH AND FILTER BAR - KEPT -->
    <form method="GET" class="filter-row">

        <input
            type="search"
            name="search"
            placeholder="Search clothing or charity..."
            value="<?= e($search) ?>"
        >

        <select name="category">
            <option value="">All categories</option>
            <?php foreach (['footwear', 'hats', 'accessory', 'clothes', 'coats'] as $item): ?>
                <option value="<?= e($item) ?>"
                    <?= $category === $item ? 'selected' : '' ?>>
                    <?= e(ucfirst($item)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="urgency">
            <option value="">All priorities</option>
            <option value="urgent" <?= $urgency === 'urgent' ? 'selected' : '' ?>>Urgent</option>
            <option value="standard" <?= $urgency === 'standard' ? 'selected' : '' ?>>Standard</option>
            <option value="winter" <?= $urgency === 'winter' ? 'selected' : '' ?>>Winter</option>
        </select>

        <button type="submit" class="primary-btn">Search</button>
        <a href="donor.php" class="reset-btn">Clear</a>

    </form>

    <div class="section-heading">
        <h2>Clothing requests</h2>
        <span><?= count($requests) ?> requests found</span>
    </div>

    <section class="needs-grid">

        <?php if (count($requests) > 0): ?>

            <?php foreach ($requests as $request): ?>

                <article class="need-card">

                    <div class="card-top">
                        <span class="category">
                            <?= e(ucfirst($request['category'])) ?>
                        </span>

                        <span class="urgency">
                            <?= e(ucfirst($request['urgency'])) ?>
                        </span>
                    </div>

                    <h3><?= e($request['description']) ?></h3>

                    <p><?= e($request['charity_name']) ?></p>

                    <p class="muted"><?= e($request['city']) ?></p>

                    <div class="request-details">
                        <span>Size: <?= e($request['clothing_size']) ?></span>
                        <span>Quantity: <?= e($request['quantity']) ?></span>
                    </div>

                    <p class="muted">Needed by: <?= e($request['date']) ?></p>

                    <form method="POST" class="donation-form">
                        <input type="hidden" name="request_id"
                               value="<?= e($request['request_id']) ?>">

                        <label>Quantity to donate</label>
                        <input type="number" name="quantity"
                               min="1" max="100" value="1" required>

                        <label>Clothing size</label>
                        <select name="clothing_size" required>
                            <option>XS</option>
                            <option>S</option>
                            <option selected>M</option>
                            <option>L</option>
                            <option>XL</option>
                            <option>XXL</option>
                        </select>

                        <button type="submit" name="donate" class="primary-btn">
                            Donate now
                        </button>
                    </form>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <p class="empty">No clothing requests match your search.</p>

        <?php endif; ?>

    </section>

    <section id="myDonations" class="history-section">
        <h2>My donation history</h2>

        <?php
        $history = $conn->prepare(
            "SELECT * FROM donation
             WHERE donor_id = :id
             ORDER BY donation_id DESC"
        );
        $history->execute([':id' => $_SESSION['id']]);
        $donations = $history->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if ($donations): ?>
            <?php foreach ($donations as $donation): ?>
                <div class="history-card">
                    <strong><?= e($donation['description']) ?></strong>
                    <span>Quantity: <?= e($donation['quantity']) ?></span>
                    <span>Size: <?= e($donation['clothing_size']) ?></span>
                    <span><?= e($donation['date']) ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You haven't made any donations yet.</p>
        <?php endif; ?>

    </section>

</main>

</body>
</html>
