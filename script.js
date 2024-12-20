document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('ratingForm').addEventListener('submit', function(event) {
        const comment = document.getElementById('comment').value.trim();

        if (!comment) {
            alert('Please add a comment before submitting.');
            event.preventDefault();
        }
    });
});
