<?php
// Script will upload users from csv to MySQL DB.

include_once "./inc/Utility.php";

// Declaring the expected command line options for the script
$short_options = "u:p:h:d:"; // DB access details - username, password, host and db name
$long_options  = array(
    "file:", // CSV file
    "create_table", // command to create user table
    "dry_run", // command to skip db alteration
    "help", // command to see help text for all possible options
);
$options = getopt($short_options, $long_options); // Getting all options specified when executing from terminal

// Executing --help Command
if (isset($options['help'])) { // If help option is specified, print help text and exit.
    Utility:: display_help();

    exit;  // No futher execution required when help command is specified.
}

// Executing --create_table Command
if (isset($options['create_table'])) {
    // Check for all required parameters - Username, Password and DB Host
    if (isset($options['u']) && isset($options['p']) && isset($options['h']) && isset($options['d'])) {
        $conn = get_db_connection ($options['u'], $options['p'], $options['h'], $options['d']);
        Utility::log ("DB connection successful");
        if (!isset($options['dry_run'])) { // Not creating table if it's a dry run
            create_user_table ($conn);
        }
        
        $conn->close();
    } else {
        Utility::log ("Please provide all required DB access parameters. Use --help for more information.");
    }

    exit; // No further execution required if create_table command is specified.
}

// Executing --file Command
if (isset($options['file'])) {
    include_once "./inc/CSV.php";

    $file_uploader = new CSV($options['file']);
    
    if (!$file_uploader->error) {
        if (isset($options['u']) && isset($options['p']) && isset($options['h']) && isset($options['d'])) {
            $conn = get_db_connection ($options['u'], $options['p'], $options['h'], $options['d']);

            $csv_data = $file_uploader->parse_csv();
            $file_uploader->close_csv();
            // prepare and bind
            $ins_sql = $conn->prepare("INSERT INTO users (name, surname, email) VALUES (?, ?, ?)");
            $ins_sql->bind_param("sss", $name, $surname, $email);

            foreach ($csv_data as $data) {
                $name = Utility::title_case ($data['name']);
                $surname = Utility::title_case ($data['surname']);
                $email = strtolower($data['email']);
                Utility::log ("Checking User - ".$name.' | '.$surname.' | '. $email);
                if (Utility::valid_email($email)) {
                    if (!isset($options['dry_run'])) { // Don't import if it's a dry run
                        if ($ins_sql->execute()) {
                            Utility::log ("\tUser imported.");
                        } else {
                            Utility::log ("\tUser import failed - " . $ins_sql->error);
                        }
                    }
                } else {
                    Utility::log ("\tUser error - Invalid email address.");
                }
            }
            $conn->close();
        } else {
            Utility::log ("Please provide all required DB access parameters. Use --help for more information.");
        }
    } else {
        Utility::log ("Exiting script due to file error.");
    }

    exit;
}



/**
 * This method will create user table, will also delete the table first if it already exists
 */
function create_user_table ($conn) {
    // Delete if the table already exists
    $drop_sql = "DROP TABLE IF EXISTS users";

    if ($conn->query($drop_sql) === TRUE) {
        Utility::log("Deleted if table existed already");
    } else {
        Utility::log("Error deleting existing table: " . $conn->error);
        return;
    }

    // sql to create table
    $sql = "CREATE TABLE users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(30) NOT NULL,
        surname VARCHAR(30) NOT NULL,
        email VARCHAR(50) NOT NULL UNIQUE KEY
        )";

    if ($conn->query($sql) === TRUE) {
        Utility::log("Users table created successfully");
    } else {
        Utility::log("Error creating table: " . $conn->error);
    }

    return;
}

/**
 * This method will simply create a MySQL db connection, will display error if connection fails.
 */
function get_db_connection ($user, $pass, $host, $db) {
    try {
        $conn = new mysqli ($host, $user, $pass, $db);
    }
    catch (exception $e) {
        Utility::log("Connection failed: " . $e->getMessage());
        die();
    }

    return $conn;
}


?>