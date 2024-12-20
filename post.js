document.addEventListener("DOMContentLoaded", () => {
    const likeButton = document.querySelector("#like-button");
    const likeCount = document.querySelector("#like-count");
    const commentForm = document.querySelector("#comment-form");
    const commentTextarea = document.querySelector("#comment-textarea");
    const commentError = document.querySelector("#comment-error");

    // Like/Unlike functionality
    if (likeButton) {
        likeButton.addEventListener("click", () => {
            const postId = likeButton.dataset.postId;

            fetch("like_post.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ postId }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        likeCount.textContent = data.newLikeCount;
                        likeButton.textContent = data.liked ? "Unlike" : "Like";
                    } else {
                        alert("Action failed. Please try again.");
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                });
        });
    }

    // Comment form validation
    if (commentForm && commentTextarea) {
        commentForm.addEventListener("submit", (event) => {
            if (commentTextarea.value.trim() === "") {
                event.preventDefault();
                commentError.textContent = "Comment cannot be empty.";
                commentError.style.display = "block";
            } else {
                commentError.style.display = "none";
            }
        });
    }
});
