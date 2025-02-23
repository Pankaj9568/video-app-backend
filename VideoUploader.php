<?php
class VideoUploader {
    private $pdo;

    // Constructor to initialize database connection
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Uploads a video and inserts its metadata into the database.
     *
     * @param string $userId
     * @param string $videoUrl
     * @param string $posterUrl
     * @param string $title
     * @param string $description
     * @param string $role (user or moderator)
     * @param string $source (youtube or upload)
     * @return bool
     */
    public function uploadVideo(
        string $userId,
        string $videoUrl,
        string $posterUrl,
        string $title,
        string $description,
        string $role,
        string $source = 'upload' // Default to 'upload'
    ): bool {
        // Set the video status based on the user role
        $status = ($role === 'moderator') ? 'approved' : 'pending';

        // Generate a unique video ID
        $videoId = $this->generateUUID();

        // Prepare the SQL query
        $sql = "INSERT INTO videos (
                    video_id, user_id, video_url, poster_url, title, description, status, source
                ) VALUES (
                    :video_id, :user_id, :video_url, :poster_url, :title, :description, :status, :source
                )";

        // Prepare and execute the statement
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':video_id' => $videoId,
            ':user_id' => $userId,
            ':video_url' => $videoUrl,
            ':poster_url' => $posterUrl,
            ':title' => $title,
            ':description' => $description,
            ':status' => $status,
            ':source' => $source
        ]);

        // Check if the insertion was successful
        return $stmt->rowCount() > 0;
    }

    /**
     * Fetches video metadata (title and description) from YouTube using the YouTube Data API.
     *
     * @param string $youtubeUrl
     * @return array|null
     */
    public function fetchYouTubeMetadata(string $youtubeUrl): ?array {
        // Extract video ID from YouTube URL
        if (preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $youtubeUrl, $matches)) {
            $youtubeVideoId = $matches[4];

            // Fetch video details using YouTube Data API
            $apiUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id=$youtubeVideoId&key=" . YOUTUBE_API_KEY;
            $response = file_get_contents($apiUrl);
            $data = json_decode($response, true);

            if (!empty($data['items'])) {
                $snippet = $data['items'][0]['snippet'];
                return [
                    'title' => $snippet['title'],
                    'description' => $snippet['description'],
                    'thumbnail' => $snippet['thumbnails']['high']['url']
                ];
            }
        }

        return null; // Invalid YouTube URL or API error
    }

    /**
     * Generates a UUID for video_id.
     *
     * @return string
     */
    private function generateUUID(): string {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
?>