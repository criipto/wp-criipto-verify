jQuery(function($) {
    $('.criipto-toggle-secret').click(function() {
        if ($('#criipto-client-secret').get(0).type === 'password') {
            $(this).text('Hide');
            $('#criipto-client-secret').get(0).type = 'text';
        } else {
            $(this).text('Show');
            $('#criipto-client-secret').get(0).type = 'password';
        }
    });
})