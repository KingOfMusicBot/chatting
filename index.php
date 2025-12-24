<?php

// Heroku automatically vendor folder create karega
require 'vendor/autoload.php';

// --- CONFIGURATION (Environment Variables se lenge) ---
// Keys direct code mai nahi daalni hain, Heroku settings mai dalenge
$telegramToken = getenv('TELEGRAM_TOKEN');
$groqApiKey = getenv('GROQ_API_KEY');
$mongoUri = getenv('MONGO_URI'); 

// --- MONGODB CONNECTION ---
try {
    $client = new MongoDB\Client($mongoUri);
    // Database: 'telegram_bot', Collection: 'chats'
    $collection = $client->telegram_bot->chats; 
} catch (Exception $e) {
    // Agar DB connect na ho to error log karo (Heroku logs mai dikhega)
    error_log("MongoDB Connection Error: " . $e->getMessage());
    exit;
}

// --- TELEGRAM WEBHOOK HANDLING ---
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!isset($update["message"])) {
    exit; 
}

$chatId = $update["message"]["chat"]["id"];
$userMessage = $update["message"]["text"] ?? ""; 

if (empty($userMessage)) {
    exit;
}

// 1. TYPING STATUS (User ko lage bot soch raha hai)
file_get_contents("https://api.telegram.org/bot$telegramToken/sendChatAction?chat_id=$chatId&action=typing");

// 2. FETCH HISTORY (Context ke liye pichle 10 messages)
// Hum history read karte hain taaki bot purani baat yaad rakh sake
$historyCursor = $collection->find(
    ['chat_id' => $chatId],
    [
        'limit' => 10,
        'sort' => ['timestamp' => -1] 
    ]
);

$messages = [];

// System Prompt: Bot ki personality
$messages[] = [
    "role" => "system",
    "content" => "You are a helpful and friendly AI assistant who speaks mostly in Hinglish. Keep answers concise."
];

// History formatting
$historyArray = iterator_to_array($historyCursor);
$historyArray = array_reverse($historyArray); 

foreach ($historyArray as $msg) {
    $messages[] = ["role" => $msg['role'], "content" => $msg['content']];
}

// Add current user message
$messages[] = ["role" => "user", "content" => $userMessage];

// 3. CALL GROQ API
$aiReply = callGroqApi($messages, $groqApiKey);

// 4. SAVE DATA TO MONGODB
try {
    // Save User Msg
    $collection->insertOne([
        'chat_id' => $chatId,
        'role' => 'user',
        'content' => $userMessage,
        'timestamp' => new MongoDB\BSON\UTCDateTime()
    ]);

    // Save Bot Reply
    $collection->insertOne([
        'chat_id' => $chatId,
        'role' => 'assistant',
        'content' => $aiReply,
        'timestamp' => new MongoDB\BSON\UTCDateTime()
    ]);
} catch (Exception $e) {
    error_log("DB Save Error: " . $e->getMessage());
}

// 5. SEND REPLY TO TELEGRAM
sendTelegramMessage($chatId, $aiReply, $telegramToken);


// --- FUNCTIONS ---

function callGroqApi($messages, $apiKey) {
    $url = "https://api.groq.com/openai/v1/chat/completions";
    
    $data = [
        "model" => "llama3-8b-8192", // Fast and efficient model
        "messages" => $messages,
        "temperature" => 0.7
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        return "Error connecting to AI.";
    }
    
    curl_close($ch);
    $result = json_decode($response, true);
    
    return $result['choices'][0]['message']['content'] ?? "Sorry, AI is currently busy.";
}

function sendTelegramMessage($chatId, $message, $token) {
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
?>
