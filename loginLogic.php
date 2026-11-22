
<?php
session_start();

include_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        die("Please fill in all fields.");
    }

    // Find the user in the database
    $sql = "SELECT id, emri, username, email, password, role
            FROM users
            WHERE username = :username";

    $selectUser = $conn->prepare($sql);

    $selectUser->execute([
        ':username' => $username
    ]);

    // Fetch the user's information
    $data = $selectUser->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die("The user does not exist.");
    }

    // Check the password
    if (password_verify($password, $data['password'])) {

        // Save user information in the session
        $_SESSION['id'] = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['email'] = $data['email'];
        $_SESSION['emri'] = $data['emri'];
        $_SESSION['role'] = $data['role'];

        header("Location: redirect.php");
        exit();

    } else {
        die("The password is not valid.");
    }
}
?>
