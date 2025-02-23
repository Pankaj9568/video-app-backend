-- Create Database
CREATE DATABASE IF NOT EXISTS video_sharing_app;
USE video_sharing_app;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id VARCHAR(50) NOT NULL PRIMARY KEY, -- User-friendly ID (e.g., "user_1", "user_2")
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255),
    is_verified BOOLEAN DEFAULT FALSE,
    role ENUM('user', 'moderator') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Followers Table
CREATE TABLE IF NOT EXISTS followers (
    follower_id VARCHAR(50) NOT NULL, -- The user who is following
    followee_id VARCHAR(50) NOT NULL, -- The user being followed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (follower_id, followee_id), -- Composite primary key
    FOREIGN KEY (follower_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (followee_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Videos Table
CREATE TABLE IF NOT EXISTS videos (
    video_id CHAR(36) NOT NULL PRIMARY KEY, -- UUID
    user_id VARCHAR(50) NOT NULL, -- Updated to match users table
    video_url VARCHAR(255) NOT NULL,
    poster_url VARCHAR(255),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    source ENUM('youtube', 'upload') DEFAULT 'upload', -- New column for video source
    moderator_comments TEXT,
    reupload_comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    comment_id CHAR(36) NOT NULL PRIMARY KEY, -- UUID
    video_id CHAR(36) NOT NULL,
    user_id VARCHAR(50) NOT NULL, -- Updated to match users table
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(video_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Likes Table
CREATE TABLE IF NOT EXISTS likes (
    like_id CHAR(36) NOT NULL PRIMARY KEY, -- UUID
    video_id CHAR(36) NOT NULL,
    user_id VARCHAR(50) NOT NULL, -- Updated to match users table
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(video_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Shares Table
CREATE TABLE IF NOT EXISTS shares (
    share_id CHAR(36) NOT NULL PRIMARY KEY, -- UUID
    video_id CHAR(36) NOT NULL,
    user_id VARCHAR(50) NOT NULL, -- Updated to match users table
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(video_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Insert Demo Users
INSERT INTO users (user_id, username, email, password_hash, profile_pic, is_verified, role) VALUES
('user_1', 'Pankaj96', 'moderator@example.com', 'hashedpassword1', 'https://example.com/moderator.jpg', TRUE, 'moderator'),
('user_2', 'regular_user1', 'user1@example.com', 'hashedpassword2', 'https://example.com/user1.jpg', TRUE, 'user'),
('user_3', 'regular_user2', 'user2@example.com', 'hashedpassword3', 'https://example.com/user2.jpg', FALSE, 'user'),
('user_4', 'regular_user3', 'user3@example.com', 'hashedpassword4', 'https://example.com/user3.jpg', TRUE, 'user');

-- Insert Demo Followers
INSERT INTO followers (follower_id, followee_id) VALUES
('user_2', 'user_1'), -- user_2 follows user_1
('user_3', 'user_1'), -- user_3 follows user_1
('user_4', 'user_2'); -- user_4 follows user_2

-- Insert Demo Videos
INSERT INTO videos (video_id, user_id, video_url, poster_url, title, description, status, source, moderator_comments, reupload_comments) VALUES
('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'user_1', 'https://example.com/video1.mp4', 'https://example.com/poster1.jpg', 'Demo Video 1', 'This is the first demo video.', 'approved', 'upload', NULL, NULL),
('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'user_2', 'https://example.com/video2.mp4', 'https://example.com/poster2.jpg', 'Demo Video 2', 'This is the second demo video.', 'pending', 'upload', NULL, NULL),
('cccccccc-cccc-cccc-cccc-cccccccccccc', 'user_3', 'https://example.com/video3.mp4', 'https://example.com/poster3.jpg', 'Demo Video 3', 'This is the third demo video.', 'rejected', 'upload', 'This video violates community guidelines.', 'I have removed the violating content. Please review again.');

-- Insert Demo Comments
INSERT INTO comments (comment_id, video_id, user_id, comment_text) VALUES
('dddddddd-dddd-dddd-dddd-dddddddddddd', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'user_2', 'Great video!'),
('eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'user_3', 'Nice work!');

-- Insert Demo Likes
INSERT INTO likes (like_id, video_id, user_id) VALUES
('ffffffff-ffff-ffff-ffff-ffffffffffff', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'user_1'),
('gggggggg-gggg-gggg-gggg-gggggggggggg', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'user_2');

-- Insert Demo Shares
INSERT INTO shares (share_id, video_id, user_id) VALUES
('hhhhhhhh-hhhh-hhhh-hhhh-hhhhhhhhhhhh', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'user_3');