<?php
session_start();
require 'config.php';
require 'VideoUploader.php';

// Redirect to login if the user is not logged in or not a moderator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'moderator') {
    header("Location: $baseUrl/login.php");
    exit;
}

// Initialize the VideoUploader class
$videoUploader = new VideoUploader($pdo);

// Get the video ID from the query parameter
$videoId = $_GET['video_id'];

// Fetch the video details from the database
$stmt = $pdo->prepare("SELECT * FROM videos WHERE video_id = ?");
$stmt->execute([$videoId]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated fields from the form
    $title = $_POST['title'];
    $description = $_POST['description'];
    $videoUrl = $_POST['video_url'];

    // Handle poster image upload
    $posterUrl = $video['poster_url']; // Default to existing poster URL
    if (!empty($_FILES['poster_file']['name'])) {
        $uploadDir = 'uploads/posters/';

        // Ensure the upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Handle poster file upload
        $posterFile = $_FILES['poster_file'];
        $posterFileName = basename($posterFile['name']);
        $posterFilePath = $uploadDir . uniqid('poster_') . '_' . $posterFileName;

        // Validate file type
        $allowedPosterTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($posterFile['type'], $allowedPosterTypes)) {
            if (move_uploaded_file($posterFile['tmp_name'], $posterFilePath)) {
                $posterUrl = $posterFilePath; // Update poster URL to the new file path
            } else {
                echo "<p>Error: Failed to move uploaded file.</p>";
                exit;
            }
        } else {
            echo "<p>Error: Invalid poster file type. Allowed types: JPEG, PNG, GIF.</p>";
            exit;
        }
    }

    // Update the video details in the database
    $sql = "UPDATE videos 
            SET title = :title, 
                description = :description, 
                poster_url = :poster_url, 
                video_url = :video_url 
            WHERE video_id = :video_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':poster_url' => $posterUrl,
        ':video_url' => $videoUrl,
        ':video_id' => $videoId
    ]);

    // Redirect back to the moderator dashboard
    header("Location: moderator_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Video</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .edit-form {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .edit-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .edit-form input[type="text"],
        .edit-form textarea,
        .edit-form input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .edit-form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .edit-form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="edit-form">
        <h1>Edit Video</h1>
        <form method="POST" enctype="multipart/form-data">
            <!-- Video Title -->
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($video['title']); ?>" required>

            <!-- Video Description -->
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($video['description']); ?></textarea>

            <!-- Poster Image Upload -->
            <label for="poster_file">Poster Image:</label>
            <input type="file" id="poster_file" name="poster_file">
            <?php if (!empty($video['poster_url'])): ?>
                <p>Current Poster: <img src="<?php echo htmlspecialchars($video['poster_url']); ?>" alt="Current Poster" style="max-width: 200px;"></p>
            <?php endif; ?>

            <!-- Video URL -->
            <label for="video_url">Video URL:</label>
            <input type="text" id="video_url" name="video_url" value="<?php echo htmlspecialchars($video['video_url']); ?>" required>

            <!-- Submit Button -->
            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>