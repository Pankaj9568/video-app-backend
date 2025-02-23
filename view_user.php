<?php
session_start();
require 'config.php';

// Redirect to login if the user is not logged in or not a moderator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'moderator') {
    header("Location: $baseUrl/login.php");
    exit;
}

// Get the user ID from the query parameter
$userId = $_GET['user_id'];

// Fetch user details
$stmt = $pdo->prepare("SELECT username, email, profile_pic FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p>User not found.</p>";
    exit;
}

// Fetch videos uploaded by the user
$stmt = $pdo->prepare("SELECT video_id, title, description, video_url, created_at FROM videos WHERE user_id = ?");
$stmt->execute([$userId]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch comments made by the user
$stmt = $pdo->prepare("SELECT c.comment_id, c.comment_text, c.created_at, v.title AS video_title 
                       FROM comments c 
                       JOIN videos v ON c.video_id = v.video_id 
                       WHERE c.user_id = ?");
$stmt->execute([$userId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Records - Moderator Dashboard</title>
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
        h1, h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Link -->
        <a href="moderator_dashboard.php" class="back-link">← Back to Dashboard</a>

        <h1>User Records: <?php echo htmlspecialchars($user['username']); ?></h1>

        <!-- User Details -->
        <h2>User Details</h2>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Profile Picture:</strong> <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" style="width: 100px; height: auto;"></p>

        <!-- Videos Uploaded by the User -->
        <h2>Videos Uploaded</h2>
        <?php if (count($videos) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Video URL</th>
                        <th>Uploaded On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($video['title']); ?></td>
                            <td><?php echo htmlspecialchars($video['description']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($video['video_url']); ?>" target="_blank">View Video</a></td>
                            <td><?php echo htmlspecialchars($video['created_at']); ?>
                            <td class="action-buttons">
                                <!-- Edit Button -->
                                <a href="edit_video.php?video_id=<?php echo $video['video_id']; ?>" class="edit-btn">Edit</a>
                                <!-- Delete Button -->
                                <form method="POST" action="delete_video.php" style="display: inline;">
                                    <input type="hidden" name="video_id" value="<?php echo $video['video_id']; ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this video?');">Delete</button>
                                </form>
                        
                        
                        </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No videos uploaded by this user.</p>
        <?php endif; ?>

        <!-- Comments Made by the User -->
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
                            <td><?php echo htmlspecialchars($comment['video_title']); ?></td>
                            <td><?php echo htmlspecialchars($comment['comment_text']); ?></td>
                            <td><?php echo htmlspecialchars($comment['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No comments made by this user.</p>
        <?php endif; ?>
    </div>
</body>
</html>