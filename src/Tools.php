<?php

/**
 * @name 常用工具集合
 * @author devkeep <devkeep@skeep.cc>
 * @author vipkwd <service@vipkwd.com>
 * @link https://github.com/aiqq363927173/Tools
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @copyright The PHP-Tools
 */

declare(strict_types=1);

namespace Vipkwd\Utils;

// use Vipkwd\Utils\System\Cookie;
// use Vipkwd\Utils\System\Session;
// use Vipkwd\Utils\Type\Arr;
use Vipkwd\Utils\Type\Random,
    Vipkwd\Utils\Libs\ExpressAI\Address as ExpressAddressAI_V1,
    Vipkwd\Utils\Libs\SmartParsePro\Address as ExpressAddressAI_V2,
    PHPMailer\PHPMailer\PHPMailer,
    Vipkwd\Utils\Type\Str as VipkwdStr,
    \Exception,
    // Vipkwd\Utils\Libs\QRcode,
    // Vipkwd\Utils\Validate,
    // Vipkwd\Utils\System\Store,
    \Closure;


class Tools
{
    // use \Vipkwd\Utils\Libs\Develop;

    /**
     * 判断当前的运行环境是否是cli模式
     *
     * -e.g: phpunit("Tools::isCli");
     *
     * @return boolean
     */
    static function isCli()
    {
        return Dev::isCli();
    }

    /**
     * MD5|16位
     * -e.g: echo "\md5(\"admin\"); //".md5('admin');
     * -e.g: phpunit("Tools::md5_16",["admin"]);
     *
     * @param string $str
     * @return string
     */
    static function md5_16(string $str): string
    {
        return VipkwdStr::md5_16($str);
    }
    /**
     * 生成UUID
     *
     * -e.g: phpunit("Tools::uuid");
     * -e.g: phpunit("Tools::uuid",[false, "前缀：仅支持英文字符与数字"]);
     * -e.g: phpunit("Tools::uuid",[false, "99"]);
     * -e.g: phpunit("Tools::uuid",[true]);
     * -e.g: phpunit("Tools::uuid",[true, "0000"]);
     * -e.g: phpunit("Tools::uuid",[true, "00000000000000"]);
     *
     * @param bool $toUppercase <false>
     * @param string $prefix 前缀：仅支持英文字符与数字 <"">
     * @param string $separator 分隔符 <"-">
     * @return string
     */
    static function uuid(bool $toUppercase = false, string $prefix = '', string $separator = "-"): string
    {
        return VipkwdStr::uuid($toUppercase, $prefix, $separator);
    }

    /**
     * PHP代码格式化
     * 
     * @param string $data 文件PATH或代码字符串
     * @return string|null;
     */
    static function formatPhpCode(string $data): ?string
    {
        if (is_file($data)) {
            $file = $data;
        } else {
            $file = __DIR__ . '/' . date('YmdHis') . mt_rand(1000, 9999) . '.php';
            file_put_contents($file, $data);
            unset($data);
        }
        if (is_dir(VIPKWD_UTILS_LIB_ROOT . '/vendor')) {
            $vendor = VIPKWD_UTILS_LIB_ROOT;
        } elseif (is_dir(realpath(VIPKWD_UTILS_LIB_ROOT . '/../../../vendor'))) {
            $vendor = realpath(VIPKWD_UTILS_LIB_ROOT . '/../../../');
        } else {
            exit('php-cs-fixer 插件缺失');
        }
        @exec("php {$vendor}/vendor/bin/php-cs-fixer fix {$file} --quiet --allow-risky=yes --rules=@Symfony,@PSR12,-full_opening_tag,-indentation_type,-blank_line_before_statement,strict_comparison", $resArr, $state);
        if (!isset($data)) {
            $data = file_get_contents($file);
            @unlink($file);
            return $data;
        }
        return null;
    }

    /**
     * 获取文件夹大小
     *
     * -e.g: phpunit("Tools::getDirSize",["./"]);
     *
     * @param string $dir
     * @return float
     */
    static function getDirSize(string $dir): float
    {
        if (!is_dir($dir)) {
            return "\"$dir\" is not a directory";
        }
        static $sizeResult = 0;
        $handle = opendir($dir);
        while (false !== ($FolderOrFile = readdir($handle))) {
            if ($FolderOrFile != "." && $FolderOrFile != "..") {
                if (is_dir("$dir/$FolderOrFile")) {
                    $sizeResult += self::getDirSize("$dir/$FolderOrFile");
                } else {
                    $sizeResult += filesize("$dir/$FolderOrFile");
                }
            }
        }
        closedir($handle);
        return round(($sizeResult / 1048576), 2);
    }

