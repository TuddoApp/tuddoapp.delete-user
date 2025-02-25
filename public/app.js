document.getElementById('deleteForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const email = document.getElementById('email').value;

    fetch('http://localhost:3000/request-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.link) {
                alert('Confirmation link sent. Check your email.');
                window.location.href = 'page2.html';
            } else {
                throw new Error('Link not received');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
});