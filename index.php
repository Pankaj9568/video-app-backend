<?php
session_start();
require 'config.php';
require 'VideoUploader.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: $baseUrl/login.php");
    exit;
}

// Redirect moderators to the moderator dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'moderator') {
    header("Location: $baseUrl/moderator_dashboard.php");
    exit;
}

// Initialize the VideoUploader class
$videoUploader = new VideoUploader($pdo);

// Fetch user details
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email, profile_pic FROM users WHERE user_id = :user_id");
$stmt->execute([':user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch videos uploaded by the user
$stmt = $pdo->prepare("SELECT video_id, title, description, created_at FROM videos WHERE user_id = :user_id");
$stmt->execute([':user_id' => $userId]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch comments made by the user
$stmt = $pdo->prepare("SELECT c.comment_id, c.comment_text, c.created_at, v.title AS video_title 
                       FROM comments c 
                       JOIN videos v ON c.video_id = v.video_id 
                       WHERE c.user_id = :user_id");
$stmt->execute([':user_id' => $userId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch liked videos count
$stmt = $pdo->prepare("SELECT COUNT(*) AS liked_count FROM likes WHERE user_id = :user_id");
$stmt->execute([':user_id' => $userId]);
$likedCount = $stmt->fetch(PDO::FETCH_ASSOC)['liked_count'];

// Handle video upload form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_video'])) {
    $videoUrl = $_POST['video_url'];
    $posterUrl = $_POST['poster_url'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    try {
        // Upload the video
        if ($videoUploader->uploadVideo($userId, $videoUrl, $posterUrl, $title, $description, $_SESSION['role'] ?? 'user')) {
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
    <title>User Dashboard</title>
    <style>
        /* Add your CSS styles here */
    </style>
</head>
<body>
    <div class="container">
        <!-- Logout Link -->
        <a href="<?php echo $baseUrl; ?>/logout.php" class="logout-link">Logout</a>

        <!-- User Details -->
        <div class="user-details">
            <h1>User Dashboard</h1>
            <img src="<?php echo $user['profile_pic']; ?>" alt="Profile Picture">
            <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
            <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
        </div>

        <!-- Videos Uploaded by the User -->
        <div class="videos">
            <h2>Videos Uploaded</h2>
            <?php if (count($videos) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Uploaded On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($videos as $video): ?>
                            <tr>
                                <td><?php echo $video['title']; ?></td>
                                <td><?php echo $video['description']; ?></td>
                                <td><?php echo $video['created_at']; ?></td>
                                <td>
                                    <button type="button" class="delete-btn" onclick="confirmDelete('<?php echo $video['video_id']; ?>')">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No videos uploaded yet.</p>
            <?php endif; ?>
        </div>

        <!-- Comments Made by the User -->
        <div class="comments">
            <h2>Comments Made</h2>
            <?php if (count($comments) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Video Title</th>
                            <th>Comment</th>
                            <th>Commented On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comments as $comment): ?>
                            <tr>
                                <td><?php echo $comment['video_title']; ?></td>
                                <td><?php echo $comment['comment_text']; ?></td>
                                <td><?php echo $comment['created_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No comments made yet.</p>
            <?php endif; ?>
        </div>

        <!-- Liked Videos Count -->
        <div class="likes">
            <h2>Liked Videos</h2>
            <p>Total videos liked: <?php echo $likedCount; ?></p>
        </div>

        <!-- Video Upload Form -->
        <div class="upload-form">
            <h2>Upload a Video</h2>
            <form method="POST" action="">
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
    </div>
</body>
</html>