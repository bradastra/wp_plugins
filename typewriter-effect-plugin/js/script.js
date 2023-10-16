jQuery(document).ready(function($) {
    function typeMessage(element, message, currentIndex) {
        if (currentIndex < message.length) {
            element.text(element.text() + message[currentIndex]);
            setTimeout(function() {
                typeMessage(element, message, currentIndex + 1);
            }, 70);
        } else {
            element.append('<span class="cursor">_</span>');
        }
    }
    
    $('.typewriter-text').each(function() {
        var element = $(this);
        var message = element.text();
        element.text('');
        typeMessage(element, message, 0);
    });
});
