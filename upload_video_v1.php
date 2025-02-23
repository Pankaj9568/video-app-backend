<?php
session_start();
require 'config.php';
require 'VideoUploader.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: $baseUrl/login.php");
    exit;
}

// Initialize the VideoUploader class
$videoUploader = new VideoUploader($pdo);

// Handle video upload form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_video'])) {
    $videoUrl = $_POST['video_url'];
    $posterUrl = $_POST['poster_url'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    try {
        // Upload the video
        if ($videoUploader->uploadVideo($_SESSION['user_id'], $videoUrl, $posterUrl, $title, $description, $_SESSION['role'])) {
            echo "<p>Video uploaded successfully!</p>";
        } else {
            echo "<p>Failed to upload video.</p>";
        }
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .upload-form input, .upload-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        .upload-form button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .upload-form button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Video</h1>
        <form method="POST" action="" class="upload-form">
            <label for="video_url">Video URL:</label>
            <input type="text" id="video_url" name="video_url" required>
            <br>

            <label for="poster_url">Poster URL:</label>
            <input type="text" id="poster_url" name="poster_url" required>
            <br>

            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            <br>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>
            <br>

            <button type="submit" name="upload_video">Upload Video</button>
        </form>
    </div>
</body>
</html>