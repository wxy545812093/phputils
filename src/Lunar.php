<?php
/**
 * @name 阴、阳历法
 * @author vipkwd <service@vipkwd.com>
 * @link https://github.com/wxy545812093/vipkwd-phputils
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @copyright The PHP-Tools
 */
declare(strict_types = 1);
namespace Vipkwd\Utils;
class Lunar 
{
    //定义公历月分天数
    private static $_SMDay = array(1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    //农历从1950年开始
    private static $_LStart = 1950;
    private static $_LMDay = array(array(47, 29, 30, 30, 29, 30, 30, 29, 29, 30, 29, 30, 29), array(36, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30), array(6, 29, 30, 29, 30, 59, 29, 30, 30, 29, 30, 29, 30, 29), array(44, 29, 30, 29, 29, 30, 30, 29, 30, 30, 29, 30, 29), array(33, 30, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30), array(23, 29, 30, 59, 29, 29, 30, 29, 30, 29, 30, 30, 30, 29), array(42, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30), array(30, 30, 29, 30, 29, 30, 29, 29, 59, 30, 29, 30, 29, 30), array(48, 30, 30, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30), array(38, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29), array(27, 30, 29, 30, 29, 30, 59, 30, 29, 30, 29, 30, 29, 30), array(45, 30, 29, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30), array(35, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29), array(24, 30, 29, 30, 58, 30, 29, 30, 29, 30, 30, 30, 29, 29), array(43, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 30), array(32, 29, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29), array(20, 30, 30, 59, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30), array(39, 30, 30, 29, 30, 30, 29, 29, 30, 29, 30, 29, 30), array(29, 29, 30, 29, 30, 30, 29, 59, 30, 29, 30, 29, 30, 30), array(47, 29, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29), array(36, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30), array(26, 29, 30, 29, 29, 59, 30, 29, 30, 30, 30, 29, 30, 30), array(45, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 29, 30), array(33, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30), array(22, 30, 30, 29, 59, 29, 30, 29, 29, 30, 30, 29, 30, 30), array(41, 30, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30), array(30, 30, 30, 29, 30, 29, 30, 29, 59, 29, 30, 29, 30, 30), array(48, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 29), array(37, 30, 29, 30, 30, 29, 30, 30, 29, 30, 29, 30, 29), array(27, 30, 29, 29, 30, 29, 60, 29, 30, 30, 29, 30, 29, 30), array(46, 30, 29, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30), array(35, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30, 30), array(24, 30, 29, 30, 58, 30, 29, 29, 30, 29, 30, 30, 30, 29), array(43, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30), array(32, 30, 29, 30, 30, 29, 29, 30, 29, 29, 59, 30, 30, 30), array(50, 29, 30, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30), array(39, 29, 30, 30, 29, 30, 30, 29, 30, 29, 30, 29, 29), array(28, 30, 29, 30, 29, 30, 59, 30, 30, 29, 30, 29, 29, 30), array(47, 30, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29), array(36, 30, 29, 29, 30, 29, 30, 29, 30, 29, 30, 30, 30), array(26, 29, 30, 29, 29, 59, 29, 30, 29, 30, 30, 30, 30, 30), array(45, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 30), array(34, 29, 30, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30), array(22, 29, 30, 59, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30), array(40, 30, 30, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30), array(30, 29, 30, 30, 29, 30, 29, 30, 59, 29, 30, 29, 30, 30), array(49, 29, 30, 29, 30, 30, 29, 30, 29, 30, 30, 29, 29), array(37, 30, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29), array(27, 30, 29, 29, 30, 58, 30, 30, 29, 30, 30, 29, 30, 29), array(46, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 30, 29), array(35, 30, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 29), array(23, 30, 30, 29, 59, 30, 29, 29, 30, 29, 30, 29, 30, 30), array(42, 30, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29), array(31, 30, 30, 29, 30, 30, 29, 30, 29, 29, 30, 29, 30), array(21, 29, 59, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30, 30), array(39, 29, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29), array(28, 30, 29, 30, 29, 30, 29, 59, 30, 30, 29, 30, 30, 30), array(48, 29, 29, 30, 29, 29, 30, 29, 30, 30, 30, 29, 30), array(37, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30), array(25, 30, 30, 29, 29, 59, 29, 30, 29, 30, 29, 30, 30, 30), array(44, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30), array(33, 30, 29, 30, 30, 29, 30, 29, 29, 30, 29, 30, 29), array(22, 30, 29, 30, 59, 30, 29, 30, 29, 30, 29, 30, 29, 30), array(40, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30), array(30, 29, 30, 29, 30, 29, 30, 29, 30, 59, 30, 29, 30, 30), array(49, 29, 30, 29, 29, 30, 29, 30, 30, 30, 29, 30, 29), array(38, 30, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30), array(27, 29, 30, 29, 30, 29, 59, 29, 30, 29, 30, 30, 30, 29), array(46, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30), array(35, 30, 29, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30), array(24, 29, 30, 30, 59, 30, 29, 29, 30, 29, 30, 29, 30, 30), array(42, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29), array(31, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30), array(21, 29, 59, 29, 30, 30, 29, 30, 30, 29, 30, 29, 30, 30), array(40, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29), array(28, 30, 29, 30, 29, 29, 59, 30, 29, 30, 30, 30, 29, 30), array(47, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 30, 29), array(36, 30, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29), array(25, 30, 30, 30, 29, 59, 29, 30, 29, 29, 30, 30, 29, 30), array(43, 30, 30, 29, 30, 29, 30, 29, 30, 29, 29, 30, 30), array(33, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29), array(22, 29, 30, 59, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30), array(41, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30), array(30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 59, 30, 30), array(49, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 29, 30), array(38, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30), array(27, 30, 30, 29, 30, 29, 59, 29, 29, 30, 29, 30, 30, 29), array(45, 30, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30), array(34, 30, 30, 29, 30, 29, 30, 29, 30, 29, 29, 30, 29), array(23, 30, 30, 29, 30, 59, 30, 29, 30, 29, 30, 29, 29, 30), array(42, 30, 29, 30, 30, 29, 30, 29, 30, 30, 29, 30, 29), array(31, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30), array(21, 29, 59, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 30), array(40, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30, 30), array(29, 30, 29, 30, 29, 29, 30, 58, 30, 29, 30, 30, 30, 29), array(47, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30), array(36, 30, 29, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30), array(25, 30, 29, 30, 30, 59, 29, 30, 29, 29, 30, 29, 30, 29), array(44, 29, 30, 30, 29, 30, 30, 29, 30, 29, 29, 30, 29), array(32, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30, 29), array(22, 29, 30, 59, 29, 30, 29, 30, 30, 29, 30, 30, 29, 29));
    
    private static $_YearList = array(
        0x04bd8,0x04ae0,0x0a570,0x054d5,0x0d260,0x0d950,0x16554,0x056a0,0x09ad0,0x055d2,//1900-1909
        0x04ae0,0x0a5b6,0x0a4d0,0x0d250,0x1d255,0x0b540,0x0d6a0,0x0ada2,0x095b0,0x14977,//1910-1919
        0x04970,0x0a4b0,0x0b4b5,0x06a50,0x06d40,0x1ab54,0x02b60,0x09570,0x052f2,0x04970,//1920-1929
        0x06566,0x0d4a0,0x0ea50,0x06e95,0x05ad0,0x02b60,0x186e3,0x092e0,0x1c8d7,0x0c950,//1930-1939
        0x0d4a0,0x1d8a6,0x0b550,0x056a0,0x1a5b4,0x025d0,0x092d0,0x0d2b2,0x0a950,0x0b557,//1940-1949
        0x06ca0,0x0b550,0x15355,0x04da0,0x0a5b0,0x14573,0x052b0,0x0a9a8,0x0e950,0x06aa0,//1950-1959
        0x0aea6,0x0ab50,0x04b60,0x0aae4,0x0a570,0x05260,0x0f263,0x0d950,0x05b57,0x056a0,//1960-1969
        0x096d0,0x04dd5,0x04ad0,0x0a4d0,0x0d4d4,0x0d250,0x0d558,0x0b540,0x0b6a0,0x195a6,//1970-1979
        0x095b0,0x049b0,0x0a974,0x0a4b0,0x0b27a,0x06a50,0x06d40,0x0af46,0x0ab60,0x09570,//1980-1989
        0x04af5,0x04970,0x064b0,0x074a3,0x0ea50,0x06b58,0x055c0,0x0ab60,0x096d5,0x092e0,//1990-1999

        0x0c960,0x0d954,0x0d4a0,0x0da50,0x07552,0x056a0,0x0abb7,0x025d0,0x092d0,0x0cab5,//2000-2009
        0x0a950,0x0b4a0,0x0baa4,0x0ad50,0x055d9,0x04ba0,0x0a5b0,0x15176,0x052b0,0x0a930,//2010-2019
        0x07954,0x06aa0,0x0ad50,0x05b52,0x04b60,0x0a6e6,0x0a4e0,0x0d260,0x0ea65,0x0d530,//2020-2029
        0x05aa0,0x076a3,0x096d0,0x04bd7,0x04ad0,0x0a4d0,0x1d0b6,0x0d250,0x0d520,0x0dd45,//2030-2039
        0x0b5a0,0x056d0,0x055b2,0x049b0,0x0a577,0x0a4b0,0x0aa50,0x1b255,0x06d20,0x0ada0,//2040-2049
        0x14b63,0x09370,0x049f8,0x04970,0x064b0,0x168a6,0x0ea50, 0x06b20,0x1a6c4,0x0aae0,//2050-2059
        0x0a2e0,0x0d2e3,0x0c960,0x0d557,0x0d4a0,0x0da50,0x05d55,0x056a0,0x0a6d0,0x055d4,//2060-2069
        0x052d0,0x0a9b8,0x0a950,0x0b4a0,0x0b6a6,0x0ad50,0x055a0,0x0aba4,0x0a5b0,0x052b0,//2070-2079
        0x0b273,0x06930,0x07337,0x06aa0,0x0ad50,0x14b55,0x04b60,0x0a570,0x054e4,0x0d160,//2080-2089
        0x0e968,0x0d520,0x0daa0,0x16aa6,0x056d0,0x04ae0,0x0a9d4,0x0a2d0,0x0d150,0x0f252,//2090-2099

        0x0d520 //2100
    );

    /**
     * 公历转农历(Sdate：公历日期)
     * 
     * -e.g: phpunit("Lunar::S2L", ["20190423"]);
     * -e.g: phpunit("Lunar::S2L", ["20200423"]);
     * -e.g: phpunit("Lunar::S2L", ["2019-04-23"]);
     * -e.g: phpunit("Lunar::S2L", ["2019/04/23"]);
     * -e.g: phpunit("Lunar::S2L", ["2019年04月23日"]);
     * -e.g: phpunit("Lunar::S2L", ["1989-02-21"]);
     * -e.g: phpunit("Lunar::S2L", ["1989-02-11"]);
     *
     * @param string $date  格式：Y([^0-9]+)?m([^0-9]+)?d([^0-9]+)?
     * @return void
     */
    static function S2L(string $date)
    {
        list($year, $month, $day) = self::dateFixes($date, true);
        if ($year <= 1951 || $month <= 0 || $day <= 0 || $year >= 2051) {
            return false;
        }
        //获取查询日期到当年1月1日的天数
        $date1 = strtotime($year . "-01-01");
        //当年1月1日
        $date2 = strtotime($year . "-" . $month . "-" . $day);
        $days = round(($date2 - $date1) / 3600 / 24);
        $days += 1;
        //获取相应年度农历数据，化成数组Larray
        $Larray = self::$_LMDay[$year - self::$_LStart];
        if ($days <= $Larray[0]) {
            $Lyear = $year - 1;
            $days = $Larray[0] - $days;
            $Larray = self::$_LMDay[$Lyear - self::$_LStart];
            if ($days < $Larray[12]) {
                $Lmonth = 12;
                $Lday = $Larray[12] - $days;
            } else {
                $Lmonth = 11;
                $days = $days - $Larray[12];
                $Lday = $Larray[11] - $days;
            }
        } else {
            $Lyear = $year;
            $days = $days - $Larray[0];
            for ($i = 1; $i <= 12; $i++) {
                if ($days > $Larray[$i]) {
                    $days = $days - $Larray[$i];
                } else {
                    if ($days > 30) {
                        $days = $days - $Larray[13];
                        $Ltype = 1;
                    }
                    $Lmonth = $i;
                    $Lday = $days;
                    break;
                }
            }
        }
        // return mktime(0, 0, 0, intval($Lmonth), intval($Lday), intval($Lyear));
        $Ldate = $Lyear."-".$Lmonth."-".$Lday;
        $Ldate = self::LYearName($Lyear)."年".self::LMonName($Lmonth)."月".self::LDayName($Lday);
        if( 0 < self::getLeapMonth($year)) $Ldate.="(闰)";
        return $Ldate;
    }
    /**
     * 农历转公历(date:农历日期;type:是否闰月)
     * 
     * -e.g: phpunit("Lunar::L2S", ["1989-01-16"]);
     * 
     * @param [type] $date
     * @param integer $type
     * @return void
     */
    static function L2S($date, $type = 0)
    {
        list($year, $month, $day) = explode("-", $date);
        if ($year <= 1951 || $month <= 0 || $day <= 0 || $year >= 2051) {
            return false;
        }
        $Larray = self::$_LMDay[$year - self::$_LStart];
        if ($type == 1 && count($Larray) <= 12) {
            return false;
        }
        //要求查询闰，但查无闰月
        //如果查询的农历是闰月并该年度农历数组存在闰月数据就获取
        if ($Larray[$month] > 30 && $type == 1 && count($Larray) >= 13) {
            $day = $Larray[13] + $day;
        }
        //获取该年农历日期到公历1月1日的天数
        $days = $day;
        for ($i = 0; $i <= $month - 1; $i++) {
            $days += $Larray[$i];
        }
        //当查询农历日期距离公历1月1日超过一年时
        if ($days > 366 || self::GetSMon($month, 2) != 29 && $days > 365) {
            $Syear = $year + 1;
            if (self::GetSMon($month, 2) != 29) {
                $days -= 366;
            } else {
                $days -= 365;
            }
            if ($days > self::$_SMDay[1]) {
                $Smonth = 2;
                $Sday = $days - self::$_SMDay[1];
            } else {
                $Smonth = 1;
                $Sday = $days;
            }
        } else {
            $Syear = $year;
            for ($i = 1; $i <= 12; $i++) {
                if ($days > self::GetSMon($Syear, $i)) {
                    $days -= self::GetSMon($Syear, $i);
                } else {
                    $Smonth = $i;
                    $Sday = $days;
                    break;
                }
            }
        }
        // return mktime(0, 0, 0, intval($Smonth), intval($Sday), intval($Syear));
        $Sdate = $Syear."-".str_pad("$Smonth",2,"0",STR_PAD_LEFT)."-".str_pad("$Sday",2,"0",STR_PAD_LEFT);
        return $Sdate;
    }

    /**
     * 获取干支纪年
     * 
     * -e.g: phpunit("Lunar::getYearGZ",["2019"]);
     * -e.g: phpunit("Lunar::getYearGZ",[2020]);
     * 
     * @param string $year
     */
    static function getYearGZ($year){
        list($year,,)=self::dateFixes($year);
        $tg = ["癸", "甲", "乙", "丙", "丁", "戊", "己", "庚", "辛", "任"];
        $dz = ["亥", "子", "丑", "寅", "卯", "辰", "巳", "午", "未", "申", "酉", "戌"];
        $year = $year . '';
        $tgIdx = ($year - 3) % 10;
        $dzIdx = ($year - 3) % 12;
        return $tg[$tgIdx] . $dz[$dzIdx];
    }

    /**
     * 根据阴历年获取生肖
     * 
     * -e.g: phpunit("Lunar::getYearZodiac",["2019"]);
     * -e.g: phpunit("Lunar::getYearZodiac",[2020]);
     * 
     * @param string $year 阴历年
     */
    static function getYearZodiac($year){
        list($year,,)=self::dateFixes($year);
        $zodiac = array('猪', '鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊','猴', '鸡', '狗');
        $year = $year . '';
        $zx = ($year - 3) % 12;
        return $zodiac[$zx];
    }


    /**
     * 获取星座
     *
     * -e.g: phpunit("Lunar::getConstellation",["2019-04-24"]);
     * -e.g: phpunit("Lunar::getConstellation",["2019/04/24"]);
     * -e.g: phpunit("Lunar::getConstellation",["2019.04.24"]);
     * -e.g: phpunit("Lunar::getConstellation",["2019年04月24日"]);
     * -e.g: phpunit("Lunar::getConstellation",["20190424"]);
     * 
     * @param string $date
     * @return string
     */
    static function getConstellation(string $date):string{
        list(, $month, $day) = self::dateFixes($date);

        $date = 1 * bcadd(strval($month * 1), "0.{$day}", 2);

        if (( $date >= 1.20) || ($date <= 2.18)) {
            return "水瓶座";
        } else if (($date >= 2.19) || ($date <= 3.20)) {
            return "双鱼座";
        } else if (($date >= 3.21) || ($date <= 4.19)) {
            return "白羊座";
        } else if (($date >= 4.20) || ($date <= 5.20)) {
            return "金牛座";
        } else if (($date >= 5.21) || ($date <= 6.21)) {
            return "双子座";
        } else if (($date >= 6.22) || ($date <= 7.22)) {
            return "巨蟹座";
        } else if (($date >= 7.23) || ($date <= 8.22)) {
            return "狮子座";
        } else if (($date >= 8.23) || ($date <= 9.22)) {
            return "处女座";
        } else if (($date >= 9.23) || ($date <= 10.23)) {
            return "天秤座";
        } else if (($date >= 10.24) || ($date <= 11.22)) {
            return "天蝎座";
        } else if (($date >= 11.23) || ($date <= 12.21)) {
            return "射手座";
        } else if (($date >= 12.22) || ($date <= 1.19)) {
            return "魔羯座";
        }
        return "";
    }

    /**
     * 获取阳历月份的天数
     * 
     * -e.g: phpunit("Lunar::getSolarMonthDays",["2019","4"]);
     * -e.g: phpunit("Lunar::getSolarMonthDays",["2019","04"]);
     * 
     * @param string $year 阳历-年
     * @param string $month 阳历-月
     * @return int
     */
    static function getSolarMonthDays($year, $month):int{
        $monthHash = array('1' => 31, '2' => self::isLeapYear($year) ? 29 : 28, '3' => 31, '4' => 30, '5' => 31, '6' => 30, '7' => 31, '8' => 31, '9' => 30, '10' => 31, '11' => 30, '12' => 31);
        $month *=1;
        return $monthHash["$month"];
    }

    /**
     * 获取公历对应的农历闰月天数
     * 
     * - 返回0 无闰月
     *
     * -e.g: phpunit("Lunar::getLeapMonthDays",["2020"]);
     * -e.g: phpunit("Lunar::getLeapMonthDays",["2019"]);
     * -e.g: phpunit("Lunar::getLeapMonthDays",["2018"]);
     * -e.g: phpunit("Lunar::getLeapMonthDays",["2017"]);
     * 
     * 
     * @param string $year
     * @return int
     */
    static function getLeapMonthDays($year){
       if(self::getLeapMonth($year) > 0){
            $year = 1 * substr(strval($year),0,4) - 1900;
            return (self::$_YearList[$year] & 0x10000) ? 30: 29;
       } else {
           return 0;
       }
    }

    /**
     * 获取公历对应的农历闰月
     * 
     * - 返回0 无闰月
     *
     * -e.g: phpunit("Lunar::getLeapMonth",["2020"]);
     * -e.g: phpunit("Lunar::getLeapMonth",["2019"]);
     * -e.g: phpunit("Lunar::getLeapMonth",["2018"]);
     * -e.g: phpunit("Lunar::getLeapMonth",["2017"]);
     * 
     * @param [type] $year
     * @return int
     */
    static function getLeapMonth($year){
        $year = 1 * substr(strval($year),0,4) - 1900;
        return (self::$_YearList[$year] & 0xf);
    }

    /**
     * 日历综合方法
     *
     * -e.g: phpunit("Lunar::getDateLunar",["2019-04-23"]);
     * 
     * @param string $date
     * @return array
     */
    static function getDateLunar(string $date):array{
        list($year, $month, $day) = self::dateFixes($date);
        return [
            "origin" => $date,
            "leapMonth" => self::getLeapMonth($year),
            "leapMonthDays" => self::getLeapMonthDays($year),
            "leapYear" => self::isLeapYear($year) ? 1 : 0,
            "solarMonthDays" => self::getSolarMonthDays($year, $month),
            "constellation" => self::getConstellation( implode("-",[$year, $month, $day]) ),
            "yearZodiac"=> self::getYearZodiac($year),
            "yearGZ"=> self::getYearGZ($year),
            "l2s"=> self::L2S( implode("-",[$year, $month, $day]) ),
            "s2l"=> self::S2L( implode("-",[$year, $month, $day]) ),
        ];
    }

    private static function dateFixes($date, bool $num = false):array{
        $date = preg_replace("/[^0-9]/",'', $date);
        $year = substr($date,0,4);
        $month = $day = "01";
        if(strlen($date) == 8){
            $day = substr($date, -2);
            $month = substr($date, 4,2);
        }
        if($num){
            $year *= 1;
            $month *= 1;
            $day *= 1;
        }
        return [$year, $month, $day];
    }


    //是否闰年
    private static function isLeapYear($year){
        $year *=1;
        return (($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0));
    }
    /**
     * 公历该月的天数(year：年份； month：月份)
     *
     * @param [type] $year
     * @param [type] $month
     * @return void
     */
    private static function GetSMon($year, $month)
    {
        if (self::IsLeapYear($year) && $month == 2) {
            return 29;
        } else {
            return self::$_SMDay[$month];
        }
    }


    /**
     * 农历名称转换
     *
     * @param [type] $year
     * @return void
     */
    private static function LYearName($year)
    {
        $year.= '';
        $chars = array("零", "一", "二", "三", "四", "五", "六", "七", "八", "九");
        $tmp="";
        for ($i = 0; $i < 4; $i++) {
            $tmp .= $chars[($year[$i])];
        }
        return $tmp;
    }

    /**
     * 数字转农历月份
     *
     * @param [type] $month
     * @return void
     */
    private static function LMonName($month)
    {
        if ($month >= 1 && $month <= 12) {
            $Name = [1 => "正", "二", "三", "四", "五", "六", "七", "八", "九", "十", "冬", "腊"];
            return $Name[$month];
        }
        return $month;
    }
    /**
     * 获取数字的阴历叫法
     *
     * @param [type] $day
     * @return void
     */
    private static function LDayName($day)
    {
        if ($day >= 1 && $day <= 30) {
            $n = ["十","一", "二", "三", "四", "五", "六", "七", "八", "九"];
            $s = ["初","十","廿", "三"];
            $m = $day % 10;
            $p = intval($day / 10);
            return $s[$p] . $n[$m];
            // $Name = array(1 => "初一", "初二", "初三", "初四", "初五", "初六", "初七", "初八", "初九", "初十", "十一", "十二", "十三", "十四", "十五", "十六", "十七", "十八", "十九", "二十", "廿一", "廿二", "廿三", "廿四", "廿五", "廿六", "廿七", "廿八", "廿九", "三十");
            // return $Name[$day];
        }
        return $day;
    }

}

// header("Content-Type:text/html;charset=utf-8");
// $lunar = new Lunar();
// $month = $lunar->convertSolarToLunar('2017', '03', '09');//将阳历转换为阴历
// echo '<pre>';
// print_r($month);