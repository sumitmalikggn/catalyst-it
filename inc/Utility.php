<?php
/**
 * Class with static utility methods 
 */
Class Utility {
    /**
     * Output messages on Terminal
     */
    public static function log ($type, $msg) {
        switch ($type) {
            case 'SUCCESS':
                $color = '0;32';
                break;
            case 'ERROR':
                $color = '0;31';
                break;
            case 'INFO':
            default:
                $color = '0;30';
        }
        
        echo "\t".chr(27)."[".$color."m" . $msg . chr(27)."[0m\n";

        return;
    }

    /**
     * This method will display help text
     */
    public static function display_help () {
        self::log ('INFO', "--file \t\t\t This is the name of the CSV to be parsed");
        self::log ('INFO', "--create_table \t\t This will cause the MySQL users table to be built \n\t\t\t\t (and no further action will be taken)");
        self::log ('INFO', "--dry_run \t\t This will be used with the --file directive in the instance \n\t\t\t\t that we want to run the script but not insert into the DB.\n\t\t\t\t All other functions will be executed, but the database won't \n\t\t\t\t be altered");
        self::log ('INFO', "-u \t\t\t MySQL username");
        self::log ('INFO', "-p \t\t\t MySQL password");
        self::log ('INFO', "-h \t\t\t MySQL host");
        self::log ('INFO', "-d \t\t\t MySQL db name");

        return;
    }

    /**
     * This method converts a string to proper name format.
     */
    public static function title_case ($string) {
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

    /**
     * This method check if an email address is valid or not.
     */
    function valid_email ($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }
}
?>