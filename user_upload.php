<?php
// Script will upload users from csv to MySQL DB.

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
    display_help();

    exit;  // No futher execution required when help command is specified.
}

// Executing --create_table Command
if (isset($options['create_table'])) {
    // Check for all required parameters - Username, Password and DB Host
    if (isset($options['u']) && isset($options['p']) && isset($options['h']) && isset($options['d'])) {
        $conn = get_db_connection ($options['u'], $options['p'], $options['h'], $options['d']);
        log_message ("DB connection successful");
        if (!isset($options['dry_run'])) { // Not creating table if it's a dry run
            create_user_table ($conn);
        }
        
        $conn->close();
    } else {
        log_message ("Please provide all required DB access parameters. Use --help for more information.");
    }

    exit; // No further execution required if create_table command is specified.
}

// Executing --file Command
if (isset($options['file'])) {
    if (is_file($options['file'])) {
        if (valid_csv ($options['file'])) {
            $handle = fopen($options['file'], "r");

            if (isset($options['u']) && isset($options['p']) && isset($options['h']) && isset($options['d'])) {
                $conn = get_db_connection ($options['u'], $options['p'], $options['h'], $options['d']);

                // prepare and bind
                $ins_sql = $conn->prepare("INSERT INTO users (name, surname, email) VALUES (?, ?, ?)");
                $ins_sql->bind_param("sss", $name, $surname, $email);

                log_message ("Reading file...");
                fgetcsv($handle, 10000, ","); // Ignoring first line in the file as it contains only field names
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $name = titleCase($data[0]);
                    $surname = titleCase($data[1]);
                    $email = strtolower($data[2]);
                    log_message ("Checking User - ".$name.' | '.$surname.' | '. $email);
                    if (valid_email($email)) {
                        if (!isset($options['dry_run'])) { // Don't import if it's a dry run
                            if ($ins_sql->execute()) {
                                log_message ("\tUser imported.");
                            } else {
                                log_message ("\tUser import failed - " . $ins_sql->error);
                            }
                        }
                    } else {
                        log_message ("\tUser error - Invalid email address.");
                    }
                }
                $conn->close();
            } else {
                log_message ("Please provide all required DB access parameters. Use --help for more information.");
            }
        } else {
            log_message ("Invalid file. Please specify a valid CSV file.");
        }
    } else {
        log_message ("File do not exist. Please specify a valid CSV file.");
    }

    exit;
}

/**
 * This method will display help text
 */
function display_help () {
    log_message ("--file \t\t\t This is the name of the CSV to be parsed");
    log_message ("--create_table \t\t This will cause the MySQL users table to be built \n\t\t\t\t (and no further action will be taken)");
    log_message ("--dry_run \t\t This will be used with the --file directive in the instance \n\t\t\t\t that we want to run the script but not insert into the DB.\n\t\t\t\t All other functions will be executed, but the database won't \n\t\t\t\t be altered");
    log_message ("-u \t\t\t MySQL username");
    log_message ("-p \t\t\t MySQL password");
    log_message ("-h \t\t\t MySQL host");
    log_message ("-d \t\t\t MySQL db name");

    return;
}

/**
 * This method will create user table, will also delete the table first if it already exists
 */
function create_user_table ($conn) {
    // Delete if the table already exists
    $drop_sql = "DROP TABLE IF EXISTS users";

    if ($conn->query($drop_sql) === TRUE) {
        log_message("Deleted if table existed already");
    } else {
        log_message("Error deleting existing table: " . $conn->error);
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
        log_message("Users table created successfully");
    } else {
        log_message("Error creating table: " . $conn->error);
    }

    return;
}

/**
 * This method will simply create a MySQL db connection, will display error if connection fails.
 */
function get_db_connection ($user, $pass, $host, $db) {
    $conn = new mysqli ($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die(log_message ("Connection failed: " . $conn->connect_error));
    } 

    return $conn;
}

/**
 * This method will display a given string message on Terminal.
 */
function log_message ($msg) {
    echo "\t" . $msg . "\n";

    return;
}

/**
 * This method check if an email address is valid or not.
 */
function valid_email ($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    return true;
}

/**
 * This method check if a file is a valid csv or not.
 */
function valid_csv ($file) {
    $csv_mime_types = [ 
        'text/csv',
        'text/plain',
        'application/csv',
        'text/comma-separated-values',
        'application/excel',
        'application/vnd.ms-excel',
        'application/vnd.msexcel',
        'text/anytext',
        'application/octet-stream',
        'application/txt',
    ];
    $finfo = finfo_open( FILEINFO_MIME_TYPE );
    $mime_type = finfo_file( $finfo, $file );

    return in_array( $mime_type, $csv_mime_types );
}

/**
 * This method converts a string to proper name format.
 */
function titleCase($string) {
    $word_splitters = array(' ', '-', "O'", "L'", "D'", 'St.', 'Mc', 'Mac');
    $lowercase_exceptions = array('the', 'van', 'den', 'von', 'und', 'der', 'de', 'di', 'da', 'of', 'and', "l'", "d'");
    $uppercase_exceptions = array('III', 'IV', 'VI', 'VII', 'VIII', 'IX');

    $string = strtolower($string);
    foreach ($word_splitters as $delimiter) {
        $words = explode($delimiter, $string);
        $newwords = array();
        foreach ($words as $word) {
            if (in_array(strtoupper($word), $uppercase_exceptions))
                $word = strtoupper($word);
            else
                if (!in_array($word, $lowercase_exceptions))
                    $word = ucfirst($word);

            $newwords[] = $word;
        }

        if (in_array(strtolower($delimiter), $lowercase_exceptions))
            $delimiter = strtolower($delimiter);

        $string = join($delimiter, $newwords);
    }
    return $string;
}


?>