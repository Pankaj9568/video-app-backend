<?php
session_start();
require 'config.php';

// Redirect if the user is not logged in or not a moderator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'moderator') {
    header("Location: $baseUrl/login.php");
    exit;
}

// Approve the video
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $videoId = $_POST['video_id'];

    try {
        $stmt = $pdo->prepare("UPDATE videos SET status = 'approved' WHERE video_id = :video_id");
        $stmt->execute([':video_id' => $videoId]);

        // Redirect back to the moderator dashboard
        header("Location: $baseUrl/moderator_dashboard.php");
        exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>