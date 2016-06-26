<?php


class Moogento_ShipEasy_Helper_Date extends Mage_Core_Helper_Abstract
{
    const TYPE_PERSIAN = 'persian';
    const TYPE_THAI = 'thai';
    const TYPE_GREGORIAN = 'gregorian';
    const THAI_YEAR_INCREMENT = 543;

    protected $_thaiMonth = array(
        '01'=>'มกราคม',
        '02'=>'กุมภาพันธ์',
        '03'=>'มีนาคม',
        '04'=>'เมษายน',
        '05'=>'พฤษภาคม',
        '06'=>'มิถุนายน',
        '07'=>'กรกฎาคม',
        '08'=>'สิงหาคม',
        '09'=>'กันยายน',
        '10'=>'ตุลาคม',
        '11'=>'พฤศจิกายน',
        '12'=>'ธันวาคม'
    );
    protected $_thaiMonthShort = array(
        '01'=>'ม.ค.',
        '02'=>'ก.พ.',
        '03'=>'มี.ค.',
        '04'=>'เม.ย.',
        '05'=>'พ.ค.',
        '06'=>'มิ.ย.',
        '07'=>'ก.ค.',
        '08'=>'ส.ค.',
        '09'=>'ก.ย.',
        '10'=>'ต.ค.',
        '11'=>'พ.ย.',
        '12'=>'ธ.ค.'
    );
    protected $_thaiDayWeek = array(
        "Sat" => "ส.",
        "Sun" => "อา.",
        "Mon" => "จ.",
        "Tue" => "อ.",
        "Wed" => "พ.",
        "Thu" => "พฤ.",
        "Fri" => "ศ.",
        "Saturday" => "เสาร์",
        "Sunday" => "อาทิตย์",
        "Monday" => "จันทร์",
        "Tuesday" => "อังคาร",
        "Wednesday" => "พุธ",
        "Thursday" => "พฤหัสบดี",
        "Friday" => "ศุกร์",
    );


