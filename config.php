<?php
// Database configuration
$host = 'localhost';
$dbname = 'video_sharing_app';
$username = 'root';
$password = '';

// Base URL for the application
$baseUrl = 'http://localhost/pro'; // Your base URL

// YouTube Data API Key
define('YOUTUBE_API_KEY', 'AIzaSyCpFIRU88CphWaTLK7N2TBrXfU9GIzKbO8'); // Replace with your actual YouTube API key

// Create a PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>