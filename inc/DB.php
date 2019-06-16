<?php
/**
 * DB handler class
 */
Class DB {
    private $dblink;

    public function __construct ($host, $user, $pass, $dbname) {
        $this->dblink = new mysqli ($host, $user, $pass, $dbname);
        
        if ($this->dblink->connect_error) {
            die(Utility::log ('ERROR', 'Error Connecting DB: ' . $this->dblink->connect_error));
        }
    }

    /**
     * Execute a db query
     */
    public function execute_query ($query) {
        if ($this->dblink->query($query) !== TRUE) {
            die (Utility::log ('ERROR', 'DB Error - ' . $this->dblink->error));
        }

        return;
    }

    /**
     * Method to insert data to the given table.
     */
    public function insert ($tableName, $data) {
        $cols = ''; $vals = '';
        foreach( $data as $key => $val ){
            $cols .= $key.', ';
            $vals .= "'".$this->dblink->real_escape_string($val)."', ";
        }
        $cols = rtrim( $cols , ", ");
        $vals = rtrim( $vals , ", ");
        $query = "INSERT INTO `".$tableName."` (".$cols.") VALUES (".$vals.")";
        
        if ($this->dblink->query($query) === TRUE) {
            Utility::log ('SUCCESS', 'Record Inserted.');
        }
        else {
            Utility::log ('ERROR', 'Insert Error - ' . $this->dblink->error);
        }

        return;
    }

    /**
     * Closes an open DB connection
     */
    public function close () {
        if( ! mysqli_close($this->dblink) ){
            Utility::log ('ERROR', 'Error While Closing Connection to DB: '.mysqli_error( $this->dblink ));
        }
    }
}

?>