
<?php
include_once('config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.html");
    exit();
}

// Get registration details
$emri = trim($_POST['emri'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? '';
$admin_code = $_POST['admin_code'] ?? '';

// Charity details
$charity_name = trim($_POST['charity_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$city = trim($_POST['city'] ?? '');

// Check required account fields
if (
    $emri === '' ||
    $username === '' ||
    $email === '' ||
    $password === '' ||
    $confirm_password === '' ||
    $role === ''
) {
    die("Please fill in all the required fields.");
}

// Validate role
if (!in_array($role, ['donor', 'charity', 'admin'], true)) {
    die("Invalid account type.");
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Please enter a valid email address.");
}

// Check passwords
if ($password !== $confirm_password) {
    die("Passwords do not match.");
}

// Validate charity information
if ($role === 'charity') {
    if ($charity_name === '' || $description === '' || $city === '') {
        die("Please complete all charity information.");
    }
}

// Verify admin registration code
if ($role === 'admin') {
    if (
        !defined('ADMIN_SIGNUP_CODE') ||
        !hash_equals(ADMIN_SIGNUP_CODE, $admin_code)
    ) {
        die("Incorrect admin registration code.");
    }
}

try {

    // Check whether username or email already exists
    $checkUser = $conn->prepare(
        "SELECT id FROM users
         WHERE username = :username OR email = :email"
    );

    $checkUser->execute([
        ':username' => $username,
        ':email' => $email
    ]);

    if ($checkUser->fetch()) {
        die("Username or email already exists.");
    }

    // Hash passwords
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $hashedConfirmPassword = password_hash($confirm_password, PASSWORD_DEFAULT);

    // Start transaction so account and charity profile are created together
    $conn->beginTransaction();

    // Create user account
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

    // Get the ID of the newly created account
    $userId = (int)$conn->lastInsertId();

    // Automatically create a charity profile for charity accounts
    if ($role === 'charity') {

        $profileSql = "INSERT INTO charity_profiles
                       (charity_name, description, city, user_id)
                       VALUES
                       (:charity_name, :description, :city, :user_id)";

        $createProfile = $conn->prepare($profileSql);

        $createProfile->execute([
            ':charity_name' => $charity_name,
            ':description' => $description,
            ':city' => $city,
            ':user_id' => $userId
        ]);
    }

    // Save both records
    $conn->commit();

    // Registration successful
    header("Location: login.php");
    exit();

} catch (PDOException $e) {

    // Undo account creation if profile creation failed
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    die("Registration failed. Please check your information and try again.");
}
?>
