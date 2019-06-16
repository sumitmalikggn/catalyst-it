<?php
// Script will upload users from csv to MySQL DB.

include_once "./inc/Utility.php";
include_once "./inc/DB.php";

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
        $db = new DB ($options['h'], $options['u'], $options['p'], $options['d']);
        Utility::log ('SUCCESS', 'DB connection successful');
        if (!isset($options['dry_run'])) { // Not creating table if it's a dry run
            Utility::log('INFO', 'Deleting if users table exists...');
            $drop_sql = "DROP TABLE IF EXISTS users";
            $db->execute_query($drop_sql);

            Utility::log('INFO', 'Creating users table...');
            // sql to create table
            $table_sql = "CREATE TABLE users (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(30) NOT NULL,
                surname VARCHAR(30) NOT NULL,
                email VARCHAR(50) NOT NULL UNIQUE KEY
                )";
            
            $db->execute_query($table_sql);
            Utility::log('SUCCESS', 'Table users created successfully.');
        }
        
        $db->close();
    } else {
        Utility::log ('ERROR', 'Please provide all required DB access parameters. Use --help for more information.');
    }

    exit; // No further execution required if create_table command is specified.
}

// Executing --file Command
if (isset($options['file'])) {
    include_once "./inc/CSV.php";

    $file_uploader = new CSV($options['file']);
    
    if (!$file_uploader->error) {
        if (isset($options['u']) && isset($options['p']) && isset($options['h']) && isset($options['d'])) {
            $db = new DB ($options['h'], $options['u'], $options['p'], $options['d']);
            Utility::log ('INFO', 'Reading file...');
            $csv_data = $file_uploader->parse_csv();
            $file_uploader->close_csv();

            foreach ($csv_data as $data) {
                $data['name'] = Utility::title_case ($data['name']);
                $data['surname'] = Utility::title_case ($data['surname']);
                $data['email'] = strtolower($data['email']);
                Utility::log ('INFO', 'Checking User - '.implode(' | ', $data));
                if (Utility::valid_email($data['email'])) {
                    if (!isset($options['dry_run'])) { // Not creating table if it's a dry run
                        $db->insert('users', $data); // Insert user
                    }
                } else {
                    Utility::log ('ERROR', "\tUser error - Invalid email address.");
                }
            }

            $db->close();
        } else {
            Utility::log ('ERROR', 'Please provide all required DB access parameters. Use --help for more information.');
        }
    } else {
        Utility::log ('INFO', 'Exiting script due to file error.');
    }

    exit;
}



?>