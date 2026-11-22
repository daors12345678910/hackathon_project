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

            $id = (int)($_POST['request_id'] ?? 0);
            $charityId = (int)($_POST['charity_id'] ?? 0);
            $category = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $urgency = $_POST['urgency'] ?? '';
            $date = $_POST['date'] ?? '';
            $quantity = (int)($_POST['quantity'] ?? 0);
            $size = trim($_POST['clothing_size'] ?? '');
            $image = trim($_POST['image'] ?? '');

            if (
                $charityId <= 0 ||
                $category === '' ||
                $description === '' ||
                $date === '' ||
                $size === '' ||
                $quantity <= 0 ||
                ($action === 'edit' && $id <= 0)
            ) {
                throw new Exception('Please complete all required fields.');
            }

            if (!in_array($urgency, ['Low', 'Medium', 'High'], true)) {
                throw new Exception('Please select a valid urgency.');
            }

            $check = $conn->prepare(
                "SELECT id FROM charity_profiles WHERE id = :id"
            );

            $check->execute([':id' => $charityId]);

            if (!$check->fetch()) {
                throw new Exception('The selected charity does not exist.');
            }

            if ($action === 'add') {

                $stmt = $conn->prepare(
                    "INSERT INTO clothing_needed
                    (charity_id, category, description, urgency, `date`, image, quantity, clothing_size)
                    VALUES
                    (:charity_id, :category, :description, :urgency, :date, :image, :quantity, :size)"
                );

                $message = 'Clothing request created successfully.';

            } else {

                $stmt = $conn->prepare(
                    "UPDATE clothing_needed
                     SET charity_id = :charity_id,
                         category = :category,
                         description = :description,
                         urgency = :urgency,
                         `date` = :date,
                         image = :image,
                         quantity = :quantity,
                         clothing_size = :size
                     WHERE request_id = :request_id"
                );

                $message = 'Clothing request updated successfully.';
            }

            $params = [
                ':charity_id' => $charityId,
                ':category' => $category,
                ':description' => $description,
                ':urgency' => $urgency,
                ':date' => $date,
                ':image' => $image,
                ':quantity' => $quantity,
                ':size' => $size
            ];

            if ($action === 'edit') {
                $params[':request_id'] = $id;
            }

            $stmt->execute($params);

        } elseif ($action === 'delete') {

            $id = (int)($_POST['request_id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('Invalid request.');
            }

            $stmt = $conn->prepare(
                "DELETE FROM clothing_needed WHERE request_id = :id"
            );

            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Request not found.');
            }

            $message = 'Clothing request deleted successfully.';
        }

    } catch (PDOException $e) {

        $error = 'This request cannot be deleted or changed because it may be connected to existing donations.';

    } catch (Exception $e) {

        $error = $e->getMessage();
    }
}

$charities = $conn->query(
    "SELECT id, charity_name
     FROM charity_profiles
     ORDER BY charity_name"
)->fetchAll(PDO::FETCH_ASSOC);

