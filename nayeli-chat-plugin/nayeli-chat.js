jQuery(document).ready(function($) {
    var userInput = $('#nayeliUserInput');
    var chatMessages = $('#nayeliChatMessages');
    var sendButton = $('#nayeliSendButton');
    var chatLauncher = $('#nayeliChatLauncher');
    var chatInterface = $('#nayeliChatInterface');
    var closeButton = $('#nayeliCloseButton');

    var isEmbedded = !chatLauncher.length; // If chatLauncher doesn't exist, it's embedded

    function sendMessage() {
        var userText = userInput.val();
        chatMessages.append('<div class="user-message">' + userText + '</div>');

        // AJAX call to your WordPress endpoint
        $.ajax({
            url: '/wp-json/nayeli/v1/chat/',
            method: 'POST',
            data: {
                'message': userText
            },
            success: function(response) {
                // Check if the response contains the 'message' key
                if (response && response.message) {
                    chatMessages.append('<div class="ai-message">' + response.message + '</div>');
                } else {
                    chatMessages.append('<div class="error-message">Received unexpected response format.</div>');
                }
            },
            error: function() {
                chatMessages.append('<div class="error-message">Sorry, there was an error processing your request.</div>');
            }
        });

        userInput.val(''); // clear the input
    }

    sendButton.on('click', function() {
        sendMessage();
    });

    userInput.on('keypress', function(e) {
        if (e.which == 13) { // Enter key
            sendMessage();
            return false;  // Prevents the default action on keypress
        }
    });

    chatLauncher.on('click', function(event) {
        event.preventDefault();  // Prevent the default link behavior
        // Toggle the chat interface's visibility
        if (chatInterface.css('display') === 'none') {
            chatInterface.css('display', 'block');
        } else {
            chatInterface.css('display', 'none');
        }
    });

    if (!isEmbedded) {
        chatLauncher.on('click', function(event) {
            event.preventDefault();  // Prevent the default link behavior
            // Toggle the chat interface's visibility
            if (chatInterface.css('display') === 'none') {
                chatInterface.css('display', 'block');
            } else {
                chatInterface.css('display', 'none');
            }
        });

        closeButton.on('click', function() {
            var userConfirm = confirm("Do you wish to disconnect from the chat?");
            if (userConfirm) {
                chatInterface.css('display', 'none');
                chatMessages.empty();  // This line clears the chat messages
            }
        });
    }
});
