<?php
# input variables
$fName = $lName = $bDay = $position = '';
$empID = $salary = 0;

# error variables
$lnErr = $fnErr = $bdErr = $psErr = $slErr = $emptyErr = $dbMsg = '';

/**
 * validates a name to ensure it contains only letters, spaces, and hyphens
 * @param string $name name to validate
 * @return bool true if name is valid, false otherwise
 */
function validateName($name)
{
    return !preg_match('~[^a-zA-Z\s\-]+~', $name);
}

/**
 * processes form data and validates all fields
 * @param array $postData the POST data array from the form
 * @return array array with validation result and errors
 */
function validateFormData($postData)
{
    $errors = [
        'fnErr' => '',
        'lnErr' => '',
        'psErr' => ''
    ];
    $isValid = true;

    # check for empty fields, invalidate form if any
    foreach ($postData as $key => $value) {
        if (empty($value) && $key != 'add_record') {
            $isValid = false;
            return ['isValid' => false, 'errors' => $errors, 'message' => 'One or more fields are empty.'];
        }
    }

    # validate first name
    if (!validateName($postData['f_name'])) {
        $errors['fnErr'] = "Your first name must not contain numbers.";
        $isValid = false;
    } elseif (strlen($postData['f_name']) > 25) {
        $errors['fnErr'] = "Your first name is too long.";
        $isValid = false;
    }

    # validate last name
    if (!validateName($postData['l_name'])) {
        $errors['lnErr'] = "Your last name must not contain numbers.";
        $isValid = false;
    } elseif (strlen($postData['l_name']) > 15) {
        $errors['lnErr'] = "Your last name is too long.";
        $isValid = false;
    }

    # validate position length
    if (strlen($postData['position']) > 15) {
        $errors['psErr'] = "Your position is too long.";
        $isValid = false;
    }

    return ['isValid' => $isValid, 'errors' => $errors];
}

?>

<!DOCTYPE html>
<html lang="en">

<meta charset="UTF-8">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Employee Database | Create</title>
</head>

