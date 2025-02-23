<?php
session_start();
require 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit;
}

// Get the video ID from the request
$videoId = $_POST['video_id'];
$userId = $_SESSION['user_id'];

// Delete the video
try {
    $stmt = $pdo->prepare("DELETE FROM videos WHERE video_id = :video_id AND user_id = :user_id");
    $stmt->execute([':video_id' => $videoId, ':user_id' => $userId]);

    if ($stmt->rowCount() > 0) {
        echo "Video deleted successfully.";
    } else {
        echo "Failed to delete video.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>