<?php

namespace Appserver\Utils;

class AlipayNotify
{
    private $di;
    private $alipay_config;
    private $paymentConf;

    //HTTPS形式消息验证地址
    private $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';

     //HTTP形式消息验证地址
    private $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';

    public function __construct($di)
    {
        $this->di=$di;
        $this->paymentConf = $this->di->get('sysconfig')['payment']['alipay'];

        //合作身份者id，以2088开头的16位纯数字
        $alipay_config['partner'] = $this->paymentConf['partner'];

        //商户的私钥（后缀是.pen）文件相对路径
        $alipay_config['private_key_path']  = __DIR__.'/alipay/key/rsa_private_key.pem';

        //支付宝公钥（后缀是.pen）文件相对路径
        $alipay_config['ali_public_key_path']= __DIR__.'/alipay/key/alipay_public_key.pem';

        //签名方式 不需修改
        $alipay_config['sign_type']    = strtoupper('RSA');

        //字符编码格式 目前支持 gbk 或 utf-8
        $alipay_config['input_charset']= strtolower('utf-8');

        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        $alipay_config['cacert']  = __DIR__.'/alipay/cacert.pem';

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipay_config['transport']    = 'http';
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    function verifyNotify()
    {
        if(empty($_POST)) {//判断POST来的数组是否为空
            return false;
        }
        else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}
            
            //写日志记录
            if ($isSign) {
                $isSignStr = 'true';
            }
            else {
                $isSignStr = 'false';
            }
            $log_text = "responseTxt=".$responseTxt."\n notify_url_log:isSign=".$isSignStr.",";
            $log_text = $log_text.createLinkString($_POST);
            
            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i",$responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    function getSignVeryfy($para_temp, $sign)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = paraFilter($para_temp);
        
        //对待签名参数数组排序
        $para_sort = argSort($para_filter);
        
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = createLinkstring($para_sort);
        
        $isSgin = false;
        switch (strtoupper(trim($this->alipay_config['sign_type']))) {
            case "RSA" :
                $isSgin = rsaVerify($prestr, trim($this->alipay_config['ali_public_key_path']), $sign);
                break;
            default :
                $isSgin = false;
        }

        return $isSgin;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    function getResponse($notify_id)
    {
        $transport = strtolower(trim($this->alipay_config['transport']));
        $partner = trim($this->alipay_config['partner']);
        $veryfy_url = '';
        if($transport == 'https') {
            $veryfy_url = $this->https_verify_url;
        }
        else {
            $veryfy_url = $this->http_verify_url;
        }
        $veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);

        return $responseTxt;
    }
}
