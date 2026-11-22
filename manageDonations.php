<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid request. Please refresh the page.');
    }

    $action = $_POST['action'] ?? '';

    try {

        if ($action === 'add' || $action === 'edit') {

            $donationId = (int)($_POST['donation_id'] ?? 0);
            $requestId = (int)($_POST['request_id'] ?? 0);
            $donorId = (int)($_POST['donor_id'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $date = $_POST['date'] ?? '';
            $image = trim($_POST['image'] ?? '');
            $quantity = (int)($_POST['quantity'] ?? 0);
            $size = trim($_POST['clothing_size'] ?? '');

            if (
                $requestId <= 0 ||
                $donorId <= 0 ||
                $description === '' ||
                $date === '' ||
                $quantity <= 0 ||
                $size === '' ||
                ($action === 'edit' && $donationId <= 0)
            ) {
                throw new Exception('Please complete all required fields correctly.');
            }

            $checkDonor = $conn->prepare(
                "SELECT id FROM users WHERE id = :id AND role = 'donor'"
            );
            $checkDonor->execute([':id' => $donorId]);

            $checkRequest = $conn->prepare(
                "SELECT request_id FROM clothing_needed WHERE request_id = :id"
            );
            $checkRequest->execute([':id' => $requestId]);

            if (!$checkDonor->fetch() || !$checkRequest->fetch()) {
                throw new Exception('The selected donor or clothing request does not exist.');
            }

            if ($action === 'add') {

                $stmt = $conn->prepare(
                    "INSERT INTO donation
                    (request_id, donor_id, description, date, image, quantity, clothing_size)
                    VALUES
                    (:request_id, :donor_id, :description, :date, :image, :quantity, :size)"
                );

                $message = 'Donation added successfully.';

            } else {

                $stmt = $conn->prepare(
                    "UPDATE donation
                    SET request_id = :request_id,
                        donor_id = :donor_id,
                        description = :description,
                        date = :date,
                        image = :image,
                        quantity = :quantity,
                        clothing_size = :size
                    WHERE donation_id = :donation_id"
                );

                $message = 'Donation updated successfully.';
            }

            $params = [
                ':request_id' => $requestId,
                ':donor_id' => $donorId,
                ':description' => $description,
                ':date' => $date,
                ':image' => $image,
                ':quantity' => $quantity,
                ':size' => $size
            ];

            if ($action === 'edit') {
                $params[':donation_id'] = $donationId;
            }

            $stmt->execute($params);

        } elseif ($action === 'delete') {

            $id = (int)($_POST['donation_id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('Invalid donation.');
            }

            $stmt = $conn->prepare(
                "DELETE FROM donation WHERE donation_id = :id"
            );

            $stmt->execute([':id' => $id]);

            $message = 'Donation deleted successfully.';
        }

    } catch (PDOException $e) {

        $error = 'The database could not complete this operation. Check related records and try again.';

    } catch (Exception $e) {

        $error = $e->getMessage();
    }
}

$donors = $conn->query(
    "SELECT id, username
     FROM users
     WHERE role = 'donor'
     ORDER BY username"
)->fetchAll(PDO::FETCH_ASSOC);

$requests = $conn->query(
    "SELECT cn.request_id, cn.category, cp.charity_name
     FROM clothing_needed cn
     LEFT JOIN charity_profiles cp ON cn.charity_id = cp.id
     ORDER BY cn.request_id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$donations = $conn->query(
    "SELECT d.*,
            u.username AS donor_username,
            cn.category,
            cp.charity_name
     FROM donation d
     LEFT JOIN users u ON d.donor_id = u.id
     LEFT JOIN clothing_needed cn ON d.request_id = cn.request_id
     LEFT JOIN charity_profiles cp ON cn.charity_id = cp.id
     ORDER BY d.donation_id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Manage Donations | Kind Society</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="management-pages.css">
</head>

<body class="management-page">

<nav class="navbar navbar-expand-lg">
    <div class="container">

        <a href="admin.php" class="navbar-brand">
            Kind Society | Admin
        </a>

        <div class="ms-auto">
            <a href="admin.php" class="btn">Dashboard</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>

    </div>
</nav>

<main class="container main-container">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h2>Manage Donations</h2>
            <p>Review and manage donations submitted by donors.</p>
        </div>

        <a href="admin.php" class="btn btn-secondary">
            Back to Dashboard
        </a>

    </div>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <?= h($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <section class="content-card mb-4">

        <h4 class="mb-3">Add a Donation</h4>

        <form method="POST" action="manageDonations.php">

            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="add">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Donor</label>

                    <select class="form-select" name="donor_id" required>
                        <option value="">Select donor</option>

                        <?php foreach ($donors as $d): ?>
                            <option value="<?= (int)$d['id'] ?>">
                                <?= h($d['username']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Clothing Request</label>

                    <select class="form-select" name="request_id" required>
                        <option value="">Select request</option>

                        <?php foreach ($requests as $r): ?>
                            <option value="<?= (int)$r['request_id'] ?>">
                                #<?= (int)$r['request_id'] ?>
                                — <?= h($r['category']) ?>
                                (<?= h($r['charity_name'] ?? 'Unknown charity') ?>)
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3" required></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="date" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" name="quantity" min="1" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Clothing Size</label>
                    <input class="form-control" name="clothing_size" placeholder="S, M, L, XL..." required>
                </div>

                <div class="col-12">
                    <label class="form-label">Image Path (optional)</label>
                    <input class="form-control" name="image" placeholder="images/clothes.jpg">
                </div>

            </div>

            <button class="btn btn-success mt-3" type="submit">
                Add Donation
            </button>

        </form>

    </section>

    <section class="content-card">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">All Donations</h4>
            <span class="badge bg-success"><?= count($donations) ?> total</span>
        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Donor</th>
                        <th>Request</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Image Path</th>
                        <th>Quantity</th>
                        <th>Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($donations as $d): ?>

                        <tr>

                            <td><?= (int)$d['donation_id'] ?></td>
                            <td><?= h($d['donor_username'] ?? 'Unknown') ?></td>

                            <td>
                                #<?= (int)$d['request_id'] ?>
                                — <?= h($d['category'] ?? 'Unknown') ?>
                            </td>

                            <td><?= h($d['description']) ?></td>
                            <td><?= h($d['date']) ?></td>
                            <td><?= h($d['image'] ?? '') ?></td>
                            <td><?= (int)$d['quantity'] ?></td>
                            <td><?= h($d['clothing_size']) ?></td>

                            <td class="text-nowrap">

                                <button
                                    class="btn btn-primary btn-sm"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#edit-donation-<?= (int)$d['donation_id'] ?>">
                                    Edit
                                </button>

                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this donation?');">

                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="donation_id" value="<?= (int)$d['donation_id'] ?>">

                                    <button class="btn btn-danger btn-sm" type="submit">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                        <tr class="collapse" id="edit-donation-<?= (int)$d['donation_id'] ?>">

                            <td colspan="9">

                                <form method="POST" class="p-3">

                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="donation_id" value="<?= (int)$d['donation_id'] ?>">

                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label class="form-label">Donor</label>

                                            <select class="form-select" name="donor_id" required>

                                                <?php foreach ($donors as $donor): ?>

                                                    <option
                                                        value="<?= (int)$donor['id'] ?>"
                                                        <?= (int)$donor['id'] === (int)$d['donor_id'] ? 'selected' : '' ?>>

                                                        <?= h($donor['username']) ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Clothing Request</label>

                                            <select class="form-select" name="request_id" required>

                                                <?php foreach ($requests as $r): ?>

                                                    <option
                                                        value="<?= (int)$r['request_id'] ?>"
                                                        <?= (int)$r['request_id'] === (int)$d['request_id'] ? 'selected' : '' ?>>

                                                        #<?= (int)$r['request_id'] ?>
                                                        — <?= h($r['category']) ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Description</label>

                                            <textarea class="form-control" name="description" rows="2" required><?= h($d['description']) ?></textarea>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Date</label>

                                            <input
                                                type="date"
                                                class="form-control"
                                                name="date"
                                                value="<?= h($d['date']) ?>"
                                                required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Quantity</label>

                                            <input
                                                type="number"
                                                class="form-control"
                                                name="quantity"
                                                min="1"
                                                value="<?= (int)$d['quantity'] ?>"
                                                required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Size</label>

                                            <input
                                                class="form-control"
                                                name="clothing_size"
                                                value="<?= h($d['clothing_size']) ?>"
                                                required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Image Path</label>

                                            <input
                                                class="form-control"
                                                name="image"
                                                value="<?= h($d['image'] ?? '') ?>">
                                        </div>

                                    </div>

                                    <button class="btn btn-success mt-3" type="submit">
                                        Save Changes
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if (!$donations): ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                No donations found.
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>