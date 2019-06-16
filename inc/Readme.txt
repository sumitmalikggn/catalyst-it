File list
------------
user_upload.php		PHP script to upload users from CSV to DB
inc/DB.php		    DB class to handle all DB transactions
inc/CSV.php		    Class to handle CSV file
inc/Utility.php		Class containing all utility methods to be used globally
users.csv	        A sample users CSV file
foobar.php	        Script implementing the foobar problem


Command Line Arguments
----------------------
Examples:  
    php user_upload.php --file users.csv -u root -p mysql -h localhost -d catalyst-it
    php user_upload.php --create_table -u root -p mysql -h localhost -d catalyst-it
    php user_upload.php --dry_run --file users.csv -u root -p mysql -h localhost -d catalyst-it
    php user_upload.php --help

--file                   This is the name of the CSV to be parsed (optional)
--create_table           This will cause the MySQL users table to be built 
                         (and no further action will be taken) (optional)
--dry_run                This will be used with the --file directive in the instance 
                         that we want to run the script but not insert into the DB.
                         All other functions will be executed, but the database won't 
                         be altered (optional)
-u                       MySQL username (required with --file or --create_table)
-p                       MySQL password (required with --file or --create_table)
-h                       MySQL host (required with --file or --create_table)
-d                       MySQL db name (required with --file or --create_table)


Assumptions
-----------
1. Users CSV file header keys are same as users table columns.
2. All required PHP and MySQL packages are installed for the terminal execution of the script.