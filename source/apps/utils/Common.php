<?php

namespace Appserver\Utils;

class Common
{

    public static function initModel($model)
    {
        $modObj = new $model();
        return $modObj;
    }

    /**
     * 密码加密算法
     */
    public static function makeSecert($pass, $salt, $counts = 1000, $length = 32)
    {
        return hash_pbkdf2("sha256", $pass, $salt, $counts, $length);
    }

    /**
     * 生成随机码
     * @param string $length 验证码位数
     * @param string $chars
     * @return string
     */
    public static function random($length, $chars = '0123456789')
    {
        $hash = '';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++)
        {
        $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    /**
     * 为上传的图片创建目录和文件名等
     * @param savePath 要将图片保留到的路径
     */
    public static function makePath($savePath, $mobi)
    {
        $time = substr(md5($_SERVER['REQUEST_TIME']), 8, 16);
        //url增加两段
        $schme1 = substr($time, 0, 2);
        $schme2 = substr($time, 2, 2);
        $path = $savePath.'/'.$schme1.'/'.$schme2;
        $rename = substr(md5($time.$mobi), 8, 16) . '.jpg';
        $picUrl = '/'. $path .'/'. $rename;
        return array('path' => $path, 'rename' => $rename, 'picUrl' => $picUrl);
    }
    /**
     * 获取服务期的期限
     * @param str $timestamp  开始服务的时间戳
     * @param str $base   服务期基数： 3个月 6个月..
     */
    public static function expires($timestamp, $base = '3')
    {
        $arr = getdate($timestamp);
        $res = self::monthToDay($arr['year'], $arr['mon']);
        //获得服务开始月份的第一天
        $firstday = $res['0'];
        //获得服务开始月份的最后一天
        $lastday = $res['1'];
        
        //计算到期的年月
        if($arr['mon'] == '10' || $arr['mon'] == '11' || $arr['mon'] == '12')
        {
            $month = $arr['mon'] - (12 -$base);
            $year = $arr['year'] + 1;
        }
        else
        {
            $month = $arr['mon'] + $base;
            $year = $arr['year'];
        }
        
        //获取服务到期月份的最后一天
        $expires = self::monthToDay($year, $month);
        
        //如果到期月份是2月并且是平年
        if($month == '2' && (date('d', $expires[1]) == 28))
        {
            //如果服务开始的时间日期是29号，或者30号，则2月份到期的日期为2月份最后一天
            if($arr['mday'] == '29' || $arr['mday'] == '30' || $arr['mday'] == '31')
            {
                $expiresDate = $expires[1];
            }
            else
            {
                $expiresDate = mktime(23,59,59, $month, $arr['mday']-1, $year);
            }
        }
        //如果到期月份是2月并且是闰年
        elseif($month == '2' && (date('d', $expires[1]) == 29))
        {
            //如果服务开始的时间日期是30号或者31号，则2月份到期的日期为2月份最后一天
            if($arr['mday'] == '30' || $arr['mday'] == '31')
            {
                $expiresDate = $expires[1];
            }
            else
            {
                $expiresDate = mktime(23,59,59, $month, $arr['mday']-1, $year);
            }
        }
        else
        {
            //如果服务开始日期为当月第一天，那么服务到期时间为到期月份的最后一天
            if($arr['mday'] === date('d', $firstday))
            {
                $expiresDate = $expires[1];
            }
            else
            {
                $expiresDate = mktime(23,59,59, $month, $arr['mday']-1, $year);
            }
        }
        
        return (string)$expiresDate;
    }

    /**
     * 返回指定月份的第一天和最后一天
     * @param string $y   年份
     * @param string $m   月份
     * @return array
     */
    public static function monthToDay($y = '', $m = '')
    {
        if($y == '')
        {
            $y = date('Y');
        }
        if($m == '')
        {
            $m = date('m');
        }
        $m = sprintf("%02d", intval($m));
        $y = str_pad(intval($y), 4, "0", STR_PAD_RIGHT);
        $m>12||$m<1 ? $m = 1: $m =$m;
        $firstday = strtotime($y . $m . '01');
        $firstdaystr = date('Y-m-01', $firstday);
        $endday = strtotime(date('Y-m-d 23:59:59', strtotime("$firstdaystr +1 month -1 day")));
        return array($firstday, $endday);
    }

    /**
     * 生成用户二维码
     * @param unknown $uid
     * @param unknown $mobi
     * @param unknown $regtime
     * @return string
     */
    public static function makeQr($uid, $mobi, $regtime)
    {
        return substr(md5($mobi . $regtime), 8, 16) . '@' . $uid;
    }

    /**
     * 根据轨迹id获取推送对象的uid
     * @param locusId 轨迹id
     * @param relId 对这条轨迹进行评论或者点赞的人的id
     * @return array 返回推送对象的uid
     */
    public static function getPushUid($locusId, $relId)
    {
        $locusmodel = self::initModel('\Appserver\Mdu\Models\LocusModel');
        $familymodel = self::initModel('\Appserver\Mdu\Models\FamilyModel');
        $uids = array();
        $babyId = $locusmodel->getBabyId($locusId);
        if(empty($babyId))
        {
            return false;
        }
        else
        {
            //获取relId和该轨迹的宝贝id
            $babyId = $babyId['baby_id'];
            $rel = $familymodel->getAuthor($relId, $babyId);
            if(!empty($rel))
            {
                foreach($rel as $v)
                {
                    $uids[] = $v['u_id'];
                }
            }
        }
        return $uids;
    }

    /**
     * 字符串截取
     * @param unknown $sourcestr 原字符串
     * @param unknown $cutlength  截取的长度
     * @param string $suffix        超过部分用默认'...'代替
     * @return unknown|string
     */
    protected static function strCut($sourcestr,$cutlength,$suffix='...')
    {
        $str_length = strlen($sourcestr);
        if($str_length <= $cutlength) {
            return $sourcestr;
        }
        $returnstr='';
        $n = $i = $noc = 0;
        while($n < $str_length) {
            $t = ord($sourcestr[$n]);
            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $i = 1; $n++; $noc++;
            } elseif(194 <= $t && $t <= 223) {
                $i = 2; $n += 2; $noc += 2;
            } elseif(224 <= $t && $t <= 239) {
                $i = 3; $n += 3; $noc += 2;
            } elseif(240 <= $t && $t <= 247) {
                $i = 4; $n += 4; $noc += 2;
            } elseif(248 <= $t && $t <= 251) {
                $i = 5; $n += 5; $noc += 2;
            } elseif($t == 252 || $t == 253) {
                $i = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }
            if($noc >= $cutlength) {
                break;
            }
        }
        if($noc > $cutlength) {
            $n -= $i;
        }
        $returnstr = substr($sourcestr, 0, $n);
    
    
        if ( substr($sourcestr, $n, 6)){
            $returnstr = $returnstr . $suffix;//超过长度时在尾处加上省略号
        }
        return $returnstr;
    }

