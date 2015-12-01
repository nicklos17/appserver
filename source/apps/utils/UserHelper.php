<?php

namespace Appserver\Utils;

class UserHelper
{
    /**
     * [生成用户二维码]
     * @param  [string] $uid     [用户id]
     * @param  [string] $mobi    [用户手机号]
     * @param  [string] $regtime [用户注册时间]
     * @return [string]          [返回二维码]
     */
    public static function makeUserQr($uid, $mobi, $regtime)
    {
        return substr(md5($mobi . $regtime), 8, 16) . '@' . $uid;
    }

    /**
     *如果图片来自第三方，则直接输出;不是则加服务器地址
     * @param unknown $pic
     * @return unknown
     */
    public static function checkPic($di, $pic)
    {
        if(strstr($pic, 'http:'))
        {
            return $pic;
        }
        else
        {
            if($pic != '')
            {
                return $di->get('sysconfig')['userPicServer'] . $pic;
            }
            else 
            {
                return $pic;
            }
        }
    }

    /**
     * 设置token
     * @param array $userInfo 用户信息
     * @param array $deviceToken 手机的设备标签
     * @return string
     */
    public static function setToken($di, $userInfo, $type, $deviceToken = '')
    {
        $redis = RedisLib::getRedis();
        $oldUserData = $redis->get('login:' . $userInfo['uid']);
        if($oldUserData)
        {
            if($oldUserData['deviceToken'] != $deviceToken)
            {
                //该记录存在，则获取原来的token，将token信息设为false
                $oldToken = $oldUserData['token'];
                $oldInfo = $redis->get('token:' . $oldToken);
                $oldInfo['tokenFlag'] = '3';
                $redis->setex('token:' . $oldToken, $di->get('sysconfig')['tokenTime'], $oldInfo);
                //加入推送队列
                $redis->lPush( $di->get('sysconfig')['untoken'], json_encode(array(
                    'uid' => $userInfo['uid'],
                    'content' => $di->get('sysconfig')['untokenMsg'],
                    'type' => empty($oldUserData['type']) ? '' : $oldUserData['type'],
                    'deviceToken' => $oldUserData['deviceToken'])));
            }
        }
        $userInfo['deviceToken'] = $deviceToken;
        //生成token
        $token = md5($userInfo['uid'] . $_SERVER['REQUEST_TIME']);
        $redis->setex('token:' . $token,$di->get('sysconfig')['tokenTime'], $userInfo);
        $redis->setex('login:' . $userInfo['uid'], $di->get('sysconfig')['tokenTime'], array('token' => $token, 'maketime' => $_SERVER['REQUEST_TIME'], 'deviceToken' => $deviceToken, 'type' => $type));
        return $token;
    }

