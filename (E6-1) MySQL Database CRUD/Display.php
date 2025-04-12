<?php

# initialize message variable
$dbMsg = '';

/**
 * format salary with a currency symbol and proper decimal placement
 * @param float $salary the salary value to format
 * @return string formatted salary
 */
function formatSalary($salary)
{
    return '₱' . number_format($salary, 2);
}

/**
 * format date from YYYY-MM-DD to a more readable format
 * @param string $date the date in database format
 * @return string formatted date
 */
function formatDate($date)
{
    return date('F j, Y', strtotime($date));
}
?>

<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Employee Database | Display</title>
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
        ?>

        <div class="header_left">
            <h1>Display All</h1>
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
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" onsubmit="return confirmDrop('database')">
                    <input type="hidden" name="drop_db" value="set">
                    <input type="submit" value="Drop Database" <?php if (!$dbExists) echo 'disabled' ?>>
                </form>

                <!-- create table -->
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <input type="hidden" name="create_table" value="set">
                    <input type="submit" value="Create Table" <?php if (!$dbExists || $tableExists) echo 'disabled' ?>>
                </form>

                <!-- drop table -->
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" onsubmit="return confirmDrop('table')">
                    <input type="hidden" name="drop_tb" value="set">
                    <input type="submit" value="Drop Table" <?php if (!$dbExists || !$tableExists) echo 'disabled' ?>>
                </form>
            </div>

            <!-- navigation -->
            <form>
                <label for="page-select">Page</label>
                <select id="page-select" onchange="navigateToPage()">
                    <option value="E6-1C.php">Create</option>
                    <option value="E6-1RU.php">Retrieve/Update</option>
                    <option value="E6-1D.php" selected>Display</option>
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

    <div class="display_cont">
        <?php
        if (!$dbExists) {
            echo "<p class='err'>The database does not exist. Please create the database first.</p>";
        } elseif (!$tableExists) {
            echo "<p class='err'>The Employee table does not exist. Please create the table first.</p>";
        } else {
            try {
                $conn = new mysqli('127.0.0.1:3306', 'root', '', 'dbE6');

                if ($conn->connect_error) {
                    echo "<p class='err'>Connection failed: " . $conn->connect_error . "</p>";
                } else {
                    # query select all employees, ordered by ID
                    $sql = "SELECT * FROM Employee ORDER BY EmpID";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        # sum of records in the database
                        echo "<p class='record-count'>Total records: " . $result->num_rows . "</p>";

                        echo "<div class='employee-table'>";
                        echo "<div class='employee-header'>
                                    <div class='employee-cell'>ID</div>
                                    <div class='employee-cell'>First Name</div>
                                    <div class='employee-cell'>Last Name</div>
                                    <div class='employee-cell'>Birthday</div>
                                    <div class='employee-cell'>Salary</div>
                                    <div class='employee-cell'>Position</div>
                              </div>";

                        while ($row = $result->fetch_assoc()) {
                            # pads the ID with leading zeros to 3 digits [1 -> 001]
                            echo "<div class='employee-row'>
                                        <div class='employee-cell'>" . htmlspecialchars(str_pad($row['EmpID'], 3, '0', STR_PAD_LEFT)) . "</div>
                                        <div class='employee-cell'>" . htmlspecialchars($row['FName']) . "</div>
                                        <div class='employee-cell'>" . htmlspecialchars($row['LName']) . "</div>
                                        <div class='employee-cell'>" . formatDate($row['Birthday']) . "</div>
                                        <div class='employee-cell'>" . formatSalary($row['Salary']) . "</div>
                                        <div class='employee-cell'>" . htmlspecialchars($row['Position']) . "</div>
                                  </div>";
                        }

                        echo "</div>";

                    } else {
                        echo "<p class='err'>No records found in the Employee table.</p>";
                    }
                }

                $conn->close();
            } catch (mysqli_sql_exception $e) {
                echo "<p class='err'>Error: " . $e->getMessage() . "</p>";
            }
        }
        ?>
    </div>
</body>

</html>