    /**
     * 根据签到天数获得相应的云币数量
     * @param str $days 连续签到的天数
     * @return string
     */
    public static function checkinCoin($di, $days)
    {
        //连续签到大于6天，则每天获得与第六天签到等量的云币
        if($days > 6)
        {
            $coins = $di['sysconfig']['chenkinSixDay'];
        }
        else
        {
            switch($days)
            {
                case 1:
                    $coins = $di['sysconfig']['chenkinFirstDay'];
                    break;
                case 2:
                    $coins = $di['sysconfig']['chenkinTwoDay'];
                    break;
                case 3:
                    $coins = $di['sysconfig']['chenkinThreeDay'];
                    break;
                case 4:
                    $coins = $di['sysconfig']['chenkinFourDay'];
                    break;
                case 5:
                    $coins = $di['sysconfig']['chenkinFiveDay'];
                    break;
                case 6:
                    $coins = $di['sysconfig']['chenkinSixDay'];
                    break;
            }
        }
        return $coins;
    }

    /**
     * 字符串截取
     * @param str $sourcestr 字符串
     * @param str $cutlength 要截取的长度
     * @param str $suffix 代替被截掉的字符
     */
    public static function substrCut($sourcestr, $cutlength, $suffix='..')
    {
        $str_length = strlen($sourcestr);
        if($str_length <= $cutlength) {
            return $sourcestr;
        }
        $returnstr='';  
        $n = $i = $noc = 0;
        while($n < $str_length) {
                $t = ord($sourcestr[$n]);
                if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $i = 1; $n++; $noc++;
                } elseif(194 <= $t && $t <= 223) {
                    $i = 2; $n += 2; $noc += 2;
                } elseif(224 <= $t && $t <= 239) {
                    $i = 3; $n += 3; $noc += 2;
                } elseif(240 <= $t && $t <= 247) {
                    $i = 4; $n += 4; $noc += 2;
                } elseif(248 <= $t && $t <= 251) {
                    $i = 5; $n += 5; $noc += 2;
                } elseif($t == 252 || $t == 253) {
                    $i = 6; $n += 6; $noc += 2;
                } else {
                    $n++;
                }
                if($noc >= $cutlength) {
                    break;
                }
        }
        if($noc > $cutlength) {
                $n -= $i;
        }
        $returnstr = substr($sourcestr, 0, $n);
     

        if ( substr($sourcestr, $n, 6)){
              $returnstr = $returnstr . $suffix;//超过长度时在尾处加上省略号
          }
        return $returnstr;
    }

    /**
     * 跟踪日志
     */
    public static function writeLog($logName, $msg)
    {
        $msg = date('Y-m-d H:i:s') . '>>>' . $msg . "\r\n";
        $fp = fopen($logName, 'a+');
        fwrite($fp, $msg);
        fclose($fp);
    }

    /**
     * [创建目录]
     * @param  [type]  $path [description]
     * @param  integer $mode [description]
     * @return [type]        [description]
     */
    public function _createdir($path, $mode = 0777)
    {
        if(!is_dir($path))
        {
            //true为可创建多级目录
            $re = mkdir($path, $mode, true);
            if($re)
                return TRUE;
            else
                return FALSE;
        }
        else
            return TRUE;
    }

    /**
     * 生成唯一订单编码
     *
     */
    public static function  makeOrderSn()
    {
        return date('ymd').substr(time(),-5).substr(microtime(),2,5);
    }
}
