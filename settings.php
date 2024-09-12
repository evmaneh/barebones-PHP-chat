<?php
session_start(); // Start the session to access user data

if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

$username = $_SESSION['username'];
$users_file = 'secret/logs/users.json';

// Load users from the JSON file
if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true);
} else {
    $users = [];
}

// Check if the user exists
if (!isset($users[$username])) {
    echo "User not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update description
    if (isset($_POST['description'])) {
        $users[$username]['description'] = trim($_POST['description']);
    }

    // Update password
    if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
        $new_password = trim($_POST['new_password']);
        $users[$username]['password'] = password_hash($new_password, PASSWORD_DEFAULT);
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_name = $_FILES['image']['name'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $allowed_exts = ['jpg', 'jpeg', 'png']; // Allowed image extensions

        if (in_array(strtolower($image_ext), $allowed_exts)) {
            $image_path = "public/images/{$username}.{$image_ext}";

            // Move uploaded file to the desired directory
            if (move_uploaded_file($image_tmp_name, $image_path)) {
                // Update image path in the JSON file
                $users[$username]['image'] = $image_path;
            } else {
                echo "Failed to upload image.";
            }
        } else {
            echo "Invalid image type.";
        }
    }

    // Save updated user data back to the JSON file
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));

    echo "Settings updated successfully!";
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

        input[type="text"], input[type="password"], textarea {
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
    <form method="POST" action="" enctype="multipart/form-data">
        <h2>Settings</h2>
        <label for="description">Description:</label>
        <textarea name="description" id="description" rows="4"><?= htmlspecialchars($users[$username]['description']) ?></textarea><br>
        
        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password"><br>

        <label for="image">Profile Image (JPG, JPEG, PNG):</label>
        <input type="file" name="image" id="image"><br>

        <input type="submit" value="Update Settings">
        <p><a href="profile.php">Back to Profile</a></p>
    </form>
</body>
</html>
