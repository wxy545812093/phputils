<?php
/**
 * @name 快递地址智能解析 -- 省份源
 * @author vipkwd <service@vipkwd.com>
 * @link https://github.com/wxy545812093/vipkwd-phputils
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @copyright The PHP-Tools
 */
declare(strict_types = 1);

namespace Vipkwd\Utils\Libs\ExpressAI\Data;

class Province {

    static function list():array {
        return [
            '1' => ['name' => '北京', 'pid' => 0],
            '2' => ['name' => '天津', 'pid' => 0],
            '3' => ['name' => '河北省', 'pid' => 0],
            '4' => ['name' => '山西省', 'pid' => 0],
            '5' => ['name' => '内蒙古自治区', 'pid' => 0],
            '6' => ['name' => '辽宁省', 'pid' => 0],
            '7' => ['name' => '吉林省', 'pid' => 0],
            '8' => ['name' => '黑龙江省', 'pid' => 0],
            '9' => ['name' => '上海', 'pid' => 0],
            '10' => ['name' => '江苏省', 'pid' => 0],
            '11' => ['name' => '浙江省', 'pid' => 0],
            '12' => ['name' => '安徽省', 'pid' => 0],
            '13' => ['name' => '福建省', 'pid' => 0],
            '14' => ['name' => '江西省', 'pid' => 0],
            '15' => ['name' => '山东省', 'pid' => 0],
            '16' => ['name' => '河南省', 'pid' => 0],
            '17' => ['name' => '湖北省', 'pid' => 0],
            '18' => ['name' => '湖南省', 'pid' => 0],
            '19' => ['name' => '广东省', 'pid' => 0],
            '20' => ['name' => '广西壮族自治区', 'pid' => 0],
            '21' => ['name' => '海南省', 'pid' => 0],
            '22' => ['name' => '重庆', 'pid' => 0],
            '23' => ['name' => '四川省', 'pid' => 0],
            '24' => ['name' => '贵州省', 'pid' => 0],
            '25' => ['name' => '云南省', 'pid' => 0],
            '26' => ['name' => '西藏自治区', 'pid' => 0],
            '27' => ['name' => '陕西省', 'pid' => 0],
            '28' => ['name' => '甘肃省', 'pid' => 0],
            '29' => ['name' => '青海省', 'pid' => 0],
            '30' => ['name' => '宁夏回族自治区', 'pid' => 0],
            '31' => ['name' => '新疆维吾尔自治区', 'pid' => 0],
            '32' => ['name' => '台湾省', 'pid' => 0],
            '33' => ['name' => '香港特别行政区', 'pid' => 0],
            '34' => ['name' => '澳门特别行政区', 'pid' => 0],
            '35' => ['name' => '海外', 'pid' => 0],
        ];
    }

};