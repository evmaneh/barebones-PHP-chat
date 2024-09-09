<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$banned_users = file_exists('secret/logs/banned_users.txt') ? file('secret/logs/banned_users.txt', FILE_IGNORE_NEW_LINES) : [];
$is_banned = in_array($username, $banned_users);

function update_user_count() {
    $session_file = 'secret/logs/session_users.txt';
    $session_expiration = 300;

    $sessions = @file_get_contents($session_file);
    $sessions = $sessions !== false ? @unserialize($sessions) : [];

    if ($sessions === false) {
        $sessions = [];
    }

    $sessions[session_id()] = time();

    foreach ($sessions as $session_id => $last_active) {
        if ($last_active < time() - $session_expiration) {
            unset($sessions[$session_id]);
        }
    }

    file_put_contents($session_file, serialize($sessions));

    return count($sessions);
}

$online_users = update_user_count();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$is_banned) {
    $botUsername = 'Bot'; // Change bot username here
    $timestamp = date('g:i A');

    if (isset($_POST['message']) && !empty(trim($_POST['message']))) {
        $message = trim($_POST['message']);
        
        if (strpos($message, '!feedback') === 0) {
            $feedback = trim(str_replace('!feedback', '', $message));
            if (!empty($feedback)) {
                $feedback_entry = "$timestamp - $username: $feedback" . PHP_EOL;
                file_put_contents('secret/logs/feedback.txt', $feedback_entry, FILE_APPEND);
            }
        } elseif ($message === '!clear' && $username === 'admin') {
            $chatLogsFile = 'secret/logs/chatlogs.txt';
            $uploadsDir = 'secret/logs/uploads/';
            
            if (file_exists($chatLogsFile)) {
                file_put_contents($chatLogsFile, '');
            }

            foreach (glob($uploadsDir . '*') as $file) {
                unlink($file);
            }

            $log_entry = "$botUsername: Chat cleared by $username" . PHP_EOL;
            file_put_contents($chatLogsFile, $log_entry, FILE_APPEND);
        } elseif (strpos($message, '!botchat') === 0 && $username === 'admin') {
            $botMessage = trim(str_replace('!botchat', '', $message));
            
            if (!empty($botMessage)) {
                $log_entry = "$timestamp - $botUsername: $botMessage" . PHP_EOL;
                file_put_contents('secret/logs/chatlogs.txt', $log_entry, FILE_APPEND);
            }
        } elseif ($message === '!bot') {
            $botResponse = "$timestamp - $botUsername: Hi, I'm $botUsername, the website bot!" . PHP_EOL;
            file_put_contents('secret/logs/chatlogs.txt', $botResponse, FILE_APPEND);
        } else {
            $log_entry = "$timestamp - $username: $message" . PHP_EOL;
            file_put_contents('secret/logs/chatlogs.txt', $log_entry, FILE_APPEND);
        }
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'secret/logs/uploads/';
        $upload_file = $upload_dir . basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            if ($_FILES['image']['size'] <= 5000000) {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($imageFileType, $allowed_extensions)) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                        $log_entry = "$timestamp - $username: <img src='$upload_file' alt='Image' style='width:auto; max-height:100px;'>" . PHP_EOL;
                        file_put_contents('secret/logs/chatlogs.txt', $log_entry, FILE_APPEND);
                    } else {
                        echo "Unknown Error.";
                    }
                } else {
                    echo "JPG, JPEG, PNG & GIF files are allowed.";
                }
            } else {
                echo "Large file.";
            }
        } else {
            echo "File is not an image.";
        }
    }

    header('Location: chat.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            messageInput.focus();
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === '/') {
                event.preventDefault();
                messageInput.focus();
            }
        });

        function checkBanStatus() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'check_ban_status.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    if (xhr.responseText === 'banned') {
                        window.location.reload();
                    }
                }
            };
            xhr.send();
        }

        setInterval(checkBanStatus, 5000);
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .chat-log {
            height: 300px;
            overflow-y: scroll;
        }
        .chat-input {
            <?php if ($is_banned) echo 'display: none;'; ?>
        }
    </style>
</head>
<body>
    <a href="logout.php">Logout</a>

        <div class="chat-log" id="chatLog">
        </div>

        <?php if ($is_banned): ?>
            <p>You have been banned from sending messages.</p>
        <?php else: ?>
            <form class="chat-input" method="POST" action="chat.php" enctype="multipart/form-data">
                <input type="file" name="image" accept="image/*">
                <input type="text" name="message" id="messageInput" placeholder="Type your message..." autocomplete="off">
            </form>
        <?php endif; ?>

    <script>
        var userIsScrolling = false;
        var chatLog = document.getElementById('chatLog');

        function loadChatLog() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'load_chat.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    chatLog.innerHTML = xhr.responseText;

                    if (!userIsScrolling) {
                        chatLog.scrollTop = chatLog.scrollHeight;
                    }
                }
            };
            xhr.send();
        }

        chatLog.addEventListener('scroll', function() {
            var scrollTop = chatLog.scrollTop;
            var scrollHeight = chatLog.scrollHeight;
            var clientHeight = chatLog.clientHeight;

            if (scrollTop + clientHeight < scrollHeight) {
                userIsScrolling = true;
            } else {
                userIsScrolling = false;
            }
        });

        setInterval(loadChatLog, 100);
    </script>
</body>
</html>
