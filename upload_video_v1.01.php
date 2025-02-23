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
    // File upload directory
    $uploadDir = 'uploads/videos/';

    // Ensure the upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle video file upload
    $videoFile = $_FILES['video_file'];
    $videoFileName = basename($videoFile['name']);
    $videoFilePath = $uploadDir . uniqid('video_') . '_' . $videoFileName;

    // Handle poster file upload
    $posterFile = $_FILES['poster_file'];
    $posterFileName = basename($posterFile['name']);
    $posterFilePath = $uploadDir . uniqid('poster_') . '_' . $posterFileName;

    // Validate file types
    $allowedVideoTypes = ['video/mp4', 'video/mpeg', 'video/quicktime'];
    $allowedPosterTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($videoFile['type'], $allowedVideoTypes)) {
        echo "<p>Error: Invalid video file type. Allowed types: MP4, MPEG, QuickTime.</p>";
        exit;
    }

    if (!in_array($posterFile['type'], $allowedPosterTypes)) {
        echo "<p>Error: Invalid poster file type. Allowed types: JPEG, PNG, GIF.</p>";
        exit;
    }

    // Move uploaded files to the uploads/videos folder
    if (move_uploaded_file($videoFile['tmp_name'], $videoFilePath) && move_uploaded_file($posterFile['tmp_name'], $posterFilePath)) {
        // Get form data
        $title = $_POST['title'];
        $description = $_POST['description'];

        try {
            // Upload the video metadata to the database
            if ($videoUploader->uploadVideo($_SESSION['user_id'], $videoFilePath, $posterFilePath, $title, $description, $_SESSION['role'])) {
                echo "<p>Video uploaded successfully!</p>";
            } else {
                echo "<p>Failed to upload video.</p>";
            }
        } catch (Exception $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Error: Failed to move uploaded files.</p>";
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
        .upload-form input, .upload-form textarea, .upload-form button {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        .upload-form button {
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
        <form method="POST" action="" class="upload-form" enctype="multipart/form-data">
            <!-- Video File Upload -->
            <label for="video_file">Video File:</label>
            <input type="file" id="video_file" name="video_file" accept="video/mp4, video/mpeg, video/quicktime" required>
            <br>

            <!-- Poster File Upload -->
            <label for="poster_file">Poster Image:</label>
            <input type="file" id="poster_file" name="poster_file" accept="image/jpeg, image/png, image/gif" required>
            <br>

            <!-- Title -->
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            <br>

            <!-- Description -->
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>
            <br>

            <!-- Submit Button -->
            <button type="submit" name="upload_video">Upload Video</button>
        </form>
    </div>
</body>
</html>