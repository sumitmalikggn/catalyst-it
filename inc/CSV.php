<?php 
/**
 * Class to handle CSV file
 */
Class CSV {
    private $file;

    // Initializes the $file variable with file path
    public function __construct ($file_path) {
        if (is_file($file_path)) {
            if ($this->valid_csv($file_path)) {
                try {
                    if (!$this->file = fopen($file_path, "r")) {
                        throw new Exception ('Failed to open file.');
                    }
                } catch (Exception $e) {
                    die (Utility::log ('ERROR', $e->getMessage()));
                }
            } else {
                die (Utility::log ('ERROR', 'Invalid file. Please specify a valid CSV file.'));
            }

        } else {
            die (Utility::log ('ERROR', 'File does not exist. Please specify a valid CSV file.'));
        }
    }

    /**
     * Closes the opened csv handle
     */
    public function close_csv() {
        fclose($this->file);
    }

    /**
     * This method checks if a file is a valid csv or not.
     */
    private function valid_csv ($file_path) {
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
        $mime_type = finfo_file( $finfo, $file_path );

        return in_array( $mime_type, $csv_mime_types );
    }

    /**
     * This method reads the opened csv file and retuns the complete data in a single array 
     */
    public function parse_csv () {
        $data = array();
        $header = fgetcsv($this->file);
        array_walk($header, create_function('&$val', '$val = trim($val);'));
        while ($row = fgetcsv($this->file)) {
            array_walk($row, create_function('&$val', '$val = trim($val);'));
            $data[] = array_combine($header, $row);
            
        }

        return $data;
    }
}

?>