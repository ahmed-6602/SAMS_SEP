$(document).ready(function() {
    $('#studentLoginForm').submit(function(e) {
        e.preventDefault();
        const email = $('#txtUsername').val();
        const password = $('#txtPassword').val();

        $.ajax({
            url: '/attendanceapp/ajaxHandler/StudentAjax.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'verifyUser',
                user_name: email,  // ✅ fixed key
                password: password
            },
            success: function(response) {
                if (response.status === 'ALL OK') {
                    window.location.href = '/attendanceapp/StudentDashBoard.php';
                } else {
                    $('#diverror').show();
                    $('#errormessage').text(response.status);
                }
            },
            error: function() {
                $('#diverror').show();
                $('#errormessage').text('Server error. Please try again.');
            }
        });
    });
});
