<?php
session_start();
include_once('config.php');

/* -------------------------------------------------
   DONOR ACCESS
------------------------------------------------- */

if (
    !isset($_SESSION['id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'donor'
) {
    header("Location: index.php");
    exit();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function allowedSizesForCategory($category) {

    $category = strtolower(trim($category));

    // Support both old and new category names.
    $categoryAliases = [
        'clothing' => 'clothes',
        'clothes' => 'clothes',

        'coat' => 'coats',
        'coats' => 'coats',

        'shoes' => 'footwear',
        'shoe' => 'footwear',
        'footwear' => 'footwear',

        'hat' => 'hats',
        'hats' => 'hats',

        'accessories' => 'accessory',
        'accessory' => 'accessory'
    ];

    $category = $categoryAliases[$category] ?? $category;

    $sizesByCategory = [

        'clothes' => [
            'XS', 'S', 'M', 'L', 'XL', 'XXL'
        ],

        'coats' => [
            'XS', 'S', 'M', 'L', 'XL', 'XXL'
        ],

        'footwear' => [
            'EU 36', 'EU 37', 'EU 38', 'EU 39',
            'EU 40', 'EU 41', 'EU 42', 'EU 43',
            'EU 44', 'EU 45', 'EU 46'
        ],

        'hats' => [
            'One Size', 'S', 'M', 'L', 'XL'
        ],

        'accessory' => [
            'One Size'
        ]

    ];

    return $sizesByCategory[$category] ?? [];
}

/* -------------------------------------------------
   PROCESS DONATION
------------------------------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donate'])) {

    $request_id = (int)($_POST['request_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $size = trim($_POST['clothing_size'] ?? '');

    if ($request_id > 0 && $quantity > 0 && $quantity <= 100) {

        $check = $conn->prepare(
            "SELECT category FROM clothing_needed WHERE request_id = :id"
        );

        $check->execute([':id' => $request_id]);
        $request = $check->fetch(PDO::FETCH_ASSOC);

        if ($request) {
            $allowedSizes = allowedSizesForCategory($request['category']);

            if (in_array($size, $allowedSizes, true)) {

                $sql = "INSERT INTO donation
                        (request_id, donor_id, description, date, image, quantity, clothing_size)
                        VALUES
                        (:request_id, :donor_id, :description, :date, :image, :quantity, :size)";

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
    }

    header("Location: donor.php?error=1");
    exit();
}

/* -------------------------------------------------
   SEARCH AND FILTERS
------------------------------------------------- */

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$urgency = trim($_GET['urgency'] ?? '');

$sql = "SELECT cn.*, cp.charity_name, cp.city
        FROM clothing_needed cn
        INNER JOIN charity_profiles cp ON cn.charity_id = cp.id
        WHERE 1=1";

$params = [];

if ($search !== '') {
    $sql .= " AND (
        cn.category LIKE :search
        OR cn.description LIKE :search
        OR cp.charity_name LIKE :search
        OR cp.city LIKE :search
    )";

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

/* -------------------------------------------------
   DONATION COUNT AND HISTORY
------------------------------------------------- */

$donationStmt = $conn->prepare(
    "SELECT COUNT(*) FROM donation WHERE donor_id = :id"
);

$donationStmt->execute([':id' => $_SESSION['id']]);
$donationCount = $donationStmt->fetchColumn();

$history = $conn->prepare(
    "SELECT * FROM donation
     WHERE donor_id = :id
     ORDER BY donation_id DESC"
);

$history->execute([':id' => $_SESSION['id']]);
$donations = $history->fetchAll(PDO::FETCH_ASSOC);

/* -------------------------------------------------
   MAP CITY COORDINATES
------------------------------------------------- */

$cityCoordinates = [
    'decan' => [42.5403, 20.2883],
    'dragash' => [42.0265, 20.6547],
    'drenas' => [42.6283, 20.8939],
    'ferizaj' => [42.3702, 21.1553],
    'fushekosove' => [42.6394, 21.0961],
    'gjakove' => [42.3803, 20.4308],
    'gjilan' => [42.4635, 21.4694],
    'gracanice' => [42.6022, 21.1958],
    'hanielezit' => [42.1500, 21.2960],
    'istog' => [42.7808, 20.4875],
    'junik' => [42.4758, 20.2772],
    'kacanik' => [42.2319, 21.2594],
    'kamenice' => [42.5781, 21.5803],
    'kline' => [42.6217, 20.5778],
    'kllokot' => [42.3714, 21.3747],
    'leposaviq' => [43.1039, 20.8028],
    'lipjan' => [42.5217, 21.1258],
    'malisheve' => [42.4822, 20.7458],
    'mamushe' => [42.3306, 20.7258],
    'mitroviceejugut' => [42.8914, 20.8659],
    'mitroviceeveriut' => [42.8939, 20.8667],
    'novoberde' => [42.6117, 21.4194],
    'obiliq' => [42.6869, 21.0703],
    'partesh' => [42.4017, 21.4339],
    'peje' => [42.6591, 20.2883],
    'podujeve' => [42.9106, 21.1931],
    'prishtine' => [42.6629, 21.1655],
    'prizren' => [42.2139, 20.7397],
    'rahovec' => [42.3994, 20.6547],
    'ranillug' => [42.4922, 21.6067],
    'shtime' => [42.4331, 21.0397],
    'shterpce' => [42.2394, 21.0272],
    'skenderaj' => [42.7467, 20.7883],
    'suhareke' => [42.3586, 20.8250],
    'viti' => [42.3214, 21.3583],
    'vushtrri' => [42.8231, 20.9675],
    'zubinpotok' => [42.9144, 20.6897],
    'zvecan' => [42.9075, 20.8403]
];

/* -------------------------------------------------
   NORMALIZE CITY NAMES
------------------------------------------------- */

function normalizeCity($city) {
    $city = trim((string)$city);
    $city = mb_strtolower($city, 'UTF-8');

    // Convert Albanian letters such as ë and ç to ASCII equivalents.
    $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $city);

    if ($converted !== false) {
        $city = $converted;
    }

    // Remove spaces and all characters except letters.
    return preg_replace('/[^a-z]/', '', $city);
}

/* -------------------------------------------------
   LOAD ALL CHARITIES FOR THE MAP
------------------------------------------------- */

$charityMapStmt = $conn->prepare("
    SELECT id, charity_name, description, city
    FROM charity_profiles
    WHERE city IS NOT NULL
      AND TRIM(city) <> ''
    ORDER BY charity_name ASC
");

$charityMapStmt->execute();
$allCharities = $charityMapStmt->fetchAll(PDO::FETCH_ASSOC);

$mapLocations = [];

foreach ($allCharities as $charity) {

    $cityKey = normalizeCity($charity['city']);

    if (isset($cityCoordinates[$cityKey])) {

        $mapLocations[] = [
            'id' => (int)$charity['id'],
            'name' => $charity['charity_name'],
            'city' => $charity['city'],
            'description' => $charity['description'],
            'coordinates' => $cityCoordinates[$cityKey]
        ];

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Kind Society | Donor Marketplace</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: var(--background-color, #f5f7f5);
    color: #24352c;
}

a {
    text-decoration: none;
}

/* NAVIGATION */

.topbar {
    height: 76px;
    padding: 0 7%;
    background: #123f32;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.brand {
    color: white;
    font-size: 25px;
    font-weight: 800;
    letter-spacing: -1px;
}

.topbar nav {
    display: flex;
    align-items: center;
    gap: 30px;
}

.topbar nav a {
    color: #d4e5db;
    font-size: 14px;
    font-weight: 600;
    transition: 0.2s;
}

.topbar nav a:hover,
.topbar nav a.active {
    color: white;
}

/* MAIN LAYOUT */

.container {
    width: 86%;
    max-width: 1350px;
    margin: 0 auto;
}

/* WELCOME */

.welcome {
    padding: 55px 0 35px;
}

.eyebrow {
    color: #38856a;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 13px;
}

.welcome h1 {
    font-size: clamp(32px, 5vw, 48px);
    color: #153f32;
    letter-spacing: -1.8px;
    margin-bottom: 12px;
}

.welcome p:last-child {
    color: #718077;
    font-size: 16px;
    line-height: 1.6;
}

/* ALERTS */

.message {
    padding: 14px 18px;
    border-radius: 7px;
    margin-bottom: 22px;
    font-size: 14px;
}

.success {
    background: #e3f4e9;
    color: #176b42;
    border-left: 4px solid #27985b;
}

.error {
    background: #fdeaea;
    color: #a52c2c;
    border-left: 4px solid #d94a4a;
}

/* SEARCH AND FILTERS */

.filter-section {
    background: white;
    border: 1px solid #e1e9e3;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 32px;
}

.filter-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto auto;
    gap: 10px;
}

.filter-row input,
.filter-row select {
    width: 100%;
    min-width: 0;
    padding: 13px 14px;
    border: 1px solid #dce5de;
    border-radius: 6px;
    background: #fbfcfb;
    color: #263a30;
    font-size: 14px;
    outline: none;
}

.filter-row input:focus,
.filter-row select:focus {
    border-color: #38856a;
    box-shadow: 0 0 0 3px rgba(56, 133, 106, 0.1);
}

.primary-btn {
    background: #176b52;
    color: white;
    border: none;
    padding: 12px 19px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: 0.2s;
}

.primary-btn:hover {
    background: #10533e;
}

.reset-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 13px;
    color: #617269;
    font-size: 13px;
    font-weight: 700;
}

/* MAP */

.map-section {
    margin-bottom: 48px;
}

.map-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    gap: 15px;
}

.map-heading h2 {
    font-size: 22px;
    color: #173e31;
    margin-bottom: 5px;
}

.map-heading p {
    color: #77847b;
    font-size: 13px;
}

.map-count {
    background: #e3f1e8;
    color: #176b52;
    font-size: 12px;
    font-weight: 700;
    padding: 8px 12px;
    border-radius: 30px;
    white-space: nowrap;
}

#charityMap {
    width: 100%;
    height: 400px;
    border: 1px solid #dce5de;
    border-radius: 9px;
    z-index: 1;
}

.map-note {
    font-size: 12px;
    color: #859087;
    margin-top: 10px;
}

/* MARKETPLACE HEADING */

.section-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.section-heading h2 {
    font-size: 24px;
    color: #153f32;
    letter-spacing: -0.5px;
}

.section-heading span {
    color: #77847b;
    font-size: 13px;
}

/* MARKETPLACE LIST */

.marketplace-list {
    background: white;
    border: 1px solid #e0e8e2;
    border-radius: 9px;
    overflow: hidden;
}

.market-header {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(100px, 0.8fr) minmax(100px, 0.8fr) minmax(110px, 0.8fr) minmax(240px, 1.5fr);
    gap: 15px;
    padding: 14px 20px;
    background: #edf3ef;
    color: #627268;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.market-row {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(100px, 0.8fr) minmax(100px, 0.8fr) minmax(110px, 0.8fr) minmax(240px, 1.5fr);
    gap: 15px;
    align-items: center;
    padding: 20px;
    border-top: 1px solid #edf0ed;
    transition: background 0.2s;
}

.market-row:hover {
    background: #f8fbf8;
}

.request-title {
    font-size: 15px;
    font-weight: 800;
    color: #234b39;
    margin-bottom: 7px;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.charity-name {
    font-size: 12px;
    color: #748077;
    line-height: 1.5;
}

.market-info {
    font-size: 13px;
    color: #52645a;
}

.market-info strong {
    display: block;
    color: #263e31;
    margin-bottom: 5px;
    font-size: 13px;
}

.urgency-label {
    display: inline-block;
    padding: 6px 9px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.urgency-urgent {
    background: #fde8e5;
    color: #b23b2f;
}

.urgency-standard {
    background: #e8f1eb;
    color: #31704d;
}

.urgency-winter {
    background: #e6eff9;
    color: #38628c;
}

.donation-form {
    display: flex;
    align-items: center;
    gap: 7px;
    min-width: 0;
}

.donation-form input,
.donation-form select {
    width: 65px;
    min-width: 0;
    padding: 10px 7px;
    border: 1px solid #dce5de;
    border-radius: 5px;
    background: white;
    color: #263a30;
    font-size: 12px;
}

.donation-form select {
    width: 90px;
}

.donation-form button {
    flex: 1;
    white-space: nowrap;
    padding: 11px 9px;
    font-size: 11px;
}

/* EMPTY STATE */

.empty {
    padding: 40px 20px;
    text-align: center;
    color: #78847c;
    background: white;
    border: 1px solid #e0e8e2;
    border-radius: 9px;
}

/* DONATION HISTORY */

.history-section {
    margin-top: 60px;
    margin-bottom: 65px;
}

.history-section h2 {
    font-size: 23px;
    color: #153f32;
    margin-bottom: 18px;
}

.history-list {
    background: white;
    border: 1px solid #e0e8e2;
    border-radius: 9px;
    overflow: hidden;
}

.history-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    padding: 17px 20px;
    border-bottom: 1px solid #edf0ed;
    font-size: 13px;
}

.history-card:last-child {
    border-bottom: none;
}

.history-card strong {
    color: #28533e;
}

.history-card span {
    color: #748077;
}

.history-empty {
    background: white;
    border: 1px solid #e0e8e2;
    padding: 22px;
    border-radius: 8px;
    color: #78847c;
}

/* RESPONSIVE DESIGN */

@media (max-width: 1050px) {

    .market-header {
        display: none;
    }

    .market-row {
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .request-main {
        grid-column: 1 / -1;
    }

    .donation-form {
        grid-column: 1 / -1;
    }

}

@media (max-width: 700px) {

    .topbar {
        height: auto;
        padding: 18px 5%;
        flex-direction: column;
        gap: 17px;
    }

    .topbar nav {
        gap: 18px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .container {
        width: 92%;
    }

    .welcome {
        padding-top: 38px;
    }

    .filter-row {
        grid-template-columns: 1fr;
    }

    .filter-row button {
        width: 100%;
    }

    .reset-btn {
        padding: 10px;
    }

    .market-row {
        grid-template-columns: 1fr 1fr;
        padding: 16px;
    }

    .donation-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .donation-form input,
    .donation-form select {
        width: 100%;
    }

    .donation-form button {
        grid-column: 1 / -1;
    }

    .section-heading h2 {
        font-size: 20px;
    }

    #charityMap {
        height: 320px;
    }

    .history-card {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
    }

}

</style>

</head>

<body>

<header class="topbar">

    <a class="brand" href="index.php">Kind Society</a>

    <nav>
        <a class="active" href="donor.php">Discover</a>
        <a href="#myDonations">My donations (<?= e($donationCount) ?>)</a>
        <a href="logout.php">Logout</a>
    </nav>

</header>

<main class="container">

    <section class="welcome">

        <p class="eyebrow">KIND SOCIETY / DONOR PORTAL</p>

        <h1>Give something good.</h1>

        <p>
            Discover clothing requests from charities and help people
            in your community.
        </p>

    </section>

    <?php if (isset($_GET['success'])): ?>

        <div class="message success">
            Your donation has been recorded successfully!
        </div>

    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>

        <div class="message error">
            Something went wrong. Please check your donation details.
        </div>

    <?php endif; ?>

    <!-- SEARCH -->

    <section class="filter-section">

        <form method="GET" class="filter-row">

            <input
                type="search"
                name="search"
                placeholder="Search charities, clothing or cities..."
                value="<?= e($search) ?>"
            >

            <select name="category">

                <option value="">All categories</option>

                <?php foreach (['footwear', 'hats', 'accessory', 'clothes', 'coats'] as $item): ?>

                    <option
                        value="<?= e($item) ?>"
                        <?= $category === $item ? 'selected' : '' ?>
                    >
                        <?= e(ucfirst($item)) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <select name="urgency">

                <option value="">All priorities</option>

                <option value="urgent" <?= $urgency === 'urgent' ? 'selected' : '' ?>>
                    Urgent
                </option>

                <option value="standard" <?= $urgency === 'standard' ? 'selected' : '' ?>>
                    Standard
                </option>

                <option value="winter" <?= $urgency === 'winter' ? 'selected' : '' ?>>
                    Winter
                </option>

            </select>

            <button type="submit" class="primary-btn">Search</button>

            <a href="donor.php" class="reset-btn">Clear filters</a>

        </form>

    </section>

    <!-- CHARITY MAP -->

    <section class="map-section">

        <div class="map-heading">

            <div>
                <h2>Find charities near you</h2>
                <p>Explore all registered charity locations across Kosovo.</p>
            </div>

            <span class="map-count">
                <?= count($mapLocations) ?> locations
            </span>

        </div>

        <div id="charityMap"></div>

        <p class="map-note">
            Locations represent approximate city centres, not exact charity addresses.
        </p>

    </section>

    <!-- MARKETPLACE REQUESTS -->

    <section class="marketplace-section">

        <div class="section-heading">

            <h2>Explore clothing requests</h2>

            <span><?= count($requests) ?> requests available</span>

        </div>

        <?php if (count($requests) > 0): ?>

            <div class="marketplace-list">

                <div class="market-header">
                    <span>Clothing request</span>
                    <span>Category</span>
                    <span>Location</span>
                    <span>Priority</span>
                    <span>Make a donation</span>
                </div>

                <?php foreach ($requests as $request): ?>

                    <div class="market-row">

                        <div class="request-main">

                            <h3 class="request-title">
                                <?= e($request['description']) ?>
                            </h3>

                            <p class="charity-name">
                                <?= e($request['charity_name']) ?>
                            </p>

                            <p class="charity-name">
                                Needed by: <?= e($request['date']) ?>
                            </p>

                            <p class="charity-name">
                                Requested quantity: <?= e($request['quantity']) ?>
                                &middot;
                                Size: <?= e($request['clothing_size']) ?>
                            </p>

                        </div>

                        <div class="market-info">
                            <strong>Category</strong>
                            <?= e(ucfirst($request['category'])) ?>
                        </div>

                        <div class="market-info">
                            <strong>Location</strong>
                            <?= e($request['city']) ?>
                        </div>

                        <div class="market-info">

                            <?php
                            $urgencyClass = 'urgency-standard';

                            if (strtolower($request['urgency']) === 'urgent') {
                                $urgencyClass = 'urgency-urgent';
                            } elseif (strtolower($request['urgency']) === 'winter') {
                                $urgencyClass = 'urgency-winter';
                            }
                            ?>

                            <span class="urgency-label <?= e($urgencyClass) ?>">
                                <?= e(ucfirst($request['urgency'])) ?>
                            </span>

                        </div>

                        <?php
                        $donationSizes = allowedSizesForCategory($request['category']);
                        $requestedSize = trim($request['clothing_size'] ?? '');
                        $selectedSize = in_array($requestedSize, $donationSizes, true)
                            ? $requestedSize
                            : ($donationSizes[0] ?? '');
                        ?>

                        <form method="POST" class="donation-form">

                            <input
                                type="hidden"
                                name="request_id"
                                value="<?= e($request['request_id']) ?>"
                            >

                            <input
                                type="number"
                                name="quantity"
                                min="1"
                                max="100"
                                value="1"
                                required
                                title="Quantity to donate"
                            >

                            <select name="clothing_size" required title="Donation size">
                                <?php foreach ($donationSizes as $sizeOption): ?>
                                    <option
                                        value="<?= e($sizeOption) ?>"
                                        <?= $selectedSize === $sizeOption ? 'selected' : '' ?>
                                    >
                                        <?= e($sizeOption) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="submit" name="donate" class="primary-btn">
                                Donate
                            </button>

                        </form>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty">
                No clothing requests match your filters.
            </div>

        <?php endif; ?>

    </section>

    <!-- DONATION HISTORY -->

    <section id="myDonations" class="history-section">

        <h2>My donation history</h2>

        <?php if ($donations): ?>

            <div class="history-list">

                <?php foreach ($donations as $donation): ?>

                    <div class="history-card">

                        <strong>
                            <?= e($donation['description']) ?>
                        </strong>

                        <span>
                            Quantity: <?= e($donation['quantity']) ?>
                        </span>

                        <span>
                            Size: <?= e($donation['clothing_size']) ?>
                        </span>

                        <span>
                            <?= e($donation['date']) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="history-empty">
                You haven't made any donations yet.
                Your future donations will appear here.
            </div>

        <?php endif; ?>

    </section>

</main>

<!-- LEAFLET MAP -->

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

const charityLocations = <?= json_encode(
    $mapLocations,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

const map = L.map('charityMap').setView([42.6026, 20.9030], 8);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

function escapeHTML(value) {

    return String(value ?? '').replace(/[&<>"']/g, function(character) {

        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[character];

    });

}

const markers = [];

charityLocations.forEach(function(location) {

    const marker = L.marker(location.coordinates).addTo(map);

    marker.bindPopup(`
        <div>
            <h3>${escapeHTML(location.name)}</h3>
            <p><strong>City:</strong> ${escapeHTML(location.city)}</p>
            <p>${escapeHTML(location.description)}</p>
        </div>
    `);

    markers.push(marker);

});

if (markers.length > 0) {

    const group = L.featureGroup(markers);

    map.fitBounds(group.getBounds().pad(0.2));

}

window.addEventListener('load', function() {

    setTimeout(function() {
        map.invalidateSize();
    }, 300);

});

</script>

</body>
</html>