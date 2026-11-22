
<?php
session_start();
include_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        die("Please enter your username and password.");
    }

    try {

        // Find user by username
        $sql = "SELECT * FROM users WHERE username = :username";

        $selectUser = $conn->prepare($sql);

        $selectUser->bindParam(':username', $username);

        $selectUser->execute();

        // Get user information
        $data = $selectUser->fetch(PDO::FETCH_ASSOC);

        // Check whether user exists and password is correct
        if (
    $data &&
    (
        password_verify($password, $data['password']) ||
        $password === $data['password']
    )
) {
            // Save user information in session
            $_SESSION['id'] = $data['id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['emri'] = $data['emri'];
            $_SESSION['role'] = $data['role'];

            // Redirect according to role
            if ($data['role'] === 'admin') {
                header("Location: admin.php");
                exit();
            }

            if ($data['role'] === 'donor') {
                header("Location: donor.php");
                exit();
            }

            if ($data['role'] === 'charity') {
                header("Location: charity.php");
                exit();
            }

        } else {

            die("Incorrect username or password.");

        }

    } catch (PDOException $e) {

        die("Login failed. Please try again.");

    }

} else {

    header("Location: login.php");
    exit();

}
?>
