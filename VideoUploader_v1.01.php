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
     * @return bool
     */
    public function uploadVideo(
        string $userId,
        string $videoUrl,
        string $posterUrl,
        string $title,
        string $description,
        string $role
    ): bool {
        // Set the video status based on the user role
        $status = ($role === 'moderator') ? 'approved' : 'pending';

        // Generate a unique video ID
        $videoId = $this->generateUUID();

        // Prepare the SQL query
        $sql = "INSERT INTO videos (
                    video_id, user_id, video_url, poster_url, title, description, status
                ) VALUES (
                    :video_id, :user_id, :video_url, :poster_url, :title, :description, :status
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
            ':status' => $status
        ]);

        // Check if the insertion was successful
        return $stmt->rowCount() > 0;
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