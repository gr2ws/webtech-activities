<!DOCTYPE html>
<?php
session_start();

if (isset($_SESSION['user'])) { // any redirects from other pages logs out user
    session_unset();
    session_destroy();
}

$remUserName = $remPass = "";
$rem = false;

if (isset($_COOKIE['user_data'])) { // check if user_data cookie exists
    $userData = json_decode($_COOKIE['user_data'], true);
    $remUserName = $userData['user'] ?? ""; // empty string if not set/null
    $remPass = $userData['pass'] ?? "";
    $rem = $userData['rem'] ?? false;
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Log In</title>
</head>

<body>
    <?php
    $nameErr = $passErr = "";
    $isValid = true;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $isValid = true;

        if (empty($_POST['user_name'])) {
            $nameErr = "* Your user name is required.";
            $isValid = false;
        } else {
            $userName = $_POST['user_name'];
        }

        if (empty($_POST['pass'])) {
            $passErr = "* Your password is required.";
            $isValid = false;
        } else {
            $password = $_POST['pass'];
        }
    }
    ?>

    <div class="form_cont">
        <h1>Login</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <label for="user_name">Username: </label>
            <span class="req"><?php echo $nameErr; ?></span>
            <input type="text" name="user_name" id="user_name" value="<?php echo $remUserName ?>">

            <br>

            <label for="password">Password: </label>
            <span class="req"><?php echo $passErr; ?></span>
            <input type="password" name="pass" id="pass" value="<?php echo $remPass ?>">

            <br>

            <div class="button_row">
                <div>
                    <input type="checkbox" name="rem" id="rem" <?php if ($rem) echo 'checked'; ?>>
                    <label for="password" id="pass_label">Remember Me</label>
                </div>
                <input type="submit" value="Log In">
            </div>
        </form>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValid) {
        if (isset($_POST['rem'])) {
            $userData = json_encode([
                'user' => $userName,
                'pass' => $password,
                'rem' => true
            ]);
            setcookie('user_data', $userData, time() + 86400 * 30);
        } else {
            setcookie('user_data', "", time() - 3600);
        }

        $_SESSION['user'] = $userName;
        $_SESSION['pass'] = $password;

        header("Location: Form.php"); // redirect to form page
        exit();
    }
    ?>
</body>

</html>