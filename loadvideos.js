let page = 1; // Track the current page of videos
let loading = false; // Prevent multiple AJAX requests

// Function to load videos
function loadVideos() {
    if (loading) return; // Prevent multiple requests
    loading = true;

    // Show loading message
    document.getElementById('loading').style.display = 'block';

    // Fetch videos from the server
    fetch(`load_public_videos.php?page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                // Append videos to the container
                const videoContainer = document.getElementById('video-container');
                data.forEach(video => {
                    const videoCard = `
                        <div class="video-card">
                            <video controls>
                                <source src="${video.video_url}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="video-info">
                                <h3>${video.title}</h3>
                                <p>${video.description}</p>
                                <p>Uploaded on: ${video.created_at}</p>
                                <div class="video-stats">
                                    <span>👁️ ${video.views || 0} Views</span>
                                    <span>❤️ ${video.likes_count || 0} Likes</span>
                                    <span>💬 ${video.comments_count || 0} Comments</span>
                                    <span>🔗 ${video.shares_count || 0} Shares</span>
                                </div>
                            </div>
                        </div>
                    `;
                    videoContainer.insertAdjacentHTML('beforeend', videoCard);
                });

                // Increment page for the next load
                page++;
                loading = false;
            } else {
                // No more videos to load
                document.getElementById('loading').textContent = 'No more videos to load.';
            }
            // Hide loading message
            document.getElementById('loading').style.display = 'none';
        })
        .catch(error => {
            console.error('Error loading videos:', error);
            document.getElementById('loading').textContent = 'Failed to load videos.';
        });
}

// Load videos when the page loads
window.onload = loadVideos;

// Load more videos when the user scrolls to the bottom
window.onscroll = function () {
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 100) {
        loadVideos();
    }
};