    /**
     * 获取系统类型
     *
     * -e.g: phpunit("Tools::getOS");
     *
     * @return string
     */
    static function getOS(): string
    {
        if (PATH_SEPARATOR == ':') {
            return 'Linux';
        } else {
            return 'Windows';
        }
    }

    /**
     * format 保留指定长度小数位
     *
     * -e.g: phpunit("Tools::format", [ "10.1234" ]);
     * -e.g: phpunit("Tools::format", [ 10.12 ]);
     * -e.g: phpunit("Tools::format", [ 10.1 ]);
     * -e.g: phpunit("Tools::format", [ 10 ]);
     * -e.g: phpunit("Tools::format", [-10]);
     * -e.g: phpunit("Tools::format", ["-10", 3]);
     *
     * @param int $input 数值
     * @param int $decimal <2> 小数位数
     *
     * @return string
     */
    static function format($input, int $decimal = 2): string
    {
        return sprintf("%." . $decimal . "f", $input);
    }

    /**
     * mt_rand增强版（兼容js版Math.random)
     *
     * -e.g: phpunit("Tools::mathRandom",[0,1, 15]);
     * -e.g: phpunit("Tools::mathRandom",[0,5,0]);
     * -e.g: phpunit("Tools::mathRandom",[0,5,1]);
     * -e.g: phpunit("Tools::mathRandom",[0,5,4]);
     * -e.g: phpunit("Tools::mathRandom",[0,5,6]);
     *
     * @param integer $min
     * @param integer $max
     * @param integer $decimal <0> 小数位数
     * @return string
     */
    static function mathRandom(int $min = 0, int $max = 1, int $decimal = 0)
    {
        $decimal = $decimal === true ? 10 : $decimal;
        return Random::float($min, $max, $decimal);
    }

    /**
     * 扫描目录（递归）
     *
     * -e.g: phpunit("Tools::dirScan", ["../vipkwd-utils/src/Libs/Image/", function($file){ var_dump($file);}]);
     *
     * @param string $dir
     * @param callable|null $fileCallback
     *                      以匿名回调方式对扫描到的文件处理；
     *                      匿名函数接收俩个参数： function($scanFile, $scanPath);
     *                      当匿名函数 return === false 时，将退出本函数所有层次的递归模式
     * @return boolean|null
     */
    static function dirScan(string $dir, ?callable $fileCallback = null): ?bool
    {
        if (!is_dir($dir)) {
            return null;
        }
        $return = null;
        $fd = opendir($dir);
        while (false !== ($file = readdir($fd))) {
            if ($file != "." && $file != "..") {
                if (is_dir($dir . "/" . $file)) {
                    $return = self::dirScan($dir . "/" . $file, $fileCallback);
                } else {
                    if (is_callable($fileCallback)) {
                        $return = $fileCallback($file, $dir);
                    }
                }
                if ($return === false) {
                    break;
                }
            }
        }
        @closedir($fd);
        return $return;
    }

