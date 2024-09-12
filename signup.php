<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check for proxy headers first
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }

    if (empty($username) || empty($password)) {
        echo "All fields are required.";
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo "Invalid username.";
        exit;
    }

    // Load or create users JSON file
    $users_file = 'secret/logs/users.json';
    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true);
    } else {
        $users = [];
    }

    // Check if the username already exists
    if (isset($users[$username])) {
        echo "Username already taken.";
        exit;
    }

    // Add new user to JSON file
    $users[$username] = [
        'password' => $hashed_password,
        'image' => '', // Default empty value
        'description' => '', // Default empty value
        'rank' => 'Basic', // Default empty value
        'ip' => $ip_address
    ];

    // Save to JSON file
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));

    echo "Signup successful!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            background-color: #2b1b17; /* Dark reddish background */
            color: #e6b8b7; /* Light red text color */
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        form {
            background-color: #4d0a0a; /* Darker red background for the form */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        
        h2 {
            color: #ffb3b3; /* Lighter red color for the header */
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
            border: 1px solid #b33a3a; /* Dark red border */
            border-radius: 4px;
            background-color: #330d0d; /* Darker red input background */
            color: #e6b8b7;
        }
        
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #b33a3a; /* Dark red submit button */
            border: none;
            border-radius: 4px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }
        
        input[type="submit"]:hover {
            background-color: #992d2d; /* Slightly darker red on hover */
        }
        
        a {
            color: #ffb3b3; /* Light red link color */
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
    <form method="POST" action="">
        <h2>Sign Up</h2>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required><br>
        <input type="submit" name="signup" value="Sign Up">
        <p>Already have an account? <a href="login.php">Login here</a></p>
        <a href="privacy-policy.php">Privacy</a>
    </form>
</body>
</html>
