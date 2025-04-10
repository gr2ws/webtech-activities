<!DOCTYPE html>
<?php
session_start();

$lName = $fName = $course = $yrLevel = $email = $fBack = "";
$yrErr = $courseErr = $fBackErr = $nameErr = $emailErr = "";

// check if summary already exists
// occurs when user clicks "Edit Review" button in summary page
if (isset($_SESSION['summary'])) {
    $summary = json_decode($_SESSION['summary'], true);
    $lName = $summary['last_name'] ?? "";
    $fName = $summary['first_name'] ?? "";
    $course = $summary['course'] ?? "def";
    $yrLevel = $summary['year_level'] ?? "";
    $email = $summary['email'] ?? "";
    $fBack = $summary['feedback'] ?? "";
}

$isValid = true;

function validName($name)
{
    return !preg_match('~[^a-zA-Z\s\-]+~', $name);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isValid = true;

    if (empty($_POST["yr_lvl"])) {
        $yrErr = "* Your year level is required.";
        $isValid = false;
    } else {
        $yrLevel = $_POST["yr_lvl"];
    }

    if ($_POST["course"] === "def") {
        $courseErr = "* Your course is required.";
        $isValid = false;
    } else {
        $course = $_POST["course"];
    }

    if (empty($_POST["f_back"])) {
        $fBackErr = "* Your feedback is required.";
        $isValid = false;
    } else {
        $fBack = $_POST["f_back"];
    }

    if (!empty($_POST["l_name"])) {
        if (validName($_POST["l_name"])) {
            $lName = ucfirst(strtolower($_POST["l_name"]));
        } else {
            $nameErr = "Your name must not contain numbers.";
            $isValid = false;
        }
    }

    if (!empty($_POST["f_name"])) {
        if (validName($_POST["f_name"])) {
            $fName = ucwords(strtolower($_POST["f_name"]));
        } else {
            $nameErr = "Your name must not contain numbers.";
            $isValid = false;
        }
    }

    if (!empty($_POST["email"])) {
        if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $email = $_POST["email"];
        } else {
            $emailErr = "Invalid e-mail address.";
            $isValid = false;
        }
    }

    if ($isValid) {
        $_SESSION['summary'] = json_encode([
            'username' => $_SESSION['user'], // add username to summary
            'last_name' => $lName,
            'first_name' => $fName,
            'course' => $course,
            'year_level' => $yrLevel,
            'email' => $email,
            'feedback' => $fBack
        ]);

        header("Location: Summary.php");
        exit();
    }
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Form</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="form_cont">
        <?php
        if (isset($_SESSION['user'])) {
            echo "<h1>Hello " . htmlspecialchars($_SESSION['user']) . "!</h1><br>";
        } elseif (isset($_COOKIE['user_data'])) {
            // if user has checked "remember me"
            $userData = json_decode($_COOKIE['user_data'], true);
            $_SESSION['user'] = $userData['user'];
            $_SESSION['pass'] = $userData['pass'];
            echo "<h1>Hello " . htmlspecialchars($_SESSION['user']) . "!</h1><br>";
        } else {
            header("Location: Login.php");
            exit();
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <label for="l_name">Last Name: </label>
            <span class="req"><?php echo $nameErr; ?></span>
            <input type="text" name="l_name" id="l_name" value="<?php echo htmlspecialchars($lName); ?>">

            <br>

            <label for="f_name">First Name: </label>
            <input type="text" name="f_name" id="f_name" value="<?php echo htmlspecialchars($fName); ?>">

            <br>

            <label for="email">Email: </label> <span class="req"> <?php echo $emailErr; ?></span>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">

            <br><br>

            <label for="course">Course: </label>
            <span class="req"><?php echo $courseErr; ?></span>
            <select name="course" id="course">
                <option value="def" <?php echo $course === "def" ? "selected" : ""; ?>>Choose a course...</option>
                <option value="BSCS" <?php echo $course === "BSCS" ? "selected" : ""; ?>>Computer Science</option>
                <option value="BSIT" <?php echo $course === "BSIT" ? "selected" : ""; ?>>Information Technology</option>
                <option value="BSIS" <?php echo $course === "BSIS" ? "selected" : ""; ?>>Information Systems</option>
                <option value="BLIS" <?php echo $course === "BLIS" ? "selected" : ""; ?>>Library Science</option>
            </select>

            <br>

            <label for="yr_lvl">Year Level: </label>
            <span class="req"><?php echo $yrErr; ?></span>
            <br>
            <input type="radio" name="yr_lvl" id="yr_1" value="I" <?php echo $yrLevel === "I" ? "checked" : ""; ?>>Level I</input>
            <input type="radio" name="yr_lvl" id="yr_2" value="II" <?php echo $yrLevel === "II" ? "checked" : ""; ?>>Level II</input>
            <input type="radio" name="yr_lvl" id="yr_3" value="III" <?php echo $yrLevel === "III" ? "checked" : ""; ?>>Level III</input>
            <input type="radio" name="yr_lvl" id="yr_4" value="IV" <?php echo $yrLevel === "IV" ? "checked" : ""; ?>>Level IV</input>

            <br><br>

            <label for="f_back">Tell us about your experience in CCS: </label>
            <br>
            <span class="req"><?php echo $fBackErr; ?></span>
            <textarea name="f_back" id="f_back"><?php echo htmlspecialchars($fBack); ?></textarea>

            <br>

            <div class="button_row">
                <button type="button" onclick="document.location='Login.php'">Log Out</button>
                <input type="submit" value="Proceed">
            </div>
        </form>
    </div>

</body>

</html>