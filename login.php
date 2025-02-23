<?php
session_start();
require 'config.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user from the database
    $stmt = $pdo->prepare("SELECT user_id, username, password_hash, role FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Store the user's role in the session

        // Redirect based on role
        if ($user['role'] === 'moderator') {
            header("Location: $baseUrl/moderator_dashboard.php");
        } else {
            header("Location: $baseUrl/index.php");
        }
        exit;
    } else {
        echo "Invalid email or password.";
    }
}
?>

<!-- Login Form -->
<form method="POST" action="<?php echo $baseUrl; ?>/login.php">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit">Login</button>
</form>