    /**
     * 检查昵称长度，最多只允许6个汉字或者12个字符
     * @param unknown $name
     */
    public static function nameCheck($name)
    {
        mb_convert_encoding($name, 'UTF-8', 'auto');
        $length = strlen($name);
        if(preg_match('/[\x7f-\xff]/', $name))
        {
            //存在中文
            $newLen = strlen(preg_replace('/[\x7f-\xff]/', '', $name));
            if($newLen)
            {
                //和其他字符混合
                $mixLength =  $newLen + intval(($length - $newLen)/3)*2;
                if($mixLength >0 && $mixLength <= 12)
                {
                    return TRUE;
                }
                else
                {
                    return FALSE;
                }
            }
            else
            {
                //纯中文
                if($length >0 && $length <= 18)
                {
                    return TRUE;
                }
                else
                {
                    return FALSE;
                }
            }
        }
        else
        {
            //不存在中文
            if($length >0 && $length <= 12)
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
    }

    /**
     * 获取同年龄段平均步数
     * @param unknown $age
     */
    public static function getAvgSteps($di, $age)
    {
        if($age <= 6)
            $avgSteps = $di->get('sysconfig')['avgSteps'][6];
        elseif(7 <= $age && $age <= 9)
            $avgSteps = $di->get('sysconfig')['avgSteps'][9];
        elseif(10 <= $age && $age <= 12)
            $avgSteps = $di->get('sysconfig')['avgSteps'][12];
        elseif(13 <= $age && $age <= 15)
            $avgSteps = $di->get('sysconfig')['avgSteps'][15];
        elseif($age > 15)
            $avgSteps = $di->get('sysconfig')['avgSteps'][16];
        
        return $avgSteps;
    }

    /**
     * 根据城市和县区获得地区编码
     */
    public static function getAreaCode($di,$city)
    {
        return array_search($city,  $di->get('areaConfig'));
    }

    /**
     * 并发执行curl操作
     * @param unknown $urls 要访问的url数组
     */
    public static function rollCurlRequest($urls)
    {
        $queue = curl_multi_init();
        $map = array();
        
        foreach($urls as $k => $url)
        {
            $ch  = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            
            curl_multi_add_handle($queue, $ch);
            
            $map[(string)$ch] = $k;
        }
        
        $responses = array();
        do{
            while(($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM);
            if($code != CURLM_OK){break;}
            while($done = curl_multi_info_read($queue))
            {
                $info = curl_multi_getcontent($done['handle']);
                $error = curl_error($done['handle']);
                $responses[$map[(string)$done['handle']]] = compact('info', 'error');
                
                //读取内容完毕，移出当前句柄
                curl_multi_remove_handle($queue, $done['handle']);
                curl_close($done['handle']);
            }
            
            if($active > 0)
            {
                curl_multi_select($queue, 0.5);
            }
        }
        while ($active);
        
        curl_multi_close($queue);
        return $responses;
    }

    /**
     * 用curl获取连接数据
     * @param unknown $url
     */
    public static function curlRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $response = curl_exec($ch);
        curl_close($ch);
        
       return $response;
    }

    /**
     * 根据宝贝性别，温度，aqi数据返回宝贝天气图片和天气状况
     * @param unknown $sex 宝贝性别
     * @param unknown $weatherCode 天气编码
     * @param unknown $aqi 空气质量指数
     * @param unknown $ntemp 当日夜间温度
     * @param unknown $dtemp 当然最高温度
     */
    public static function getBabyWeatherPic($di, $sex, $weatherCode, $aqi, $ntemp, $dtemp)
    {
        $num = $weatherCode;
        if(in_array($num, $di->get('weatherConfig')['rain2']))
            $num = '08';
        elseif(in_array($num, $di->get('weatherConfig')['rain3']))
            $num = '09';
        elseif(in_array($num, $di->get('weatherConfig')['rain4']))
            $num = 10;
        elseif(in_array($num, $di->get('weatherConfig')['snow2']))
            $num = 15;
        elseif(in_array($num, $di->get('weatherConfig')['snow3']))
            $num = 16;
        elseif(in_array($num, $di->get('weatherConfig')['snow4']))
            $num = 17;
        elseif(in_array($num, $di->get('weatherConfig')['sandStrom']))
            $num = 20;
        
        //如果是雨天，则返回有雨的图片
        if(in_array($num, array('07', '08', '09', 10)))
            $israin = 'rain';           
        else
            $israin = 'none';
        
        //如果天气质量指数大于150，则属于中度污染，返回的图片带有口罩
        if($aqi > 150)
            $ispollute = 'poll';
        else
            $ispollute = 'none';
        
        //根据白天气温显示穿衣图片，如果没有白天气温，则根据夜间气温
        if($dtemp != '')
            $temp = $dtemp;
        else
            $temp = $ntemp;
        
        if($temp >= 28)
            $scene = 'hot';
        elseif($temp >= 22 && $temp <28)
            $scene = 'cool';
        elseif($temp >= 17 && $temp < 22)
            $scene = 'dank';
        elseif($temp >= 11 && $temp < 17)
            $scene = 'cold';
        elseif($temp < 11)
            $scene = 'sc';  
        else
            $scene = '';
        
        if($scene != '')
        {
            $babyPicForWeather = $di->get('weatherConfig')['weatherServerUrl'] . sprintf($di->get('weatherConfig')['babyPicForWeather'][$sex], $scene, $israin, $ispollute);
        }
        else
        {
            $babyPicForWeather = '';
        }
        if($num != '')
            $weatherPic = $di->get('weatherConfig')['weatherServerUrl'] . $di->get('weatherConfig')['weatherPicUrl'] . $num . '.png';
        else
            $weatherPic = '';
        
        return array('baby_pic' => $babyPicForWeather, 'weat_pic' => $weatherPic);
    }

    /**
     * 签到升级计算公式：得出用户升到下一级所需的总签到天数
     * 备注：目前是线性升级方式，等级每增加一级，所需的升级天数递增5天
     * @param str $level 用户当前等级
     *
     */
    public static function levelCheck($level)
    {
        return (string)(2.5*$level*$level + 7.5*$level + 4);
    }
}