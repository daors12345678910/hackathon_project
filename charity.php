
<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'charity') {
    header("Location: index.php");
    exit();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Find the charity profile belonging to this user
$stmt = $conn->prepare(
    "SELECT * FROM charity_profiles WHERE user_id = :user_id"
);
$stmt->execute([':user_id' => $_SESSION['id']]);
$charity = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$charity) {
    die("Your charity profile has not been connected to your account. Please contact the administrator.");
}

// Publish a request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publish'])) {

    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $urgency = trim($_POST['urgency']);
    $date = $_POST['date'];
    $quantity = (int)$_POST['quantity'];
    $size = trim($_POST['size']);

    $allowedCategories = ['footwear', 'hats', 'accessory', 'clothes', 'coats'];
    $allowedUrgency = ['urgent', 'standard', 'winter'];
    $allowedSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    if (
        in_array($category, $allowedCategories, true) &&
        in_array($urgency, $allowedUrgency, true) &&
        in_array($size, $allowedSizes, true) &&
        $description !== '' &&
        $quantity > 0 &&
        $quantity <= 1000
    ) {
        $insert = $conn->prepare(
            "INSERT INTO clothing_needed
            (charity_id, category, description, urgency, date, image, quantity, clothing_size)
            VALUES (:charity_id, :category, :description, :urgency, :date, :image, :quantity, :size)"
        );

        $insert->execute([
            ':charity_id' => $charity['id'],
            ':category' => $category,
            ':description' => $description,
            ':urgency' => $urgency,
            ':date' => $date,
            ':image' => '',
            ':quantity' => $quantity,
            ':size' => $size
        ]);

        header("Location: charity.php?success=1");
        exit();
    }

    header("Location: charity.php?error=1");
    exit();
}

// Retrieve this charity's requests
$requests = $conn->prepare(
    "SELECT * FROM clothing_needed
     WHERE charity_id = :id
     ORDER BY request_id DESC"
);
$requests->execute([':id' => $charity['id']]);
$myRequests = $requests->fetchAll(PDO::FETCH_ASSOC);

// Retrieve donations for this charity
$donations = $conn->prepare(
    "SELECT d.*, cn.category
     FROM donation d
     INNER JOIN clothing_needed cn ON d.request_id = cn.request_id
     WHERE cn.charity_id = :id
     ORDER BY d.donation_id DESC"
);
$donations->execute([':id' => $charity['id']]);
$myDonations = $donations->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kind Society | Charity Dashboard</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="topbar">
    <a href="index.php" class="brand">Kind Society</a>
    <nav>
        <a class="active" href="charity.php">My dashboard</a>
        <a href="#requests">My requests</a>
        <a href="#donations">Donations</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="container">

    <section class="welcome">
        <p class="eyebrow">CHARITY PORTAL</p>
        <h1>Welcome, <?= e($charity['charity_name']) ?>.</h1>
        <p>Manage your clothing needs and connect with generous donors.</p>
        <p class="muted"><?= e($charity['city']) ?></p>
    </section>

    <?php if (isset($_GET['success'])): ?>
        <div class="message success">Your request has been published!</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="message error">Please check all the request details.</div>
    <?php endif; ?>

    <section class="panel">
        <h2>Post a clothing request</h2>

        <form method="POST" class="form-grid">

            <label>Category
                <select name="category" required>
                    <option value="clothes">Clothes</option>
                    <option value="coats">Coats</option>
                    <option value="footwear">Footwear</option>
                    <option value="hats">Hats</option>
                    <option value="accessory">Accessories</option>
                </select>
            </label>

            <label>Clothing size
                <select name="size" required>
                    <option>XS</option>
                    <option>S</option>
                    <option>M</option>
                    <option>L</option>
                    <option>XL</option>
                    <option>XXL</option>
                </select>
            </label>

            <label>Quantity needed
                <input type="number" name="quantity" min="1" max="1000" required>
            </label>

            <label>Urgency
                <select name="urgency" required>
                    <option value="standard">Standard</option>
                    <option value="urgent">Urgent</option>
                    <option value="winter">Winter</option>
                </select>
            </label>

            <label>Needed by
                <input type="date" name="date" min="<?= date('Y-m-d') ?>" required>
            </label>

            <label>Description
                <input type="text" name="description"
                       placeholder="Describe the clothing needed" required>
            </label>

            <button type="submit" name="publish" class="primary-btn">
                Publish request
            </button>

        </form>
    </section>

    <section id="requests" class="history-section">
        <h2>My clothing requests</h2>

        <?php if ($myRequests): ?>
            <?php foreach ($myRequests as $request): ?>
                <article class="history-card">
                    <strong><?= e($request['description']) ?></strong>
                    <span><?= e($request['category']) ?></span>
                    <span>Quantity: <?= e($request['quantity']) ?></span>
                    <span>Size: <?= e($request['clothing_size']) ?></span>
                    <span>Priority: <?= e($request['urgency']) ?></span>
                    <span>Needed by: <?= e($request['date']) ?></span>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You haven't published any requests yet.</p>
        <?php endif; ?>
    </section>

    <section id="donations" class="history-section">
        <h2>Donations received</h2>

        <?php if ($myDonations): ?>
            <?php foreach ($myDonations as $donation): ?>
                <article class="history-card">
                    <strong><?= e($donation['category']) ?></strong>
                    <span><?= e($donation['description']) ?></span>
                    <span>Quantity: <?= e($donation['quantity']) ?></span>
                    <span>Size: <?= e($donation['clothing_size']) ?></span>
                    <span>Date: <?= e($donation['date']) ?></span>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No donations have been received yet.</p>
        <?php endif; ?>
    </section>

</main>
</body>
</html>
