<?php
// Script will upload users from csv to MySQL DB.

// Declaring the expected command line options for the script
$shortOptions = "u:p:h:d:";
$longOptions  = array(
    "file:",
    "create_table",
    "dry_run",
    "help::",
);
$options = getopt($shortOptions, $longOptions);

if (isset($options['help'])) { // If help option is specified, print help text and exit.
    display_help();

    exit;
}

if (isset($options['create_table'])) {
    // Check for all required parameters - Username, Password and DB Host
    if (isset($options['u']) && isset($options['p']) && isset($options['h']) && isset($options['d'])) {
        $conn = get_db_connection ($options['u'], $options['p'], $options['h'], $options['d']);
        create_user_table ();
    }
    else {
        echo "\t"."Please provide all required DB access parameters. Use --help for more information."."\n";
    }

    exit; // No further execution required if create_table option is specified.
}

/**
 * This method will display help text
 */
function display_help () {
    echo "\t"."--file \t\t\t This is the name of the CSV to be parsed"."\n";
    echo "\t"."--create_table \t\t This will cause the MySQL users table to be built \n\t\t\t\t (and no further action will be taken)"."\n";
    echo "\t"."--dry_run \t\t This will be used with the --file directive in the instance \n\t\t\t\t that we want to run the script but not insert into the DB.\n\t\t\t\t All other functions will be executed, but the database won't \n\t\t\t\t be altered."."\n";
    echo "\t"."-u \t\t\t MySQL username"."\n";
    echo "\t"."-p \t\t\t MySQL password"."\n";
    echo "\t"."-h \t\t\t MySQL host"."\n";
    echo "\t"."-d \t\t\t MySQL db name"."\n";
}

function create_user_table () {
    // Create table code will go here...
}

/**
 * This method will simply create a MySQL db connection, will display error if connection fails.
 */
function get_db_connection ($user, $pass, $host, $db) {
    $conn = new mysqli ($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("\tConnection failed: " . $conn->connect_error . "\n");
    } 

    return $conn;
}

?>