<body>
    <!--reused header-->
    <div class="header">
        <?php
        # attempt connection to database to see if it exists
        $dbExists = false;

        try {
            $conn = new mysqli('127.0.0.1:3306', 'root', '', 'dbE6');

            if ($conn->connect_error) $dbMsg = $conn->connect_error;
            else $dbExists = true;

            $conn->close();
        } catch (mysqli_sql_exception $e) {
            $dbMsg = $e->getMessage();
        }

        # create database
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_db'])) {
            try {
                $conn = new mysqli('127.0.0.1:3306', 'root', '');

                if ($conn->connect_error) $dbMsg = $conn->connect_error;
                else {
                    $sql = "CREATE DATABASE IF NOT EXISTS dbE6";
                    if ($conn->query($sql) === TRUE) {
                        $dbMsg = "Database created successfully!";
                        $dbExists = true;
                    } else {
                        $dbMsg = $conn->error;
                    }
                }
                $conn->close();
            } catch (mysqli_sql_exception $e) {
                $dbMsg = $e->getMessage();
            }
        }

        # drop database
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drop_db'])) {
            try {
                $conn = new mysqli('127.0.0.1:3306', 'root', '', 'dbE6');

                if ($conn->connect_error) $dbMsg = $conn->connect_error;
                else {
                    $sql = "DROP DATABASE IF EXISTS dbE6";
                    if ($conn->query($sql) === TRUE) {
                        $dbMsg = "Database dropped successfully!";
                        $dbExists = false;
                    } else {
                        $dbMsg = $conn->error;
                    }
                }

                $conn->close();
            } catch (mysqli_sql_exception $e) {
                $dbMsg = $e->getMessage();
            }
        }

        # check if table exists
        $tableExists = false;
        try {
            $conn = new mysqli('127.0.0.1:3306', 'root', '', 'dbE6');
            if ($conn->connect_error) $dbMsg = $conn->connect_error;
            else {
                $result = $conn->query("SHOW TABLES LIKE 'Employee'");
                if ($result->num_rows > 0) $tableExists = true;
            }
            $conn->close();
        } catch (mysqli_sql_exception $e) {
            $dbMsg = $e->getMessage();
        }

        # create table
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_table'])) {
            try {
                $conn = new mysqli('127.0.0.1:3306', 'root', '', 'dbE6');

                if ($conn->connect_error) $dbMsg = $conn->connect_error;
                else {
                    $sql = "CREATE TABLE IF NOT EXISTS Employee (
                EmpID INT(4) NOT NULL PRIMARY KEY,
                FName VARCHAR(25) NOT NULL,
                LName VARCHAR(15) NOT NULL,
                Birthday DATE NOT NULL,
                Salary DECIMAL(10, 2) NOT NULL,
                Position VARCHAR(15) NOT NULL
            )";

                    if ($conn->query($sql) === TRUE) {
                        $dbMsg = "Table created successfully!";
                        $tableExists = true;
                    } else {
                        $dbMsg = $conn->error;
                    }
                }

                $conn->close();
            } catch (mysqli_sql_exception $e) {
                $dbMsg = $e->getMessage();
            }
        }

        # drop table
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drop_tb'])) {
            try {
                $conn = new mysqli('127.0.0.1:3306', 'root', '', 'dbE6');

                if ($conn->connect_error) $dbMsg = $conn->connect_error;
                else {
                    $sql = "DROP TABLE IF EXISTS Employee";
                    if ($conn->query($sql) === TRUE) {
                        $dbMsg = "Table dropped successfully!";
                        $tableExists = false;
                    } else {
                        $dbMsg = $conn->error;
                    }
                }

                $conn->close();
            } catch (mysqli_sql_exception $e) {
                $dbMsg = $e->getMessage();
            }
        }

        # form validation (not part of reusable header, moved here to display message properly)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dbExists && $tableExists && isset($_POST['add_record'])) {
            # validate form data
            $validation = validateFormData($_POST);
            $isValid = $validation['isValid'];

            if (isset($validation['message'])) {
                $dbMsg = $validation['message'];
            }

            # extract error messages if validation failed
            if (!$isValid) {
                extract($validation['errors']);
            }

            # process form if validation passed
            if ($isValid) {
                # get and format data
                $empID = $_POST['emp_id'];
                $fName = ucwords(strtolower($_POST["f_name"]));  # convert to title case
                $lName = ucwords(strtolower($_POST["l_name"]));  # convert to title case
                $bDay = date_format(date_create($_POST['b_day']), 'Y-m-d');
                $position = ucfirst($_POST['position']);  # capitalize first letter
                $salary = $_POST['salary'];

                try {
                    $conn = new mysqli('127.0.0.1:3306', 'root', '', 'dbE6');

                    if ($conn->connect_error) $dbMsg = $conn->connect_error;
                    else {
                        $sql = $conn->prepare("INSERT INTO `employee` (EmpID, FName, LName, Birthday, Salary, Position) VALUES (?, ?, ?, ?, ?, ?)");
                        $sql->bind_param("isssds", $empID, $fName, $lName, $bDay, $salary, $position);

                        if ($sql->execute()) {
                            $dbMsg = 'Record created successfully!'; # success message

                            # reset variables
                            $fName = $lName = $bDay = $position = '';
                            $empID = $salary = 0;

                            # unset POST variables
                            unset($_POST['emp_id']);
                            unset($_POST['f_name']);
                            unset($_POST['l_name']);
                            unset($_POST['b_day']);
                            unset($_POST['position']);
                            unset($_POST['salary']);
                        } else $dbMsg = $sql->error;
                    }

                    $conn->close();
                } catch (mysqli_sql_exception $e) {
                    $dbMsg = $e->getMessage();
                }
            }
        }

        ?>

        <div class="header_left">
            <h1>Add Record</h1>
            <p class="err"><?php echo htmlspecialchars($dbMsg); ?></p>
        </div>

        <div class="header_right">
            <div class="db_tb_function">
                <!-- create database -->
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <input type="hidden" name="create_db" value="set">
                    <input type="submit" value="Create Database" <?php if ($dbExists) echo 'disabled' ?>>
                </form>

                <!-- drop database -->
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST"
                    onsubmit="return confirmDrop('database')">
                    <input type="hidden" name="drop_db" value="set">
                    <input type="submit" value="Drop Database" <?php if (!$dbExists) echo 'disabled' ?>>
                </form>

                <!-- create table -->
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <input type="hidden" name="create_table" value="set">
                    <input type="submit" value="Create Table" <?php if (!$dbExists || $tableExists) echo 'disabled' ?>>
                </form>

                <!-- drop table -->
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST"
                    onsubmit="return confirmDrop('table')">
                    <input type="hidden" name="drop_tb" value="set">
                    <input type="submit" value="Drop Table" <?php if (!$dbExists || !$tableExists) echo 'disabled' ?>>
                </form>
            </div>

            <!-- navigation -->
            <form>
                <label for="page-select">Page</label>
                <select id="page-select" onchange="navigateToPage()">
                    <option value="E6-1C.php" selected>Create</option>
                    <option value="E6-1RU.php">Retrieve/Update</option>
                    <option value="E6-1D.php">Display</option>
                </select>
            </form>
        </div>

        <script>
            function confirmDrop(type) {
                return confirm("Are you sure you want to drop the " + type + "?");
            }

            function navigateToPage() {
                window.location.href = document.getElementById('page-select').value;
            }
        </script>
    </div>
    <!--end of reused header-->

    <div class="form_cont">
        <?php # check if database and table exist before displaying forms
        if (!$dbExists): ?>
            <p class="err">The database does not exist. Please create it first.</p>
        <?php elseif (!$tableExists): ?>
            <p class="err">The table does not exist. Please create it first.</p>
        <?php else: ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                <!-- names the form to separate it from other form functions-->
                <input type="hidden" name="add_record" value="set">

                <label for="emp_id">Employee ID: </label>
                <br>
                <input type="number" name="emp_id" id="emp_id" min="0" minlength="1" maxlength="3"
                    value="<?php echo htmlspecialchars($empID != 0 ? $empID : '') ?>">
                <br>

                <label for="f_name">First Name: </label>
                <span class="err"><?php echo $fnErr ?></span>
                <br>
                <input type="text" name="f_name" id="f_name"
                    value="<?php echo htmlspecialchars($fName); ?>">
                <br>

                <label for="l_name">Last Name: </label>
                <span class="err"><?php echo $lnErr ?></span>
                <br>
                <input type="text" name="l_name" id="l_name"
                    value="<?php echo htmlspecialchars($lName); ?>">
                <br>

                <label for="b_day">Birthday: </label>
                <br>
                <input type="date" name="b_day" id="b_day"
                    value="<?php echo htmlspecialchars($bDay); ?>">
                <br>

                <label for="position">Position: </label>
                <span class="err"><?php echo $psErr ?></span>
                <br>
                <input type="text" name="position" id="position"
                    value="<?php echo htmlspecialchars($position); ?>">
                <br>

                <label for="salary">Salary: </label>
                <br>
                <input type="number" name="salary" id="salary" min="0"
                    value="<?php echo htmlspecialchars($salary != 0 ? $salary : '') ?>">
                <br>

                <input type="submit" value="Add Record">
            </form>
        <?php endif; ?>
    </div>
</body>

</html>