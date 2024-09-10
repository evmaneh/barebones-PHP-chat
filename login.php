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

    $users = file_get_contents('secret/logs/users.txt');
    $users_array = explode(PHP_EOL, $users);

    foreach ($users_array as $user) {
        $user_details = explode(":", $user);
        if ($user_details[0] === $username && password_verify($password, $user_details[1])) {
            $_SESSION['username'] = $username;
            header('Location: index.php');
            exit;
        }
    }
    echo "Invalid username or password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