    /**
     * 打印目录文件列表
     *
     * -e.g: phpunit("Tools::dirTree", ["../vipkwd-utils/src/Libs/Image"]);
     *
     * @param string $dir
     * @return void
     */
    static function dirTree(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }
        $dir = rtrim($dir, "/");
        $path = array();
        $stack = array($dir);
        while ($stack) {
            $thisdir = array_pop($stack);
            if ($dircont = scandir($thisdir)) {
                $i = 0;
                while (isset($dircont[$i])) {
                    if ($dircont[$i] !== '.' && $dircont[$i] !== '..') {
                        $current_file = $thisdir . DIRECTORY_SEPARATOR . $dircont[$i];
                        if (is_file($current_file)) {
                            $path[] = "f:" . $thisdir . DIRECTORY_SEPARATOR . $dircont[$i];
                        } elseif (is_dir($current_file)) {
                            $path[] = "d:" . $thisdir . DIRECTORY_SEPARATOR . $dircont[$i];
                            $stack[] = $current_file;
                        }
                    }
                    $i++;
                }
            }
        }
        return $path;
    }

    /**
     * 发送邮件
     *
     * @param array  $form 发件人信息 [host,port,username,password,address,title]
     * @param array  $data 收件人信息 [subject,body,mail,name,attachment]
     * 
     * // SMTP服务器: form.host
     * // SMTP端口号: form.port
     * // SMTP用户名: form.username
     * // SMTP授权码: form.password
     * 
     *   // $mail->Port = 465;                                        // SMTP服务器的端口号
     *   // $mail->Username = "devkeep@aliyun.com";                   // SMTP服务器用户名
     *   // $mail->Password = "xxxxxxxxxxxx";                         // SMTP服务器密码
     *   // $mail->SetFrom('devkeep@aliyun.com', '项目完成通知');
     * 
     * 
     *
     * @return mixed
     */
    static public function sendMail(array $form, array $data)
    {
        $mail = new PHPMailer(true);       // 实例化PHPMailer对象
        $mail->CharSet = 'UTF-8';                               // 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->isSMTP();                                        // 设定使用SMTP服务
        $mail->SMTPDebug = 0;                                   // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
        $mail->SMTPAuth = true;                                 // 启用 SMTP 验证功能
        $mail->SMTPSecure = 'ssl';                              // 使用安全协议
        $mail->isHTML(true);

        // 发件人信息
        $mail->Host = $form['host'];                            // SMTP 服务器
        $mail->Port = $form['port'];                            // SMTP服务器的端口号
        $mail->Username = $form['username'];                    // SMTP服务器用户名
        $mail->Password = $form['password'];                    // SMTP服务器密码(授权码优先)
        $mail->SetFrom($form['address'], $form['title']);

        // 阿里云邮箱
        // $mail->Host = "smtp.aliyun.com";                          // SMTP 服务器
        // $mail->Port = 465;                                        // SMTP服务器的端口号
        // $mail->Username = "devkeep@aliyun.com";                   // SMTP服务器用户名
        // $mail->Password = "xxxxxxxxxxxx";                         // SMTP服务器密码
        // $mail->SetFrom('devkeep@aliyun.com', '项目完成通知');

        // 网易邮箱
        // $mail->Host = "smtp.163.com";                           // SMTP 服务器
        // $mail->Port = 465;                                      // SMTP服务器的端口号
        // $mail->Username = "devkeep@163.cc";                     // SMTP服务器用户名
        // $mail->Password = "xxxxxxxxx";                          // SMTP服务器密码
        // $mail->SetFrom('devkeep@163.cc', '系统通知');

        // QQ邮箱
        // $mail->Host = "smtp.qq.com";                            // SMTP 服务器
        // $mail->Port = 465;                                      // SMTP服务器的端口号
        // $mail->Username = "363927173@qq.com";                   // SMTP服务器用户名
        // $mail->Password = "xxxxxxxxxxxxxxxx";                   // SMTP服务器密码
        // $mail->SetFrom('devkeep@skeep.cc', '管理系统');

        // 设置发件人昵称 显示在收件人邮件的发件人邮箱地址前的发件人姓名
        $mail->FromName =  $form['nickname'] ?? $form['address'];
        // 设置发件人邮箱地址 同登录账号
        $mail->From = $form['address'];

        // 添加该邮件的主题
        $mail->Subject = $data['subject'];
        // 添加邮件正文
        $mail->MsgHTML($data['body']);
        // 收件人信息
        // 设置收件人邮箱地址(添加多个收件人 则多次调用方法即可)
        $mail->AddAddress($data['mail'], $data['name']);
        // $mail->addAddress('xxxxxx@163.com');

        // 是否携带附件
        if (isset($data['attachment']) && is_array($data['attachment'])) {
            foreach ($data['attachment'] as $file) {
                is_file($file) && $mail->AddAttachment($file);
            }
        }
        return $mail->Send() ? true : $mail->ErrorInfo;
    }

    /**
     * 获取配置文件内容
     *
     * $key 支持“.”号深度访问数组 如："db.mysql.host"
     *
     * @param string $key
     * @param string $confDir 配置文件所在目录
     * @param string $confSuffix 配置文件后缀 <php>
     *
     * @return mixed
     */
    static function config(string $key, string $confDir, string $confSuffix = "php")
    {
        static $__config_;
        !is_array($__config_) && $__config_ = [];
        $key = str_replace(' ', '', $key);
        $key = trim($key, ".");
        $l = explode('.', $key);
        if (!isset($__config_[($l[0])])) {
            $f =  rtrim($confDir, "/") . "/{$l[0]}." . ltrim($confSuffix, ".");
            file_exists($f) && $__config_[($l[0])] = require_once($f);
            unset($f);
        }
        $r = $__config_[($l[0])];
        unset($l[0]);
        foreach ($l as $conf_arr_key) {
            if (is_array($r) && isset($r[$conf_arr_key])) {
                $r = $r[$conf_arr_key];
            } else {
                $r = NULL;
                break;
            }
            unset($conf_arr_key);
        }
        unset($key, $l);
        return $r;
    }


    /**
     * 获取Http头信息为数组
     *
     * -e.g: phpunit("Tools::getHttpHeaders");
     *
     * 获取 $_SERVER 所有以“HTTP_” 开头的 头信息
     *
     * @return array
     */
    static function getHttpHeaders(): array
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $key = substr($key, 5);
                $key = strtolower($key);
                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    /**
     * 保密手机号码
     *
     * -e.g: phpunit("Tools::encryptMobile", ["13844638829"]);
     *
     * @param string $mobile
     * @return string
     */
    static function encryptMobile(string $mobile): string
    {
        return preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $mobile);
    }

    /**
     * 快递地址智能解析(提取)
     *
     * -e.g: $list=[];
     * -e.g: $list[]="北京市东城区宵云路36号国航大厦一层";
     * -e.g: $list[]="甘肃省东乡族自治县布楞沟村1号";
     * -e.g: $list[]="成都市双流区宵云路36号国航大厦一层";
     * -e.g: $list[]="内蒙古乌兰察布市公安局交警支队车管所";
     * -e.g: $list[]="内蒙乌兰察布市公安局交警支队车管所";
     * -e.g: $list[]="内蒙古自治区乌兰察布市公安局交警支队车管所";
     * -e.g: $list[]="内蒙自治区乌兰察布市公安局交警支队车管所";
     * -e.g: $list[]="长春市朝阳区宵云路36号国航大厦一层";
     * -e.g: $list[]="成都市武侯区高新区天府软件园B区科技大楼";
     * -e.g: $list[]="双流区正通路社保局区52050号";
     * -e.g: $list[]="岳阳市岳阳楼区南湖求索路碧灏花园A座1101";
     * -e.g: $list[]="四川省 凉山州美姑县东方网肖小区18号院";
     * -e.g: $list[]="四川攀枝花市东区机场路3中学校";
     * -e.g: $list[]="渝北区渝北中学51200街道地址";
     * -e.g: $list[]="13566892356天津天津市红桥区水木天成1区临湾路9-3-1101";
     * -e.g: $list[]="苏州市昆山市青阳北路时代名苑20号311室";
     * -e.g: $list[]="崇州市崇阳镇金鸡万人小区兴盛路105-107";
     * -e.g: $list[]="四平市双辽市辽北街道";
     * -e.g: $list[]="梧州市奥奇丽路10-9号A幢地层（礼迅贸易有限公司）卢丽丽";
     * -e.g: $list[]="江西省抚州市东乡区孝岗镇恒安东路125号1栋3单元502室 13511112222 吴刚";
     * -e.g: $list[]="清远市清城区石角镇美林湖大东路口佰仹公司 郑万顺 15345785872 0752-28112632";
     * -e.g: $list[]="深圳市龙华区龙华街道1980科技文化产业园3栋317    张三    13800138000 518000 120113196808214821";
     *
     * -e.g: phpunit("Tools::expressAddrParse",[$list, true]);
     *
     * @param string|array $data 地址字符串
     * @param boolean $parseUser <true> 是否提取收件人
     * @return array
     */
    static function expressAddrParse($data, bool $parseUser = true, int $version = 2): array
    {
        if (!$data) return [];
        $result = [];
        if (is_string($data)) {
            $single = true;
            $data = [$data];
        }
        $Api = $version === 1 ? new ExpressAddressAI_V1 : new ExpressAddressAI_V2;
        foreach ($data as $address) {
            $result[] = $Api->smart($address, $parseUser);
        }
        return isset($single) ? $result[0] : $result;
    }


    /**
     * 人民币金额转大写
     *
     * -e.g: phpunit("Tools::convertCurrency", ["-10.00"]);
     * -e.g: phpunit("Tools::convertCurrency", [23.12]);
     * -e.g: phpunit("Tools::convertCurrency", [2223.12]);
     * -e.g: phpunit("Tools::convertCurrency", ['2,023.12']);
     * -e.g: phpunit("Tools::convertCurrency", ['2,023.12392']);
     * -e.g: phpunit("Tools::convertCurrency", ['100,232,023.12392']);
     * -e.g: phpunit("Tools::convertCurrency", ['2s3.12']);
     *
     * @param integer $currencyDigits
     * @return string
     */
    static function convertCurrency($currencyDigits = 0)
    {
        // Constants:
        $MAXIMUM_NUMBER = 99999999999.99;
        // Predefine the radix characters and currency symbols for output:
        $CN_ZERO = "零";
        $CN_ONE = "壹";
        $CN_TWO = "贰";
        $CN_THREE = "叁";
        $CN_FOUR = "肆";
        $CN_FIVE = "伍";
        $CN_SIX = "陆";
        $CN_SEVEN = "柒";
        $CN_EIGHT = "捌";
        $CN_NINE = "玖";
        $CN_TEN = "拾";
        $CN_HUNDRED = "佰";
        $CN_THOUSAND = "仟";
        $CN_TEN_THOUSAND = "万";
        $CN_HUNDRED_MILLION = "亿";
        $CN_SYMBOL = "";
        $CN_DOLLAR = "元";
        $CN_TEN_CENT = "角";
        $CN_CENT = "分";
        $CN_INTEGER = "整";

        $currencyDigits = trim(strval($currencyDigits));
        if ($currencyDigits == "") {
            throw new Exception("请输入金额!");
        }
        $currencyDigits = str_replace([",", "，", " ", "-"], '', $currencyDigits);
        if (preg_match("/[^\.\d]/", $currencyDigits)) {
            throw new Exception("无效的金额输入!");
        }
        // if (($currencyDigits).match(/^((\d{1,3}(,\d{3})*(.((\d{3},)*\d{1,3}))?)|(\d+(.\d+)?))$/) == null) {
        //     alert("非法的字符，请输入数字!");
        //     return "";
        // }
        if (($currencyDigits * 1) > $MAXIMUM_NUMBER) {
            throw new Exception("仅支持转换千亿以下金额");
        }
        // Process the coversion from currency digits to characters:
        // Separate integral and decimal parts before processing coversion:
        $parts = explode('.', strval($currencyDigits));
        if (count($parts) > 1) {
            $integral = $parts[0];
            $decimal = substr(str_pad($parts[1], 2, "0"), 0, 2);
        } else {
            $integral = $parts[0];
            $decimal = "";
        }
        // Prepare the characters corresponding to the digits:
        $digits = [$CN_ZERO, $CN_ONE, $CN_TWO, $CN_THREE, $CN_FOUR, $CN_FIVE, $CN_SIX, $CN_SEVEN, $CN_EIGHT, $CN_NINE];
        $radices = ["", $CN_TEN, $CN_HUNDRED, $CN_THOUSAND];
        $bigRadices = ["", $CN_TEN_THOUSAND, $CN_HUNDRED_MILLION];
        $decimals = [$CN_TEN_CENT, $CN_CENT];
        // Start processing:
        $outputCharacters = "";
        // Process integral part if it is larger than 0:
        if ($integral * 1 > 0) {
            $zeroCount = 0;
            $integral = strval($integral);
            for ($i = 0; $i < strlen($integral); $i++) {
                $p = strlen($integral) - $i - 1;
                $d = substr($integral, $i, 1);
                $quotient = $p / 4;
                $modulus = $p % 4;
                if ($d == "0") {
                    $zeroCount++;
                } else {
                    if ($zeroCount > 0) {
                        $outputCharacters .= $digits[0];
                    }
                    $zeroCount = 0;
                    $outputCharacters .= $digits[($d * 1)] . $radices[$modulus];
                }
                if ($modulus == 0 && $zeroCount < 4) {
                    $outputCharacters .= $bigRadices[$quotient];
                }
            }
            $outputCharacters .= $CN_DOLLAR;
        }
        // Process decimal part if there is:
        if ($decimal != "") {
            for ($i = 0; $i < strlen($decimal); $i++) {
                $d = substr($decimal, $i, 1);
                if ($d != "0") {
                    $outputCharacters .= $digits[($d * 1)] . $decimals[$i];
                }
            }
        }
        // Confirm and return the final output string:
        if ($outputCharacters == "") {
            $outputCharacters = $CN_ZERO . $CN_DOLLAR;
        }
        if ($decimal == "") {
            $outputCharacters .= $CN_INTEGER;
        }
        return $CN_SYMBOL . $outputCharacters;
    }

    /**
     * 检测是否为移动端
     * 
     * -e.g: phpunit("Tools::isMobile");
     */
    static function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE']))
            return true;
        //此条摘自TPM智能切换模板引擎，适合TPM开发
        if (isset($_SERVER['HTTP_CLIENT']) && 'PhoneClient' == $_SERVER['HTTP_CLIENT'])
            return true;
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA']))
            //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        //判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile'
            );
            //从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        //协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
}
