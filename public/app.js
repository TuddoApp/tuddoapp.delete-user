document.getElementById('deleteForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const spinner = document.getElementById('spinner');
    spinner.style.display = 'block';

    fetch('http://localhost:3000/request-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                window.location.href = '/confirmation-delete';
            } else {
                throw new Error('Erro ao solicitar exclusão');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocorreu um erro. Por favor, tente novamente.');
        })
        .finally(() => {
            spinner.style.display = 'none';
        });
});
