<?php
/*
Plugin Name: Nayeli Chat Plugin
Description: A simple chat plugin to interact with Nayeli on bradastra.com.
Version: 1.0
Author: Brad Zacharias
*/

// ChatBot Class Definition
class ChatBot
{
    private $authorization;
    private $endpoint;

    public function __construct($api_key)
    {
        $this->authorization = $api_key;
        $this->endpoint = 'https://api.openai.com/v1/chat/completions';
    }

public function sendMessage(string $message): string
{
    $nayeli_context = "You are Nayeli, a vivacious and warm-hearted AI chatbot on bradastra.com. Infused with the spirit of a bright summer's day, you exude cheerfulness, optimism, and kindness. You have a penchant for clear, concise answers, but always with a touch of friendliness and warmth. Your primary goal is to assist and uplift those who interact with you, ensuring they leave with a smile.";
    $full_prompt = $nayeli_context . " When asked '" . $message . "', how would she respond?";

    $data = [
        'messages' => [
            ['role' => 'system', 'content' => $full_prompt],
            ['role' => 'user', 'content' => $message]
        ],
        'model' => 'gpt-3.5-turbo'
    ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->authorization,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Error sending the message: ' . $error);
        }

        curl_close($ch);

        $arrResult = json_decode($response, true);
        return $arrResult["choices"][0]["message"]["content"];
    }
}

function nayeli_enqueue_scripts() {
    wp_enqueue_style("nayeli-chat-css", plugin_dir_url(__FILE__) . "nayeli-chat.css");
    wp_enqueue_script("nayeli-chat-js", plugin_dir_url(__FILE__) . "nayeli-chat.js", array("jquery"), "1.0", true);
}

add_action("wp_enqueue_scripts", "nayeli_enqueue_scripts");

function nayeli_chat_shortcode($atts, $content = null) {
    $attributes = shortcode_atts(
        array(
            'mode' => 'popup', // default mode is popup
        ),
        $atts
    );

    $chatHTML = '<div id="nayeliChatMessages"></div>
                 <input type="text" id="nayeliUserInput" placeholder="Type your message...">
                 <button id="nayeliSendButton">Send</button>';

    if($attributes['mode'] === 'embedded') {
        // If mode is embedded, return the chat directly
        return $chatHTML;
    } else {
        // If mode is popup (or any other value), return the launcher and chat interface
        return '<a href="#" id="nayeliChatLauncher">' . $content . '</a>
                <div id="nayeliChatInterface" style="display: none;">
                    <div id="nayeliCloseButton">Close Chat</div>' .
                    $chatHTML .
                '</div>';
    }
}

add_shortcode("nayeli-chat", "nayeli_chat_shortcode");

// REST API endpoint to handle chat with OpenAI
function nayeli_openai_chat(WP_REST_Request $request) {
    $user_message = $request->get_param('message');
    $api_key = get_option('openai_api_key', '');

    $ChatBot = new ChatBot($api_key);
    $responseMessage = $ChatBot->sendMessage($user_message);

    return array('message' => $responseMessage);
}

// Register REST API route
add_action('rest_api_init', function() {
    register_rest_route('nayeli/v1', '/chat/', array(
        'methods' => 'POST',
        'callback' => 'nayeli_openai_chat',
    ));
});

// Add a new menu option for the plugin settings
function nayeli_settings_menu() {
    add_options_page('Nayeli Chat Plugin Settings', 'Nayeli Chat', 'manage_options', 'nayeli-chat-plugin', 'nayeli_display_settings');
}

add_action('admin_menu', 'nayeli_settings_menu');

// Display the settings page
function nayeli_display_settings() {
    ?>
    <div class="wrap">
        <h2>Nayeli Chat Plugin Settings</h2>
        <form method="post" action="options.php">
            <?php
                settings_fields('nayeli_options');
                do_settings_sections('nayeli-chat-plugin');
                submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Initialize plugin settings
function nayeli_settings_init() {
    register_setting('nayeli_options', 'openai_api_key');
    add_settings_section('nayeli_main', 'Main Settings', null, 'nayeli-chat-plugin');
    add_settings_field('openai_api_key', 'OpenAI API Key', 'nayeli_display_api_key', 'nayeli-chat-plugin', 'nayeli_main');
}

add_action('admin_init', 'nayeli_settings_init');

// Display API key field
function nayeli_display_api_key() {
    $api_key = esc_attr(get_option('openai_api_key'));
    echo "<input type='text' name='openai_api_key' value='$api_key' size='50' />";
}

?>
