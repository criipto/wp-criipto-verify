jQuery(function($) {
    $('.criipto-verify-toggle-secret').click(function() {
        if ($('#criipto-verify-client-secret').get(0).type === 'password') {
            $(this).text('Hide');
            $('#criipto-verify-client-secret').get(0).type = 'text';
        } else {
            $(this).text('Show');
            $('#criipto-verify-client-secret').get(0).type = 'password';
        }
    });
})