<?php
session_start();
require 'config.php';

// Redirect to login if the user is not logged in or not a moderator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'moderator') {
    header("Location: $baseUrl/login.php");
    exit;
}

// Fetch pending videos
$stmt = $pdo->prepare("SELECT video_id, title, description, created_at FROM videos WHERE status = 'pending'");
$stmt->execute();
$pendingVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch approved/rejected videos
$stmt = $pdo->prepare("SELECT video_id, title, description, status, created_at FROM videos WHERE status IN ('approved', 'rejected')");
$stmt->execute();
$moderatedVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderator Dashboard</title>
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
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .approve-btn, .reject-btn, .edit-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .approve-btn {
            background-color: #4CAF50;
            color: white;
        }
        .reject-btn {
            background-color: #f44336;
            color: white;
        }
        .edit-btn {
            background-color: #007bff;
            color: white;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .upload-link {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .upload-link:hover {
            background-color: #0056b3;
        }
        .logout-link {
            float: right;
            color: #555;
            text-decoration: none;
            font-weight: bold;
        }
        .logout-link:hover {
            color: #333;
        }
        .search-form {
            margin-bottom: 20px;
        }
        .search-form input[type="text"] {
            padding: 8px;
            width: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-form button {
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logout Link -->
        <a href="<?php echo $baseUrl; ?>/logout.php" class="logout-link">Logout</a>

        <h1>Moderator Dashboard</h1>

        <!-- Upload Video Link -->
        <a href="<?php echo $baseUrl; ?>/upload_video.php" class="upload-link">Upload Video</a>

        <!-- User Search Form -->
        <div class="search-form">
            <h2>Search User</h2>
            <form method="GET" action="view_user.php">
                <label for="user_id">Enter User ID:</label>
                <input type="text" id="user_id" name="user_id" required>
                <button type="submit">Search</button>
            </form>
        </div>

        <!-- Pending Videos -->
        <h2>Pending Videos</h2>
        <?php if (count($pendingVideos) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Uploaded On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingVideos as $video): ?>
                        <tr>
                            <td><?php echo $video['title']; ?></td>
                            <td><?php echo $video['description']; ?></td>
                            <td><?php echo $video['created_at']; ?></td>
                            <td class="action-buttons">
                                <form method="POST" action="approve_video.php" style="display: inline;">
                                    <input type="hidden" name="video_id" value="<?php echo $video['video_id']; ?>">
                                    <button type="submit" class="approve-btn">Approve</button>
                                </form>
                                <form method="POST" action="reject_video.php" style="display: inline;">
                                    <input type="hidden" name="video_id" value="<?php echo $video['video_id']; ?>">
                                    <button type="submit" class="reject-btn">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending videos.</p>
        <?php endif; ?>

        <!-- Moderated Videos -->
        <h2>Moderated Videos</h2>
        <?php if (count($moderatedVideos) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Moderated On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($moderatedVideos as $video): ?>
                        <tr>
                            <td><?php echo $video['title']; ?></td>
                            <td><?php echo $video['description']; ?></td>
                            <td><?php echo ucfirst($video['status']); ?></td>
                            <td><?php echo $video['created_at']; ?></td>
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
            <p>No moderated videos.</p>
        <?php endif; ?>
    </div>
</body>
</html>