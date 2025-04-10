<!DOCTYPE html>
<?php
session_start();

// redirects if summary is empty
if (empty($_SESSION['summary'])) {
    header("Location: Login.php");
    exit();
}

?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Review Summary</title>
</head>

<body>
    <div class="summ_cont">
        <?php
        if (isset($_SESSION['user'])) {
            echo "<h1>Thank you " . htmlspecialchars($_SESSION['user']) . "!</h1><br>";
        } elseif (isset($_COOKIE['user_data'])) {
            // if user has checked "remember me"
            $userData = json_decode($_COOKIE['user_data'], true);
            $_SESSION['user'] = $userData['user'];
            $_SESSION['pass'] = $userData['pass'];
            echo "<h1>Hello " . htmlspecialchars($_SESSION['user']) . "!</h1><br>";
        } elseif (empty($_SESSION['user'])) {
            // if user is not logged in redirect to login page
            header("Location: Login.php");
            exit();
        }
        ?>

        <p>Here is a summary of your review...</p>

        <?php
        $summary = json_decode($_SESSION['summary'], true);

        echo "<b>Reviewed by:</b> <br>"
            . $summary['last_name'] . (!empty($summary['last_name']) ? ", " : "")
            . $summary['first_name'] . (!empty($summary['first_name']) ? " " : "")
            . "(" . $summary['course'] . ", " . $summary['year_level'] . ")"
            . (!empty($summary['email']) ? " <br>" : "") . $summary['email']
            . "<br> @" . $summary['username']
            . "<br><br><b>Review:</b><br>" . $summary['feedback'];
        ?>

        <br><br>

        <div class="button_row">
            <div>
                <button onclick="document.location='Login.php'">Log Out</button>
                <button onclick="document.location='Form.php'">Edit Review</button>
            </div>
            <button onclick="alert('Thank you for your feedback!'); document.location='Login.php'">Submit</button>
        </div>
    </div>

    <br>
</body>

</html>