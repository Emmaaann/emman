form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    formData.append("ajax", "1"); // Indicate this is an AJAX request

    fetch("post.php?id=<?= $post_id ?>", {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.status === "success") {
                // Handle success
            } else {
                alert(data.message); // Display error message
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred. Please try again.");
        });
});
