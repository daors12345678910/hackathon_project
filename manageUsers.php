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

        if ($action === 'add') {

            $name = trim($_POST['emri'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $role = $_POST['role'] ?? '';

            if (
                $name === '' ||
                $username === '' ||
                $email === '' ||
                $password === '' ||
                $confirm === ''
            ) {
                throw new Exception('Please fill in all fields.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid email address.');
            }

            if ($password !== $confirm) {
                throw new Exception('Passwords do not match.');
            }

            if (!in_array($role, ['donor', 'charity', 'admin'], true)) {
                throw new Exception('Invalid user role.');
            }

            $check = $conn->prepare(
                "SELECT id FROM users
                 WHERE username = :username OR email = :email"
            );

            $check->execute([
                ':username' => $username,
                ':email' => $email
            ]);

            if ($check->fetch()) {
                throw new Exception('Username or email already exists.');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users
                (emri, username, email, password, confirm_password, role)
                VALUES
                (:emri, :username, :email, :password, :confirm_password, :role)"
            );

            $stmt->execute([
                ':emri' => $name,
                ':username' => $username,
                ':email' => $email,
                ':password' => $hash,
                ':confirm_password' => $hash,
                ':role' => $role
            ]);

            $message = 'User created successfully.';

        } elseif ($action === 'edit') {

            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['emri'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($id <= 0 || $name === '' || $username === '' || $email === '') {
                throw new Exception('Please complete all required fields.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid email address.');
            }

            if (!in_array($role, ['donor', 'charity', 'admin'], true)) {
                throw new Exception('Invalid user role.');
            }

            if (($password !== '' || $confirm !== '') && $password !== $confirm) {
                throw new Exception('Passwords do not match.');
            }

            $check = $conn->prepare(
                "SELECT id FROM users
                 WHERE (username = :username OR email = :email)
                 AND id != :id"
            );

            $check->execute([
                ':username' => $username,
                ':email' => $email,
                ':id' => $id
            ]);

            if ($check->fetch()) {
                throw new Exception('Username or email is already used by another account.');
            }

            if ($password !== '') {

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare(
                    "UPDATE users
                     SET emri = :emri,
                         username = :username,
                         email = :email,
                         role = :role,
                         password = :password,
                         confirm_password = :confirm_password
                     WHERE id = :id"
                );

                $stmt->execute([
                    ':emri' => $name,
                    ':username' => $username,
                    ':email' => $email,
                    ':role' => $role,
                    ':password' => $hash,
                    ':confirm_password' => $hash,
                    ':id' => $id
                ]);

            } else {

                $stmt = $conn->prepare(
                    "UPDATE users
                     SET emri = :emri,
                         username = :username,
                         email = :email,
                         role = :role
                     WHERE id = :id"
                );

                $stmt->execute([
                    ':emri' => $name,
                    ':username' => $username,
                    ':email' => $email,
                    ':role' => $role,
                    ':id' => $id
                ]);
            }

            $message = 'User updated successfully.';

            if ($id === (int)$_SESSION['id']) {

                $_SESSION['emri'] = $name;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                if ($role !== 'admin') {
                    $_SESSION['role'] = $role;
                    header('Location: redirect.php');
                    exit();
                }
            }

        } elseif ($action === 'delete') {

            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('Invalid user.');
            }

            if ($id === (int)$_SESSION['id']) {
                throw new Exception('You cannot delete your own account.');
            }

            $stmt = $conn->prepare(
                "DELETE FROM users WHERE id = :id"
            );

            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('User not found.');
            }

            $message = 'User deleted successfully.';
        }

    } catch (PDOException $e) {

        $error = 'This user cannot be deleted or changed because the account may be connected to donations or other records.';

    } catch (Exception $e) {

        $error = $e->getMessage();
    }
}

$users = $conn->query(
    "SELECT id, emri, username, email, role
     FROM users
     ORDER BY id DESC"
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

    <title>Manage Users | Kind Society</title>

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
            <h2>Manage Users</h2>
            <p>Create, edit and manage donor, charity and administrator accounts.</p>
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

        <h4 class="mb-3">Create a User Account</h4>

        <form method="POST" action="manageUsers.php">

            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="add">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input class="form-control" name="emri" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input class="form-control" name="username" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role</label>

                    <select class="form-select" name="role" required>
                        <option value="donor">Donor</option>
                        <option value="charity">Charity</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        autocomplete="new-password"
                        required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input
                        type="password"
                        class="form-control"
                        name="confirm_password"
                        autocomplete="new-password"
                        required>
                </div>

            </div>

            <button class="btn btn-success mt-3" type="submit">
                Create User
            </button>

        </form>

    </section>

    <section class="content-card">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Registered Users</h4>
            <span class="badge bg-success"><?= count($users) ?> total</span>
        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($users as $u): ?>

                        <tr>

                            <td><?= (int)$u['id'] ?></td>
                            <td><?= h($u['emri']) ?></td>
                            <td><?= h($u['username']) ?></td>
                            <td><?= h($u['email']) ?></td>

                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : ($u['role'] === 'charity' ? 'bg-info text-dark' : 'bg-success') ?>">
                                    <?= h(ucfirst($u['role'])) ?>
                                </span>
                            </td>

                            <td class="text-nowrap">

                                <button
                                    class="btn btn-primary btn-sm"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#edit-user-<?= (int)$u['id'] ?>">
                                    Edit
                                </button>

                                <?php if ((int)$u['id'] !== (int)$_SESSION['id']): ?>

                                    <form method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this user account?');">

                                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">

                                        <button class="btn btn-danger btn-sm" type="submit">
                                            Delete
                                        </button>

                                    </form>

                                <?php else: ?>

                                    <span class="small">Your account</span>

                                <?php endif; ?>

                            </td>

                        </tr>

                        <tr class="collapse" id="edit-user-<?= (int)$u['id'] ?>">

                            <td colspan="6">

                                <form method="POST" class="p-3">

                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">

                                    <div class="row g-3">

                                        <div class="col-md-3">
                                            <label class="form-label">Full Name</label>

                                            <input
                                                class="form-control"
                                                name="emri"
                                                value="<?= h($u['emri']) ?>"
                                                required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Username</label>

                                            <input
                                                class="form-control"
                                                name="username"
                                                value="<?= h($u['username']) ?>"
                                                required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Email</label>

                                            <input
                                                type="email"
                                                class="form-control"
                                                name="email"
                                                value="<?= h($u['email']) ?>"
                                                required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Role</label>

                                            <select class="form-select" name="role" required>

                                                <?php foreach (['donor', 'charity', 'admin'] as $role): ?>

                                                    <option
                                                        value="<?= $role ?>"
                                                        <?= $u['role'] === $role ? 'selected' : '' ?>>

                                                        <?= ucfirst($role) ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">
                                                New Password
                                                <span class="small">(leave blank to keep current)</span>
                                            </label>

                                            <input
                                                type="password"
                                                class="form-control"
                                                name="password"
                                                autocomplete="new-password">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Confirm New Password</label>

                                            <input
                                                type="password"
                                                class="form-control"
                                                name="confirm_password"
                                                autocomplete="new-password">
                                        </div>

                                    </div>

                                    <button class="btn btn-success mt-3" type="submit">
                                        Save Changes
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if (!$users): ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                No users found.
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