<?php
require 'config.php';

// Get the page number from the request
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Number of videos per page
$offset = ($page - 1) * $limit;

// Fetch approved videos with counts for likes, comments, and shares
$query = "
    SELECT 
        v.video_id, 
        v.title, 
        v.description, 
        v.video_url, 
        v.created_at,
        COUNT(DISTINCT l.like_id) AS likes_count,
        COUNT(DISTINCT c.comment_id) AS comments_count,
        COUNT(DISTINCT s.share_id) AS shares_count
    FROM videos v
    LEFT JOIN likes l ON v.video_id = l.video_id
    LEFT JOIN comments c ON v.video_id = c.video_id
    LEFT JOIN shares s ON v.video_id = s.video_id
    WHERE v.status = 'approved'
    GROUP BY v.video_id
    ORDER BY v.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $pdo->prepare($query);
$stmt->execute([$limit, $offset]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the videos as JSON
header('Content-Type: application/json');
echo json_encode($videos);
?>