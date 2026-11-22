
<?php
session_start();
require_once 'config.php';

// Only admins can access this page
if (
    !isset($_SESSION['id'], $_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: login.php');
    exit();
}

// CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die('Invalid request. Please refresh the page.');
    }

    $action = $_POST['action'] ?? '';

    try {

        // ADD OR EDIT CHARITY
        if ($action === 'add' || $action === 'edit') {

            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['charity_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $city = trim($_POST['city'] ?? '');

            if (
                $name === '' ||
                $description === '' ||
                $city === '' ||
                ($action === 'edit' && $id <= 0)
            ) {
                throw new Exception('Please complete all fields.');
            }

            if ($action === 'add') {

                // Admin-created profiles do not need an account
                $stmt = $conn->prepare(
                    "INSERT INTO charity_profiles
                     (charity_name, description, city, user_id)
                     VALUES (:name, :description, :city, NULL)"
                );

                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':city' => $city
                ]);

                $message = 'Charity profile added successfully.';

            } else {

                // Update profile details but preserve its account connection
                $stmt = $conn->prepare(
                    "UPDATE charity_profiles
                     SET charity_name = :name,
                         description = :description,
                         city = :city
                     WHERE id = :id"
                );

                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':city' => $city,
                    ':id' => $id
                ]);

                $message = 'Charity profile updated successfully.';
            }

        // DELETE CHARITY
        } elseif ($action === 'delete') {

            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('Invalid charity profile.');
            }

            $stmt = $conn->prepare(
                "DELETE FROM charity_profiles WHERE id = :id"
            );

            $stmt->execute([':id' => $id]);

            $message = 'Charity profile deleted successfully.';

        } else {
            throw new Exception('Invalid action.');
        }

    } catch (PDOException $e) {

        $error = 'This charity profile could not be changed or deleted. It may be connected to existing clothing requests.';

    } catch (Exception $e) {

        $error = $e->getMessage();
    }
}

// Get all charity profiles and their connected accounts
$charities = $conn->query(
    "SELECT cp.*, u.username, u.email
     FROM charity_profiles cp
     LEFT JOIN users u ON cp.user_id = u.id
     ORDER BY cp.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

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
            <h2>Manage Charities</h2>
            <p>Manage charity profiles and view their connected accounts.</p>
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


    <!-- ADD CHARITY PROFILE -->
    <section class="content-card mb-4">

        <h4 class="mb-3">Add a Charity Profile</h4>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= h($_SESSION['csrf_token']) ?>"
            >

            <input type="hidden" name="action" value="add">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Charity Name</label>

                    <input
                        type="text"
                        class="form-control"
                        name="charity_name"
                        placeholder="Enter charity name"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">City</label>

                    <input
                        type="text"
                        class="form-control"
                        name="city"
                        placeholder="Enter city"
                        required
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>

                    <textarea
                        class="form-control"
                        name="description"
                        rows="3"
                        placeholder="Describe the charity and its needs"
                        required
                    ></textarea>
                </div>

            </div>

            <button type="submit" class="btn btn-success mt-3">
                Add Charity
            </button>

        </form>

    </section>


    <!-- CHARITY PROFILES TABLE -->
    <section class="content-card">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h4 class="mb-0">Charity Profiles</h4>

            <span class="badge bg-success">
                <?= count($charities) ?> total
            </span>

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

                            <td>
                                <?php if (!empty($c['username'])): ?>

                                    <?= h($c['username']) ?>

                                <?php else: ?>

                                    <span class="text-muted">Not connected</span>

                                <?php endif; ?>
                            </td>

                            <td class="text-nowrap">

                                <button
                                    class="btn btn-primary btn-sm"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#edit-charity-<?= (int)$c['id'] ?>"
                                >
                                    Edit
                                </button>

                                <form
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Delete this charity profile?');"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= h($_SESSION['csrf_token']) ?>"
                                    >

                                    <input type="hidden" name="action" value="delete">

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$c['id'] ?>"
                                    >

                                    <button class="btn btn-danger btn-sm" type="submit">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>


                        <!-- EDIT CHARITY FORM -->
                        <tr class="collapse" id="edit-charity-<?= (int)$c['id'] ?>">

                            <td colspan="6">

                                <form method="POST" class="p-3">

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= h($_SESSION['csrf_token']) ?>"
                                    >

                                    <input type="hidden" name="action" value="edit">

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$c['id'] ?>"
                                    >

                                    <div class="row g-3">

                                        <div class="col-md-6">

                                            <label class="form-label">
                                                Charity Name
                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                name="charity_name"
                                                value="<?= h($c['charity_name']) ?>"
                                                required
                                            >

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">
                                                City
                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                name="city"
                                                value="<?= h($c['city']) ?>"
                                                required
                                            >

                                        </div>

                                        <div class="col-12">

                                            <label class="form-label">
                                                Description
                                            </label>

                                            <textarea
                                                class="form-control"
                                                name="description"
                                                rows="3"
                                                required
                                            ><?= h($c['description']) ?></textarea>

                                        </div>

                                        <div class="col-12">

                                            <label class="form-label">
                                                Connected Account
                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                value="<?= !empty($c['username']) ? h($c['username']) . ' (' . h($c['email']) . ')' : 'Not connected to a login account' ?>"
                                                disabled
                                            >

                                            <small class="text-muted">
                                                Account connections are created automatically during charity registration.
                                            </small>

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
