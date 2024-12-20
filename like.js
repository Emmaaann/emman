document.addEventListener("DOMContentLoaded", () => {
    const likeButtons = document.querySelectorAll(".like-button");

    likeButtons.forEach(button => {
        button.addEventListener("click", async () => {
            const postId = button.dataset.postId;
            const likeCountSpan = button.querySelector(".like-count");
            const isLiked = button.classList.contains("liked"); // Check current state

            try {
                // Send request to server
                const response = await fetch("/jegrandia/like_post.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ post_id: postId })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Update like count and button state
                    likeCountSpan.textContent = result.new_likes;
                    button.classList.toggle("liked", result.action === "liked");

                    // Provide inline feedback
                    button.setAttribute("aria-label", result.action === "liked" ? "Unlike this post" : "Like this post");
                } else {
                    // Display error message
                    displayMessage(result.message || "An error occurred.", "error");
                }
            } catch (error) {
                console.error("Error toggling like:", error);
                displayMessage("Failed to connect to the server. Please try again later.", "error");
            }
        });
    });

    /**
     * Displays a temporary inline message
     * @param {string} message - The message text.
     * @param {string} type - The type of message ("success" or "error").
     */
    function displayMessage(message, type) {
        const messageContainer = document.createElement("div");
        messageContainer.textContent = message;
        messageContainer.className = `message ${type}`;
        document.body.appendChild(messageContainer);

        setTimeout(() => {
            messageContainer.remove();
        }, 3000);
    }
});
