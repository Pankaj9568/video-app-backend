<?php
session_start(); // Start the session
require 'config.php';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate inputs
    $errors = [];

    // Check if username is unique
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    if ($stmt->fetch()) {
        $errors[] = "Username already exists.";
    }

    // Check if email is unique
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        $errors[] = "Email already exists.";
    }

    // Validate password
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Generate a UUID for the user ID
        $userId = generateUUID();

        // Insert user into the database
        $stmt = $pdo->prepare("INSERT INTO users (user_id, username, email, password_hash, is_verified, role) VALUES (:user_id, :username, :email, :password_hash, :is_verified, :role)");
        $stmt->execute([
            ':user_id' => $userId,
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':is_verified' => false, // Default: not verified
            ':role' => 'user' // Default role: user
        ]);

        // Redirect to login page after successful registration
        header("Location: $baseUrl/login.php");
        exit;
    }
}

/**
 * Generates a UUID for user_id.
 *
 * @return string
 */
function generateUUID(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Register</h1>

    <!-- Display errors -->
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Registration Form -->
    <form method="POST" action="<?php echo $baseUrl; ?>/register.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="<?php echo $baseUrl; ?>/login.php">Login here</a>.</p>
</body>
</html>