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
    $source = $_POST['source']; // 'youtube' or 'upload'
    $title = $_POST['title'];
    $description = $_POST['description'];

    if ($source === 'youtube') {
        // Handle YouTube video upload
        $youtubeUrl = $_POST['youtube_url'];

        // Extract video ID from YouTube URL
        if (preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $youtubeUrl, $matches)) {
            $youtubeVideoId = $matches[4];

            // Fetch video details using YouTube Data API
            $apiUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id=$youtubeVideoId&key=" . YOUTUBE_API_KEY;
            $response = file_get_contents($apiUrl);
            $data = json_decode($response, true);

            if (!empty($data['items'])) {
                $snippet = $data['items'][0]['snippet'];
                $title = $snippet['title']; // Extract title
                $description = $snippet['description']; // Extract description
                $videoUrl = "https://www.youtube.com/watch?v=$youtubeVideoId"; // YouTube URL
                $posterUrl = $snippet['thumbnails']['high']['url']; // Thumbnail URL

                // Upload the video metadata to the database
                if ($videoUploader->uploadVideo($_SESSION['user_id'], $videoUrl, $posterUrl, $title, $description, $_SESSION['role'], 'youtube')) {
                    echo "<p>YouTube video uploaded successfully!</p>";
                } else {
                    echo "<p>Failed to upload YouTube video.</p>";
                }
            } else {
                echo "<p>Error: Invalid YouTube video URL.</p>";
            }
        } else {
            echo "<p>Error: Invalid YouTube video URL.</p>";
        }
    } else {
        // Handle file upload
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
            // Upload the video metadata to the database
            if ($videoUploader->uploadVideo($_SESSION['user_id'], $videoFilePath, $posterFilePath, $title, $description, $_SESSION['role'], 'upload')) {
                echo "<p>Video uploaded successfully!</p>";
            } else {
                echo "<p>Failed to upload video.</p>";
            }
        } else {
            echo "<p>Error: Failed to move uploaded files.</p>";
        }
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
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            <!-- Video Source -->
            <label for="source">Video Source:</label>
            <select id="source" name="source" required>
                <option value="upload">Upload Video File</option>
                <option value="youtube">YouTube URL</option>
            </select>
            <br>

            <!-- YouTube URL Field -->
            <div id="youtube-fields" style="display: none;">
                <label for="youtube_url">YouTube URL:</label>
                <input type="text" id="youtube_url" name="youtube_url">
                <button type="button" id="fetch-metadata">Fetch Metadata</button>
                <br>
            </div>

            <!-- File Upload Fields -->
            <div id="upload-fields">
                <label for="video_file">Video File:</label>
                <input type="file" id="video_file" name="video_file">
                <br>

                <label for="poster_file">Poster Image:</label>
                <input type="file" id="poster_file" name="poster_file">
                <br>
            </div>

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

    <script>
        // Show/hide fields based on video source
        const sourceSelect = document.getElementById('source');
        const youtubeFields = document.getElementById('youtube-fields');
        const uploadFields = document.getElementById('upload-fields');

        sourceSelect.addEventListener('change', function () {
            if (this.value === 'youtube') {
                youtubeFields.style.display = 'block';
                uploadFields.style.display = 'none';
            } else {
                youtubeFields.style.display = 'none';
                uploadFields.style.display = 'block';
            }
        });

        // Fetch YouTube metadata
        document.getElementById('fetch-metadata').addEventListener('click', function () {
            const youtubeUrl = document.getElementById('youtube_url').value;
            const videoId = youtubeUrl.match(/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);

            if (videoId) {
                const apiUrl = `https://www.googleapis.com/youtube/v3/videos?part=snippet&id=${videoId[1]}&key=<?php echo YOUTUBE_API_KEY; ?>`;

                fetch(apiUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.items && data.items.length > 0) {
                            const snippet = data.items[0].snippet;
                            document.getElementById('title').value = snippet.title;
                            document.getElementById('description').value = snippet.description;
                        } else {
                            alert('Error: Invalid YouTube URL or video not found.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching YouTube metadata:', error);
                        alert('Error fetching YouTube metadata. Please try again.');
                    });
            } else {
                alert('Error: Invalid YouTube URL.');
            }
        });
    </script>
</body>
</html>