    protected $_persianDayWeek = array(
        "Sat" => "&#1588;",
        "Sun" => "&#1609;",
        "Mon" => "&#1583;",
        "Tue" => "&#1587;",
        "Wed" => "&#1670;",
        "Thu" => "&#1662;",
        "Fri" => "&#1580;",
        "Saturday" => "&#1588;&#1606;&#1576;&#1607;",
        "Sunday" => "&#1610;&#1603;&#1588;&#1606;&#1576;&#1607;",
        "Monday" => "&#1583;&#1608;&#1588;&#1606;&#1576;&#1607;",
        "Tuesday" => "&#1587;&#1607;&#32;&#1588;&#1606;&#1576;&#1607;",
        "Wednesday" => "&#1670;&#1607;&#1575;&#1585;&#1588;&#1606;&#1576;&#1607;",
        "Thursday" => "&#1662;&#1606;&#1580;&#1588;&#1606;&#1576;&#1607;",
        "Friday" => "&#1580;&#1605;&#1593;&#1607;",
    );
    protected $_persianMonth = array(
        "1" => "&#1601;&#1585;&#1608;&#1585;&#1583;&#1610;&#1606;",
        "2" => "&#1575;&#1585;&#1583;&#1610;&#1576;&#1607;&#1588;&#1578;",
        "3" => "&#1582;&#1585;&#1583;&#1575;&#1583;",
        "4" => "&#1578;&#1610;&#1585;",
        "5" => "&#1605;&#1585;&#1583;&#1575;&#1583;",
        "6" => "&#1588;&#1607;&#1585;&#1610;&#1608;&#1585;",
        "7" => "&#1605;&#1607;&#1585;",
        "8" => "&#1570;&#1576;&#1575;&#1606;",
        "9" => "&#1570;&#1584;&#1585;",
        "10" => "&#1583;&#1610;",
        "11" => "&#1576;&#1607;&#1605;&#1606;",
        "12" => "&#1575;&#1587;&#1601;&#1606;&#1583;",
    );
    protected $_persianMonthShort = array(
        "1" => "&#1601;&#1585;&#1608;",
        "2" => "&#1575;&#1585;&#1583;",
        "3" => "&#1582;&#1585;&#1583;",
        "4" => "&#1578;&#1610;&#1585;",
        "5" => "&#1605;&#1585;&#1583;",
        "6" => "&#1588;&#1607;&#1585;",
        "7" => "&#1605;&#1607;&#1585;",
        "8" => "&#1570;&#1576;&#1575;",
        "9" => "&#1570;&#1584;&#1585;",
        "10" => "&#1583;&#1610;",
        "11" => "&#1576;&#1607;&#1605;",
        "12" => "&#1575;&#1587;&#1601;",
    );
    protected $_persianNumber = array("&#1776;", "&#1777;", "&#1778;", "&#1779;", "&#1780;", "&#1781;", "&#1782;", "&#1783;", "&#1784;", "&#1785;");
    protected $_arNumber = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    protected $_thNumber = array('๐', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙');

    protected function _prepareJsFormat($format)
    {
        $pattern = array(
            'mm','m',
            'ss','s',
            'MM','M',
            'yyyy','yy'
        );
        $replace = array(
            'i','i',
            's','s',
            'm','n',
            'Y','y'
        );
        foreach($pattern as &$p)
        {
            $p = '/'.$p.'/';
        }
        $result = preg_replace($pattern, $replace, $format);
        $first_test = preg_replace('/dd/','d',$result);
        $result = ($first_test != $result) ? $first_test : preg_replace('/d/','j',$result);;
        $first_test = preg_replace('/HH/','H',$result);
        $result = ($first_test != $result) ? $first_test : preg_replace('/H/','G',$result);;
        return $result;
    }

    public function format($date, $format, $jsFormat = false, $type = self::TYPE_GREGORIAN)
    {
        $date = strtotime($date);
        if ($jsFormat) {
            $format = $this->_prepareJsFormat($format);
        }
        switch ($type) {
            case self::TYPE_THAI:
                return $this->formatThai($date, $format);
            case self::TYPE_PERSIAN:
                return $this->formatPersian($date, $format);
        }

        return date($format, $date);
    }

    public function formatThai($date, $format)
    {
        $thaiNumber = Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_created_at_thai_numbers');

        $year = date("Y",$date);
        $month = date("m",$date);
        $day = date("d",$date);

        $i = 0;
        $subtypeTemp = '';
        $result = '';

        list( $year, $month, $day ) = $this->gregorianToThai($year, $month, $day);

        while($i < strlen($format))
        {
            $subtype=substr($format,$i,1);
            if($subtypeTemp=="\\")
            {
                $result.=$subtype;
                $i++;
                continue;
            }

            switch ($subtype)
            {

                case "A":
                case "a":
                    $result .= 'น.';
                    break;
                case "d":
                    $tmpDay = str_pad($day, 2, '0', STR_PAD_LEFT);
                    $result .= $thaiNumber == 1 ? $this->number2thai($tmpDay) : $tmpDay;
                    break;
                case "D":
                    $result .= $this->_thaiDayWeek[date("D",$date)];
                    break;
                case"F":
                    $result .= $this->_thaiMonth[str_pad($month, 2, '0', STR_PAD_LEFT)];
                    break;
                case "j":
                    $result .= $thaiNumber == 1 ? $this->number2thai($day) : $day;
                    break;
                case "l":
                    $result .= $this->_thaiDayWeek[date("l",$date)];
                    break;
                case "m":
                    $tmpMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
                    $result .= $thaiNumber==1 ? $this->number2thai($tmpMonth) : $tmpMonth;
                    break;
                case "M":
                    $result .= $this->_thaiMonthShort[str_pad($month, 2, '0', STR_PAD_LEFT)];
                    break;
                case "n":
                    $result .= $thaiNumber==1 ? $this->number2thai($month) : $month;
                    break;
                case "S":
                    $result .= "";
                    break;
                case "t":
                    $result .= date('t', $date);
                    break;
                case "y":
                    $tmpYear = substr($year,2,4);
                    $result .= $thaiNumber==1 ? $this->number2thai($tmpYear) : $tmpYear;
                    break;
                case "Y":
                    $result .= $thaiNumber==1 ? $this->number2thai($year) : $year;
                    break;
                case "U" :
                    $result .= mktime();
                    break;
                case "Z" :
                    $result .= date('z', $date);
                    break;
                case "g":
                case "G":
                case "h":
                case "H":
                case "i":
                case "s":
                case "w":
                    $result .= $thaiNumber==1 ? $this->number2thai(date($subtype,$date)) : date($subtype,$date);
                    break;
                default:
                    $result.=$subtype;
            }
            $subtypeTemp=substr($format,$i,1);
            $i++;
        }
        return $result;
    }

    public function formatPersian($date, $format)
    {
        $persianNumber = Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_created_at_persian_numbers');

        $year = date("Y",$date);
        $month = date("m",$date);
        $day = date("d",$date);

        $i = 0;
        $subtypeTemp = '';
        $result = '';

        list( $Dyear, $Dmonth, $Dday ) = $this->gregorianToPersian($year, $month, $day);

        while($i < strlen($format))
        {
            $subtype=substr($format,$i,1);
            if($subtypeTemp=="\\")
            {
                $result.=$subtype;
                $i++;
                continue;
            }

            switch ($subtype)
            {

                case "A":
                    $result .= (date("a",$date) == "pm") ? $result.= "&#1576;&#1593;&#1583;&#1575;&#1586;&#1592;&#1607;&#1585;" : "&#1602;&#1576;&#1604;&#8207;&#1575;&#1586;&#1592;&#1607;&#1585;";
                    break;
                case "a":
                    $result .= date("a",$date) == "pm" ? $result.= "&#1576;&#46;&#1592;" : "&#1602;&#46;&#1592;";
                    break;
                case "d":
                    $tmpDay = str_pad($Dday, 2, '0', STR_PAD_LEFT);
                    $result .= $persianNumber == 1 ? $this->number2farsi($tmpDay) : $tmpDay;
                    break;
                case "D":
                    $result .= $this->_persianDayWeek[date("D",$date)];
                    break;
                case"F":
                    $result .= $this->_persianMonth[$Dmonth];
                    break;
                case "j":
                    $result .= $persianNumber==1 ? $this->number2farsi($Dday) : $Dday;
                    break;
                case "l":
                    $result .= $this->_persianDayWeek[date("l",$date)];
                    break;
                case "m":
                    $tmpMonth = str_pad($Dmonth, 2, '0', STR_PAD_LEFT);
                    $result .= $persianNumber==1 ? $this->number2farsi($tmpMonth) : $tmpMonth;
                    break;
                case "M":
                    $result .= $this->_persianMonthShort[$Dmonth];
                    break;
                case "n":
                    $result .= $persianNumber==1 ? $this->number2farsi($Dmonth) : $Dmonth;
                    break;
                case "S":
                    $result .= "&#1575;&#1605;";
                    break;
                case "t":
                    $result .= $this->persianLastDay($month,$day,$year);
                    break;
                case "y":
                    $tmpYear = substr($Dyear,2,4);
                    $result .= $persianNumber==1 ? $this->number2farsi($tmpYear) : $tmpYear;
                    break;
                case "Y":
                    $result .= $persianNumber==1 ? $this->number2farsi($Dyear) : $Dyear;
                    break;
                case "U" :
                    $result .= mktime();
                    break;
                case "Z" :
                    $result .= $this->daysOfYear($Dmonth, $Dday, $Dyear);
                    break;
                case "g":
                case "G":
                case "h":
                case "H":
                case "i":
                case "s":
                case "w":
                    $result .= $persianNumber==1 ? $this->number2farsi(date($subtype,$date)) : date($subtype,$date);
                    break;
                default:
                    $result.=$subtype;
            }
            $subtypeTemp=substr($format,$i,1);
            $i++;
        }
        return $result;
    }

    public function gregorianToPersian($gregorianYear, $gregorianMonth, $gregorianDay)
    {
        $y = 0; $m = 0; $day = 0.0;
        $jd = $this->getJulianDay($gregorianYear, $gregorianMonth, $gregorianDay);
        if ($jd > 0.0) {
            $jdm = $jd + 0.5;
            $z = floor($jdm);
            $f = $jdm - $z;
            $jdmp = floor($jd) + 0.5;
            $depoch = $jdmp - $this->getJulianDayFromPersian(475, 1, 1);
            $cycle = floor($depoch / 1029983);
            $cyear = $depoch % 1029983;
            if ($cyear == 1029982) {
                $ycycle = 2820;
            }
            else {
                $a1 = floor($cyear / 366);
                $a2 = $cyear % 366;
                $ycycle = floor(((2134 * $a1) + (2816 * $a2) + 2815) / 1028522) + $a1 + 1;
            }
            $y = $ycycle + (2820 * $cycle) + 474;
            if ($y <= 0) {
                $y--;
            }
            $yday = ($jdmp - $this->getJulianDayFromPersian($y, 1, 1)) + 1;
            $m = ($yday <= 186) ? ceil($yday / 31) : ceil(($yday - 6) / 30);
            $day = ($jdmp - $this->getJulianDayFromPersian($y, $m, 1)) + 1;
        }
        return array($y, $m, $day);
    }

    public function getJulianDayFromPersian($y0, $m0, $d0) {
        $epbase = $y0 - (($y0 >= 0) ? 474 : 473);
        $epyear = 474 + ($epbase % 2820);
        return $d0 + (($m0 <= 7) ? (($m0 - 1) * 31) : ((($m0 - 1) * 30) + 6)) + floor((($epyear * 682) - 110) / 2816) + ($epyear - 1) * 365 + floor($epbase / 2820) * 1029983 + (1948320.5 - 1);
    }

    public function getJulianDay($y0, $m0, $d0) {
        $y = $y0 + 0;
        $m = $m0 + 0;
        $d = $d0 + 0.0;
        /* Determine JD */
        if ($m <= 2) {
            $y = $y - 1;
            $m = $m + 12;
        }
        $a = floor($y / 100);
        $b = 2 - $a + floor($a / 4);
        $jd = floor(365.25 * ($y + 4716)) + floor(30.6001 * ($m + 1)) + $d + $b - 1524.5;
        return $jd;
    }

    public function persianToGregorian($persianYear, $persianMonth, $persianDay)
    {
        $y = 0;
        $m = 0;
        $day = 0.0;
        $jd = $this->getJulianDayFromPersian($persianYear, $persianMonth, $persianDay);
        if ($jd > 0.0) {
            $jdm = $jd + 0.5;
            $z = floor($jdm);
            $f = $jdm - $z;
            $alpha = floor(($z - 1867216.25) / 36524.25);
            $a = $z + 1 + $alpha - floor($alpha / 4);
            $b = $a + 1524;
            $c = floor(($b - 122.1) / 365.25);
            $d = floor(365.25 * $c);
            $e = floor(($b - $d) / 30.6001);
            $day = $b - $d - floor(30.6001 * $e) + $f;
            if ($e < 14) {
                $m = $e - 1;
            }
            else if ($e == 14 || $e == 15) {
                $m = $e - 13;
            }
            if ($m > 2) {
                $y = $c - 4716;
            }
            else if ($m == 1 || $m == 2) {
                $y = $c - 4715;
            }
        }
        return array($y, $m, $day);
    }

    public function number2farsi($number) {
        $output="";
        $len=strlen($number);
        for($sub=0;$sub<$len;$sub++)
        {
            $symbol = substr($number,$sub,1);
            if (isset($this->_persianNumber[$symbol])) {
                $output .= $this->_persianNumber[$symbol];
            } else {
                $output .= $symbol;
            }
        }
        return   $output;
    }

    public function persianLastDay ($month,$day,$year)
    {
        $Dday2="";
        $jdate2 ="";
        $lastdayen=date("d",mktime(0,0,0,$month+1,0,$year));
        list( , , $Dday ) = $this->gregorianToPersian($year, $month, $day);
        $lastdatep = $Dday;
        $Dday = $Dday2;
        while($Dday2!="1")
        {
            if($day<$lastdayen)
            {
                $day++;
                list( , , $Dday2 ) = $this->gregorianToPersian($year, $month, $day);
                if($jdate2=="1") break;
                if($jdate2!="1") $lastdatep++;
            }
            else
            {
                $day=0;
                $month++;
                if($month==13)
                {
                    $month="1";
                    $year++;
                }
            }

        }
        return $lastdatep-1;
    }

    public function daysOfYear($Dmonth, $Dday, $Dyear)
    {
        $result=0;
        if($Dmonth=="01")
            return $Dday;
        for ($i=1;$i<$Dmonth || $i==12;$i++)
        {
            list( $year, $month, $day ) = $this->persianToGregorian($Dyear, $i, "1");
            $result += (int)$this->persianLastDay($month,$day,$year);
        }
        return $result + $Dday;
    }

    public function gregorianToThai($gregorianYear, $gregorianMonth, $gregorianDay)
    {
        return array($gregorianYear + self::THAI_YEAR_INCREMENT, $gregorianMonth, $gregorianDay);
    }

    public function thaiToGregorian($thaiYear, $thaiMonth, $thaiDay)
    {
        return array($thaiYear - self::THAI_YEAR_INCREMENT, $thaiMonth, $thaiDay);
    }

    public function number2thai($number)
    {
        return str_replace($this->_arNumber, $this->_thNumber, $number);
    }
}