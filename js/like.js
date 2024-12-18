document.addEventListener("DOMContentLoaded", () => {
    const likeButtons = document.querySelectorAll(".like-button");

    likeButtons.forEach(button => {
        button.addEventListener("click", async () => {
            const postId = button.dataset.postId;

            try {
                const response = await fetch("/bscs4a/php/like_post.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ post_id: postId })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Update like count
                    const likeCountSpan = button.querySelector(".like-count");
                    likeCountSpan.textContent = result.new_likes;
                    alert("Post liked successfully!");
                } else {
                    alert(result.message || "An error occurred while liking the post.");
                }
            } catch (error) {
                console.error("Error liking the post:", error);
                alert("Failed to connect to the server.");
            }
        });
    });
});
