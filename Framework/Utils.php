<?php

class Utils {

    public static function dateToEn(string $date, string $lang='en'):string|false {
        if($date == null) {
            return "";
        }
        if ($lang == "fr" || str_contains($date, "/")) {
            $dateArray = explode("/", $date);
            if (count($dateArray) == 3) {
                $day = $dateArray[0];
                $month = $dateArray[1];
                $year = $dateArray[2];
                return $year . "-" . $month . "-" . $day;
            }
            return false;
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
            throw new PfmParamException("invalide date ".$date, 403);
        }
        $date_en = self::dateToEn($date, $lang);
        if($date_en === false) {
            if($now) {
                return $defaultTs;
            }
            throw new PfmParamException("invalide date ".$date, 403);
        } 
        return strtotime(sprintf("%s %d:%d:%d", $date_en, $hour, $min, $sec));
    }

}


?>


