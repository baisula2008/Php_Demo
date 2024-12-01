<?php
session_start();

function checkCredentials($username, $password) {
    $file = fopen('password.txt', 'r');
    while (($line = fgets($file)) !== false) {
        list($fileUsername, $filePassword, $favoritePet) = explode('*', trim($line));
        if ($username === $fileUsername && $password === $filePassword) {
            fclose($file);
            return $favoritePet;
        }
    }
    fclose($file);
    return false;
}

$requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($requestMethod === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $favoritePet = checkCredentials($username, $password);
    if ($favoritePet) {
        $_SESSION['username'] = $username;
        $_SESSION['favoritePet'] = $favoritePet;

        // Log user access
        $logFile = fopen('log.csv', 'a');
        fputcsv($logFile, [$username, date('Y-m-d'), date('H:i')]);
        fclose($logFile);

        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $favoritePet = $_SESSION['favoritePet'];

    // Simplified URLs for testing
    $dogUrl = 'https://placedog.net/500';
    $catUrl = 'https://placekitten.com/500';
    $imageUrl = $favoritePet === 'dog' ? $dogUrl : $catUrl;

    // Debugging output
    echo "Username: " . $username . "<br>";
    echo "Favorite Pet: " . $favoritePet . "<br>";
    echo "Image URL: " . $imageUrl . "<br>";

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <h1>Welcome, $username!</h1>
    <p>Your favorite pet is a $favoritePet.</p>
    <a href="$imageUrl" target="_blank">
        <img src="$imageUrl" alt="$favoritePet">
    </a>
    <a href="logout.php">Logout</a>
</body>
</html>
HTML;
} else {
    $error = isset($error) ? $error : '';
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form action="index.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>
        <p style="color:red;">$error</p>
    </div>
</body>
</html>
HTML;
}
