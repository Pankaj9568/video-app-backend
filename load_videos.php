<?php
session_start();
require 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit;
}

// Fetch videos uploaded by the user
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT video_id, title, description, created_at FROM videos WHERE user_id = :user_id");
$stmt->execute([':user_id' => $userId]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($videos) > 0): ?>
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
                    <form id="delete-form-<?php echo $video['video_id']; ?>" method="POST" action="" style="display: inline;">
                        <input type="hidden" name="video_id" value="<?php echo $video['video_id']; ?>">
                        <input type="hidden" name="delete_video" value="1">
                        <button type="button" class="delete-btn" onclick="confirmDelete('<?php echo $video['video_id']; ?>')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
<?php else: ?>
    <p>No videos uploaded yet.</p>
<?php endif; ?>