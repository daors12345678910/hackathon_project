
<?php
include_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $emri = trim($_POST['emri'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $admin_code = $_POST['admin_code'] ?? '';

    // Check that all required fields are filled in
    if (
        $emri === '' ||
        $username === '' ||
        $email === '' ||
        $password === '' ||
        $confirm_password === '' ||
        $role === ''
    ) {
        die("Please fill in all fields.");
    }

    // Only these three roles are allowed
    if (!in_array($role, ['donor', 'charity', 'admin'], true)) {
        die("Invalid account type.");
    }

    // Check email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Please enter a valid email address.");
    }

    // Check that both passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Check the admin registration code
    if ($role === 'admin') {
        if (
            !defined('ADMIN_SIGNUP_CODE') ||
            !hash_equals(ADMIN_SIGNUP_CODE, $admin_code)
        ) {
            die("Incorrect admin registration code.");
        }
    }

    try {
        // Check whether the username or email already exists
        $checkSql = "SELECT id FROM users
                     WHERE username = :username OR email = :email";

        $checkUser = $conn->prepare($checkSql);
        $checkUser->execute([
            ':username' => $username,
            ':email' => $email
        ]);

        if ($checkUser->fetch()) {
            die("Username or email already exists.");
        }

        // Hash both passwords
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $hashedConfirmPassword = password_hash(
            $confirm_password,
            PASSWORD_DEFAULT
        );

        // Save the new account and its selected role
        $sql = "INSERT INTO users
                (emri, username, email, password, confirm_password, role)
                VALUES
                (:emri, :username, :email, :password, :confirm_password, :role)";

        $registerUser = $conn->prepare($sql);

        $registerUser->execute([
            ':emri' => $emri,
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':confirm_password' => $hashedConfirmPassword,
            ':role' => $role
        ]);

        // Registration successful
        header("Location: login.php");
        exit();

    } catch (PDOException $e) {
        die("Registration failed. Please try again.");
    }
}
?>