$requests = $conn->query(
    "SELECT cn.*, cp.charity_name
     FROM clothing_needed cn
     LEFT JOIN charity_profiles cp ON cn.charity_id = cp.id
     ORDER BY cn.request_id DESC"
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

    <title>Manage Requests | Kind Society</title>

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
            <h2>Manage Clothing Requests</h2>
            <p>Create, update and manage clothing requests from charities.</p>
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

        <h4 class="mb-3">Create a Clothing Request</h4>

        <?php if ($charities): ?>

            <form method="POST" action="manageRequests.php">

                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="add">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Charity</label>

                        <select class="form-select" name="charity_id" required>
                            <option value="">Select charity</option>

                            <?php foreach ($charities as $c): ?>
                                <option value="<?= (int)$c['id'] ?>">
                                    <?= h($c['charity_name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Clothing Category</label>

                        <select class="form-select" name="category" required>
                            <option value="">Select category</option>

                            <?php foreach (['Shirts', 'Pants', 'Jackets', 'Shoes', 'Dresses', 'Other'] as $category): ?>
                                <option value="<?= h($category) ?>">
                                    <?= h($category) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Urgency</label>

                        <select class="form-select" name="urgency" required>
                            <option>Low</option>
                            <option>Medium</option>
                            <option>High</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" min="1" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Clothing Size</label>
                        <input
                            class="form-control"
                            name="clothing_size"
                            placeholder="S, M, L, XL or mixed sizes"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Image Path (optional)</label>
                        <input
                            class="form-control"
                            name="image"
                            placeholder="images/clothes.jpg">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>

                </div>

                <button class="btn btn-success mt-3" type="submit">
                    Create Request
                </button>

            </form>

        <?php else: ?>

            <p class="mb-0">
                No charity profiles found. Add a charity profile before creating a request.
            </p>

        <?php endif; ?>

    </section>

    <section class="content-card">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">All Clothing Requests</h4>
            <span class="badge bg-success"><?= count($requests) ?> total</span>
        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Charity</th>
                        <th>Category</th>
                        <th>Urgency</th>
                        <th>Date</th>
                        <th>Quantity</th>
                        <th>Size</th>
                        <th>Description</th>
                        <th>Image Path</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($requests as $r): ?>

                        <tr>

                            <td><?= (int)$r['request_id'] ?></td>
                            <td><?= h($r['charity_name'] ?? 'Unknown') ?></td>
                            <td><?= h($r['category']) ?></td>

                            <td>
                                <span class="badge <?= $r['urgency'] === 'High' ? 'bg-danger' : ($r['urgency'] === 'Medium' ? 'bg-warning text-dark' : 'bg-success') ?>">
                                    <?= h($r['urgency']) ?>
                                </span>
                            </td>

                            <td><?= h($r['date']) ?></td>
                            <td><?= (int)$r['quantity'] ?></td>
                            <td><?= h($r['clothing_size']) ?></td>
                            <td><?= h($r['description']) ?></td>
                            <td><?= h($r['image'] ?? '') ?></td>

                            <td class="text-nowrap">

                                <button
                                    class="btn btn-primary btn-sm"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#edit-request-<?= (int)$r['request_id'] ?>">
                                    Edit
                                </button>

                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this request?');">

                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>">

                                    <button class="btn btn-danger btn-sm" type="submit">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                        <tr class="collapse" id="edit-request-<?= (int)$r['request_id'] ?>">

                            <td colspan="10">

                                <form method="POST" class="p-3">

                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>">

                                    <div class="row g-3">

                                        <div class="col-md-4">
                                            <label class="form-label">Charity</label>

                                            <select class="form-select" name="charity_id" required>

                                                <?php foreach ($charities as $c): ?>

                                                    <option
                                                        value="<?= (int)$c['id'] ?>"
                                                        <?= (int)$c['id'] === (int)$r['charity_id'] ? 'selected' : '' ?>>

                                                        <?= h($c['charity_name']) ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Category</label>

                                            <input
                                                class="form-control"
                                                name="category"
                                                value="<?= h($r['category']) ?>"
                                                required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Urgency</label>

                                            <select class="form-select" name="urgency" required>

                                                <?php foreach (['Low', 'Medium', 'High'] as $level): ?>

                                                    <option
                                                        value="<?= $level ?>"
                                                        <?= $r['urgency'] === $level ? 'selected' : '' ?>>

                                                        <?= $level ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Date</label>

                                            <input
                                                type="date"
                                                class="form-control"
                                                name="date"
                                                value="<?= h($r['date']) ?>"
                                                required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Quantity</label>

                                            <input
                                                type="number"
                                                class="form-control"
                                                name="quantity"
                                                min="1"
                                                value="<?= (int)$r['quantity'] ?>"
                                                required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Clothing Size</label>

                                            <input
                                                class="form-control"
                                                name="clothing_size"
                                                value="<?= h($r['clothing_size']) ?>"
                                                required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Image Path</label>

                                            <input
                                                class="form-control"
                                                name="image"
                                                value="<?= h($r['image'] ?? '') ?>">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Description</label>

                                            <textarea class="form-control" name="description" rows="3" required><?= h($r['description']) ?></textarea>
                                        </div>

                                    </div>

                                    <button class="btn btn-success mt-3" type="submit">
                                        Save Changes
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if (!$requests): ?>
                        <tr>
                            <td colspan="10" class="text-center">
                                No clothing requests found.
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