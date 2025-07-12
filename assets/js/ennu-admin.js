jQuery(document).ready(function($) {
    $('#ennu-clear-data').on('click', function() {
        if (confirm(ennu_admin.confirm_msg)) {
            var userId = $(this).data('user-id');
            $.ajax({
                url: ennu_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'ennu_clear_user_data',
                    user_id: userId,
                    nonce: ennu_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('AJAX error - please try again.');
                }
            });
        }
    });
}); 