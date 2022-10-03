<?php

class Utils {

    public static function dateToEn(string $date, string $lang='en'):string|false {
        if($date == null) {
            return "";
        }
        if ($lang == "fr" || str_contains($date, "/")) {
            $dateArray = explode("/", $date);
            if (count($dateArray) == 3) {
                if(strlen($dateArray[2]) != 4) {
                    return false;
                }
                $day = $dateArray[0];
                $month = $dateArray[1];
                $year = $dateArray[2];
                return $year . "-" . $month . "-" . $day;
            }
            return $date;
        }
        // En
        return $date;
    }

    /**
     * Convert a date to a timestamp
     * 
     * @param $date Y-m-d (en) or d/m/Y (fr)
     * @param $lang date format (en/fr)
     * @param $hour optional hour else 0
     * @param $min optional minutes else 0
     * @param $sec optional seconds else 0
     * @param $now if set, in case of error, return current date timestamp with hour=0,min=0
     */
    public static function timestamp(string $date, string $lang='en', int $hour=0, int $min=0, int $sec=0, bool $now=false):int|false {
        $defaultTs = strtotime(date('Y-m-d'));
        if(!$date) {
            if($now) {
                return $defaultTs;
            }
            throw new PfmParamException("invalid date ".$date);
        }
        $date_en = self::dateToEn($date, $lang);
        if($date_en === false) {
            if($now) {
                return $defaultTs;
            }
            throw new PfmParamException("invalid date ".$date);
        } 
        return strtotime(sprintf("%s %d:%d:%d", $date_en, $hour, $min, $sec));
    }

    /**
     * Get column name (excel like eg A..Z AA...AZ BA..BZ etc.) from column index
     * 
     * @param int $num  index of column, starting at value 0=A, 1=B etc.
     * @return string name of the column
     */
    private static function _get_col_letter($num) {
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        //if the number is greater than 26, calculate to get the next letters
        if ($num >= 26) {
            return self::_get_col_letter($num/26 - 1) . $letters[$num%26];
        } else {
            //return the letter
            return $letters[$num];
        }
    }

    /**
     * Get column name (excel like eg A..Z AA...AZ BA..BZ etc.) from column index
     * 
     * @param int $num  index of column, starting at value 1=A, 2=B etc.
     * @return string name of the column
     */
    public static function get_col_letter($num):string {
        if($num<=0) {
            throw new PfmException("[utils][get_col_letter] invalid value ".$num);
        }
        return self::_get_col_letter($num-1);
    }


}


?>


