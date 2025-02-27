$(document).ready(function () {
    $('#deleteForm').on("submit", function (e) {
        e.preventDefault();
        const email = $("#email").val();
        $("#spinner").show();

        $.ajax({
            url: "/request-delete.php",
            method: "POST",
            data: { email }
        }).done(function (data) {
            if (data.message) {
                console.log(data);
                window.location.href = 'confirmation-delete.html';
            }
        })
            .fail(function () {
                console.error('Error:', error);
                alert('Ocorreu um erro. Por favor, tente novamente.');
            })
            .always(function () {
                $("#spinner").hide();
            });
        return false;
    })
})
