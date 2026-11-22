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
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['charity_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $userId = (int)($_POST['user_id'] ?? 0);

            if ($name === '' || $description === '' || $city === '' || $userId <= 0 || ($action === 'edit' && $id <= 0)) {
                throw new Exception('Please complete all fields.');
            }

            $checkUser = $conn->prepare("SELECT id FROM users WHERE id = :id AND role = 'charity'");
            $checkUser->execute([':id' => $userId]);

            if (!$checkUser->fetch()) {
                throw new Exception('The selected charity account does not exist.');
            }

            $checkProfile = $conn->prepare(
                "SELECT id FROM charity_profiles
                 WHERE user_id = :user_id AND (:id = 0 OR id != :id)"
            );
            $checkProfile->execute([
                ':user_id' => $userId,
                ':id' => $id
            ]);

            if ($checkProfile->fetch()) {
                throw new Exception('This charity account is already connected to another profile.');
            }

            if ($action === 'add') {
                $stmt = $conn->prepare(
                    "INSERT INTO charity_profiles (charity_name, description, city, user_id)
                     VALUES (:name, :description, :city, :user_id)"
                );

                $message = 'Charity profile added successfully.';
            } else {
                $stmt = $conn->prepare(
                    "UPDATE charity_profiles
                     SET charity_name = :name,
                         description = :description,
                         city = :city,
                         user_id = :user_id
                     WHERE id = :id"
                );

                $message = 'Charity profile updated successfully.';
            }

            $params = [
                ':name' => $name,
                ':description' => $description,
                ':city' => $city,
                ':user_id' => $userId
            ];

            if ($action === 'edit') {
                $params[':id'] = $id;
            }

            $stmt->execute($params);

        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('Invalid charity profile.');
            }

            $stmt = $conn->prepare("DELETE FROM charity_profiles WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $message = 'Charity profile deleted successfully.';
        }

    } catch (PDOException $e) {
        $error = 'This charity profile cannot be deleted or changed because it may be connected to existing clothing requests.';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$users = $conn->query(
    "SELECT id, username, email
     FROM users
     WHERE role = 'charity'
     ORDER BY username"
)->fetchAll(PDO::FETCH_ASSOC);

$charities = $conn->query(
    "SELECT cp.*, u.username, u.email
     FROM charity_profiles cp
     LEFT JOIN users u ON cp.user_id = u.id
     ORDER BY cp.id DESC"
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

    <title>Manage Charities | Kind Society</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="management-pages.css">
</head>

<body class="management-page">

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a href="admin.php" class="navbar-brand">Kind Society | Admin</a>

        <div class="ms-auto">
            <a href="admin.php" class="btn">Dashboard</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>
</nav>

<main class="container main-container">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2>Manage Charities</h2>
            <p>Manage charity profiles and their connected accounts.</p>
        </div>

        <a href="admin.php" class="btn btn-secondary">Back to Dashboard</a>
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

        <h4 class="mb-3">Add a Charity Profile</h4>

        <?php if ($users): ?>

            <form method="POST" action="manageCharities.php">

                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="add">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Charity Name</label>
                        <input class="form-control" name="charity_name" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input class="form-control" name="city" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Connect Charity Account</label>

                        <select class="form-select" name="user_id" required>
                            <option value="">Choose a charity account</option>

                            <?php foreach ($users as $user): ?>
                                <option value="<?= (int)$user['id'] ?>">
                                    <?= h($user['username']) ?> (<?= h($user['email']) ?>)
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                </div>

                <button class="btn btn-success mt-3" type="submit">
                    Add Charity
                </button>

            </form>

        <?php else: ?>

            <p class="mb-0">
                There are no charity-role accounts available.
                Create a charity account in Manage Users first.
            </p>

        <?php endif; ?>

    </section>

    <section class="content-card">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Charity Profiles</h4>
            <span class="badge bg-success"><?= count($charities) ?> total</span>
        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Charity</th>
                        <th>Description</th>
                        <th>City</th>
                        <th>Account</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($charities as $c): ?>

                        <tr>
                            <td><?= (int)$c['id'] ?></td>
                            <td><?= h($c['charity_name']) ?></td>
                            <td><?= h($c['description']) ?></td>
                            <td><?= h($c['city']) ?></td>
                            <td><?= h($c['username'] ?? 'Not connected') ?></td>

                            <td class="text-nowrap">

                                <button
                                    class="btn btn-primary btn-sm"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#edit-charity-<?= (int)$c['id'] ?>">
                                    Edit
                                </button>

                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this charity profile?');">

                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">

                                    <button class="btn btn-danger btn-sm" type="submit">
                                        Delete
                                    </button>

                                </form>

                            </td>
                        </tr>

                        <tr class="collapse" id="edit-charity-<?= (int)$c['id'] ?>">
                            <td colspan="6">

                                <form method="POST" class="p-3">

                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">

                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label class="form-label">Charity Name</label>
                                            <input
                                                class="form-control"
                                                name="charity_name"
                                                value="<?= h($c['charity_name']) ?>"
                                                required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">City</label>
                                            <input
                                                class="form-control"
                                                name="city"
                                                value="<?= h($c['city']) ?>"
                                                required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="3" required><?= h($c['description']) ?></textarea>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Connected Account</label>

                                            <select class="form-select" name="user_id" required>

                                                <?php foreach ($users as $user): ?>

                                                    <option
                                                        value="<?= (int)$user['id'] ?>"
                                                        <?= (int)$user['id'] === (int)$c['user_id'] ? 'selected' : '' ?>>

                                                        <?= h($user['username']) ?> (<?= h($user['email']) ?>)

                                                    </option>

                                                <?php endforeach; ?>

                                            </select>
                                        </div>

                                    </div>

                                    <button class="btn btn-success mt-3" type="submit">
                                        Save Changes
                                    </button>

                                </form>

                            </td>
                        </tr>

                    <?php endforeach; ?>

                    <?php if (!$charities): ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                No charity profiles found.
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