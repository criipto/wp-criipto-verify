jQuery(function ($) {
    var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

    // Listen to message from child window and send the user to the desired target URL. 
    // For this demo, we stay on the home page, but you could certainly add some more
    // refined logic for taking the user to a better place.
    eventer(messageEvent, function (e) {
        var origin =
            document.location.origin ||
            document.location.protocol + "//" + document.location.host;

        if (e && e.data && e.origin === origin && e.data.userLoggedIn) {
            location.reload();
        }
    }, false);
    
    $('#criipto-verify-signout').click(function () {
        window.location = 'http://localhost/test?signout=true';
    });
})