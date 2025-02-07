<?php

/**
 * @name 微信公众号核心类
 * @author vipkwd <service@vipkwd.com>
 * @link https://github.com/wxy545812093/vipkwd-phputils
 * @license MIT
 * @copyright The PHP-Tools
 */

declare(strict_types=1);

namespace Vipkwd\Utils\Wx;

use Vipkwd\Utils\Http as vipkwdHttp;
use Vipkwd\Utils\Type\Str as vipkwdStr;

class Gzhcommon
{

    private $mp_appid;
    private $mp_app_secret;
    private $request;

    const ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential';

    private function __construct(string $appid, string $app_secret)
    {
        $this->mp_appid = $appid;
        $this->mp_app_secret = $app_secret;
        $this->request = vipkwdHttp::request();
    }

    /**
     * 秘钥实例化
     * 
     * @param string $appid 公众号APPID
     * @param string $app_secret 公众号APP秘钥
     */
    static function instance(string $appid, string $app_secret)
    {
        return (new self($appid, $app_secret));
    }

    /**
     * 获取微信公众号全局ACCESSTOKEN
     * @param boolean $flush <false> 是否强制刷新
     */
    public function getAccesstoken($flush = false)
    {
        // $cache_key = 'wxmp_accesstoken';
        // $cache_data = Yii::$app->cache->get($cache_key);
        $cache_data = null;
        if (!$cache_data || $flush) {

            $url = self::ACCESS_TOKEN_URL . '&appid=' . $this->mp_appid . '&secret=' . $this->mp_app_secret;
            $cache_data = self::Curl($url);
            if (!key_exists('errcode', $cache_data)) {
                $cache_data = $cache_data['access_token'];
            } else {
                $cache_data = '';
            }
            // Yii::$app->cache->set($cache_key, $cache_data, 5000); //1小时30分刷新
        }
        return $cache_data;
    }

    //微信公众号CURL
    static function Curl($url, $type = 'get', $data = '', bool $responseArray = false)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            if ($type == 'post') {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            $output = curl_exec($ch);
            curl_close($ch);
            //        if( curl_error($ch)){
            //            return curl_error($ch);
            //        }else{
            //返回数组
            //如果需要返回数组
            if ($responseArray) {
                return $output;
            } else {
                return json_decode($output, true);
            }
            //}
        }
        return false;
    }

    static function http_request($url, $data = null)
    {
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            // curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                curl_setopt(
                    $curl,
                    CURLOPT_HTTPHEADER,
                    array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data)
                    )
                );
            }

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        }
        return null;
    }

    /**
     * 获取用户的openid
     */
    public function baseAuth($redirect_url)
    {
        //1.准备scope为snsapi_base网页授权页面
        $baseurl = urlencode($redirect_url);
        $snsapi_base_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->mp_appid . '&redirect_uri=' . $baseurl . '&response_type=code&scope=snsapi_base&state=YQJ#wechat_redirect';

        //2.静默授权,获取code
        //页面跳转至redirect_uri/?code=CODE&state=STATE
        $code = $this->request->query->code;
        if (!isset($code)) {
            header('Location:' . $snsapi_base_url);
            exit;
        }

        //3.通过code换取网页授权access_token和openid
        $curl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->mp_appid . '&secret=' . $this->mp_app_secret . '&code=' . $code . '&grant_type=authorization_code';
        $result = self::Curl($curl);

        return $result;
    }

    /**
     * 根据openid获取用户的基本信息
     */
    public function getWxuinfo($openid)
    {
        $curl = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . self::getAccesstoken() . '&openid=' . $openid;
        $result = self::Curl($curl);
        return $result;
    }

    /**
     * 获取用户的 未关注公众号的
     */
    public function getUserinfo($redirect_url)
    {

        //1.准备scope为snsapi_base网页授权页面
        $baseurl = urlencode($redirect_url);

        $snsapi_base_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->mp_appid . '&redirect_uri=' . $baseurl . '&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';

        //2.静默授权,获取code
        //页面跳转至redirect_uri/?code=CODE&state=STATE
        $code = $this->request->query->code;
        if (!isset($code)) {
            header('Location:' . $snsapi_base_url);
            exit;
        }
        /*根据code获取用户openid*/
        //3.通过code换取网页授权access_token和openid
        $curl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->mp_appid . '&secret=' . $this->mp_app_secret . '&code=' . $code . '&grant_type=authorization_code';
        $resx = self::Curl($curl);
        $openid = $resx['openid'];
        $access_token = $resx['access_token'];

        $curl = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . "&lang=zh_CN";
        $result = self::Curl($curl);
        return $result;
    }

    //字节转Emoji表情
    public static function bytesToEmoji($cp)
    {
        return vipkwdStr::bytesToEmoji($cp);
    }
}
