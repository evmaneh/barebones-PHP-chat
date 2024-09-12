<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo "All fields are required.";
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo "Invalid username or password.";
        exit;
    }

    // Load users from JSON file
    $users_file = 'secret/logs/users.json';
    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true);
    } else {
        echo "User file not found.";
        exit;
    }

    // Check for user
    if (isset($users[$username]) && password_verify($password, $users[$username]['password'])) {
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    }

    echo "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            background-color: #2b1b17;
            color: #e6b8b7;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        form {
            background-color: #4d0a0a;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        
        h2 {
            color: #ffb3b3;
            text-align: center;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        input[type="text"], input[type="password"] {
            width: 90%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #b33a3a;
            border-radius: 4px;
            background-color: #330d0d;
            color: #e6b8b7;
        }
        
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #b33a3a;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }
        
        input[type="submit"]:hover {
            background-color: #992d2d;
        }
        
        a {
            color: #ffb3b3;
            text-decoration: none;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        p {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <form method="POST" action="login.php">
        <h2>Login</h2>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>
        <input type="submit" value="Login">
        <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
    </form>
</body>
</html>
