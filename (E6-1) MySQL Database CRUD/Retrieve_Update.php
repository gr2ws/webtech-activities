<?php
# variables
$fName = $lName = $bDay = $position = '';
$empID = $salary = 0;

# search variables/flag
$searchID = '';
$employeeFound = false;

# error messages
$dbMsg = '';
$fnErr = $lnErr = $psErr = '';

/**
 * validate a name to ensure it contains only letters, spaces, and hyphens
 * @param string $name name to validate
 * @return bool true if name is valid, false otherwise
 */
function validateName($name)
{
    return !preg_match('~[^a-zA-Z\s\-]+~', $name);
}

/**
 * process form data and validates all fields
 * @param array $postData the POST data from the form
 * @return array associative array with validation result and errors
 */
function validateFormData($postData)
{
    $errors = [
        'fnErr' => '',
        'lnErr' => '',
        'psErr' => ''
    ];
    $isValid = true;

    # check for empty fields
    foreach ($postData as $key => $value) {
        if (empty($value) && $key != 'update_employee') {
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

/**
 * search for an employee by ID
 * @param int $id employee ID to search for
 * @return array|bool return employee data as array if found, false otherwise
 */
function findEmployeeById($id)
{
    try {
        $conn = new mysqli('127.0.0.1:3306', 'root', '', 'dbE6');

        if ($conn->connect_error) {
            return false;
        }

        $stmt = $conn->prepare("SELECT * FROM Employee WHERE EmpID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $employee = $result->fetch_assoc();
            $conn->close();
            return $employee;
        }

        $conn->close();
        return false;
    } catch (mysqli_sql_exception $e) {
        return false;
    }
}

/**
 * reset all form fields
 */
function resetFormFields()
{
    global $empID, $fName, $lName, $bDay, $salary, $position, $employeeFound;

    $empID = 0;
    $fName = $lName = $bDay = $position = '';
    $salary = 0;
    $employeeFound = false;
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Employee Database | Retrieve & Update</title>
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

        # Delete employee
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_employee']) && $tableExists) {
            $empID = $_POST['emp_id'];

            if (empty($empID)) {
                $dbMsg = "No employee ID provided for deletion.";
            } else {
                try {
                    $conn = new mysqli('127.0.0.1:3306', 'root', '', 'dbE6');

                    if ($conn->connect_error) {
                        $dbMsg = $conn->connect_error;
                    } else {
                        $query = $conn->prepare("DELETE FROM Employee WHERE EmpID = ?");
                        $query->bind_param("i", $empID);

                        if ($query->execute()) {
                            $dbMsg = "Employee deleted successfully!";
                            // Reset form fields after successful deletion
                            resetFormFields();
                        } else {
                            $dbMsg = $query->error;
                        }
                    }

                    $conn->close();
                } catch (mysqli_sql_exception $e) {
                    $dbMsg = $e->getMessage();
                }
            }
        }

        # check if user is searching for an employee
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_employee']) && $tableExists) {
            $searchID = $_POST['search_id'];
            $employeeFound = false; # reset the flag at the beginning of each search

            if (empty($searchID)) {
                $dbMsg = "Please enter an employee ID.";
            } else {
                # find employee by id
                $employee = findEmployeeById($searchID);

                if ($employee) {
                    # put data into variables to display in the form fields if found
                    $empID = $employee['EmpID'];
                    $fName = $employee['FName'];
                    $lName = $employee['LName'];
                    $bDay = $employee['Birthday'];
                    $salary = $employee['Salary'];
                    $position = $employee['Position'];
                    $dbMsg = "Employee found!";
                    $employeeFound = true;
                } else {
                    $dbMsg = "No employee found with ID: " . $searchID;
                    resetFormFields();
                }
            }
        }

        # update employee information
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employee']) && $tableExists) {
            # validate form data
            $validation = validateFormData($_POST);
            $isValid = $validation['isValid'];

            if (isset($validation['message'])) {
                $dbMsg = $validation['message'];
            }

            # get error messages if validation failed
            if (!$isValid) {
                extract($validation['errors']);
            }

            # process update if validation passed
            if ($isValid) {
                # Get and format data
                $empID = $_POST['emp_id'];
                $fName = ucwords(strtolower($_POST["f_name"]));  # title case
                $lName = ucwords(strtolower($_POST["l_name"]));
                $bDay = date_format(date_create($_POST['b_day']), 'Y-m-d');
                $position = ucfirst($_POST['position']);  # capitalize first letter
                $salary = $_POST['salary'];

                try {
                    $conn = new mysqli('127.0.0.1:3306', 'root', '', 'dbE6');

                    if ($conn->connect_error) {
                        $dbMsg = $conn->connect_error;
                    } else {
                        $query = $conn->prepare("UPDATE Employee SET FName = ?, LName = ?, Birthday = ?, Salary = ?, Position = ? WHERE EmpID = ?");
                        $query->bind_param("sssdsi", $fName, $lName, $bDay, $salary, $position, $empID);

                        if ($query->execute()) {
                            $dbMsg = "Employee updated successfully!";
                            # reset form fields after successful update
                            resetFormFields();
                        } else {
                            $dbMsg = $query->error;
                        }
                    }

                    $conn->close();
                } catch (mysqli_sql_exception $e) {
                    $dbMsg = $e->getMessage();
                }
            }
        }
        ?>

        <div class="header_left">
            <h1>Search & Edit</h1>
            <p class="err"><?php echo $dbMsg ?></p>
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
                    <option value="Create.php">Create</option>
                    <option value="Retrieve_Update.php" selected>Retrieve/Update</option>
                    <option value="Display.php">Display</option>
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

    <div class="search_edit_cont">
        <?php # check if database and table exist before displaying forms
        if (!$dbExists): ?>
            <p class="err">The database does not exist. Please create the database first.</p>
        <?php elseif (!$tableExists): ?>
            <p class="err">The Employee table does not exist. Please create the table first.</p>
        <?php else: ?>
            <!-- search form -->
            <div class="search_form">
                <h2>Search</h2>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <input type="hidden" name="search_employee" value="set">

                    <label for="search_id">Employee ID: </label>
                    <input type="number" name="search_id" id="search_id" required>

                    <input type="submit" value="Search">
                </form>
            </div>

            <!-- edit form -->
            <div class="edit_form">
                <h2>Update</h2>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <input type="hidden" name="update_employee" value="set">

                    <label for="emp_id">Employee ID: </label>
                    <!-- disabled to disallow changing PK -->
                    <input type="number" id="emp_id" value="<?php echo ($empID != 0) ? htmlspecialchars($empID) : ''; ?>" readonly disabled>
                    <!-- Add a hidden input field that will be submitted with the form
                 since the form above is not submitted (disabled) -->
                    <input type="hidden" name="emp_id" value="<?php echo $empID ?>">
                    <br>

                    <label for="f_name">First Name: </label>
                    <input type="text" name="f_name" id="f_name" value="<?php echo htmlspecialchars($fName); ?>" required <?php if (!$employeeFound) echo 'disabled'; ?>>
                    <span class="err"><?php echo $fnErr ?></span>
                    <br>

                    <label for="l_name">Last Name: </label>
                    <input type="text" name="l_name" id="l_name" value="<?php echo htmlspecialchars($lName); ?>" required <?php if (!$employeeFound) echo 'disabled'; ?>>
                    <span class="err"><?php echo $lnErr ?></span>
                    <br>

                    <label for="b_day">Birthday: </label>
                    <input type="date" name="b_day" id="b_day" value="<?php echo htmlspecialchars($bDay); ?>" required <?php if (!$employeeFound) echo 'disabled'; ?>>
                    <br>

                    <label for="position">Position: </label>
                    <input type="text" name="position" id="position" value="<?php echo htmlspecialchars($position); ?>" required <?php if (!$employeeFound) echo 'disabled'; ?>>
                    <span class="err"><?php echo $psErr ?></span>
                    <br>

                    <label for="salary">Salary: </label>
                    <input type="number" name="salary" id="salary" min="0" step="0.01" value="<?php echo ($salary != 0) ? htmlspecialchars($salary) : ''; ?>" required <?php if (!$employeeFound) echo 'disabled'; ?>>
                    <br>

                    <input type="submit" value="Update" <?php if (!$employeeFound) echo 'disabled'; ?>>
                </form>

                <?php # code ony runs if an employee was found
                if ($employeeFound): ?>
                    <!-- Delete form -->
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" onsubmit="return confirmDelete()">
                        <input type="hidden" name="delete_employee" value="set">
                        <input type="hidden" name="emp_id" value="<?php echo htmlspecialchars($empID); ?>">
                        <input type="submit" value="Delete" class="delete-btn">
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this record?");
        }
    </script>

</body>

</html>