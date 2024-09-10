<?php
if (file_exists('secret/logs/chatlogs.txt')) {
    $chat_logs = file('secret/logs/chatlogs.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($chat_logs as $log) {
        echo "<p>" . $log . "</p>";
    }
} else {
    echo "<p>Start a Conversation!</p>";
}
?>
