<?php
namespace Appserver\v1\Controllers;
use Appserver\Mdu\Modules\DevicesModule as Devices,
       Appserver\Mdu\Modules\FamilyModule as Family,
       Appserver\Mdu\Modules\RenewModule as Renew,
       Appserver\Utils\Common;


class RenewController extends ControllerBase
{
    private $userInfo;
    private $devices;
    private $family;
    private $renew;

    const NON_PACKAGE = 11070;
    const NON_SHOE = 10034;
    const SUCCESS = '1';
    const FAILED_GET_PREPAYID = 11076;
    const FAILED_GET_TOKEN = 11075;
    const CANNOT_TO_RENEW = 11074;

    public function initialize()
    {
        //验证token
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->devices = new Devices;
        $this->family = new Family;
        $this->renew = new Renew;
    }

    public function indexAction()
    {
        $shoeInfo = $this->devices->getUidByDev($this->_sanReq['shoe_id']);
        if(empty($shoeInfo))
            return self::NON_EXIST_SHOE;

        $this->_checkRelation($this->userInfo['uid'], $shoeInfo['baby_id']);

        //获取可以续费的最后时间，过期3个月，就不允许续费
        $deadline = Common::expires($shoeInfo['dev_expires'], $this->di['sysconfig']['allowRenew']);
        if($_SERVER['REQUEST_TIME'] > $deadline)
        {
            $this->_showMsg(self::CANNOT_TO_RENEW, $this->di['flagmsg'][self::CANNOT_TO_RENEW]);
        }

        $res = $this->renew->getRenewList($this->_sanReq['shoe_id'], $shoeInfo);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 支付宝支付
     */
    public function alipayAction()
    {
        //支付宝提供的加密方式
        include_once __DIR__.'/../../utils/alipay/lib/alipay_core.function.php';

        $babyId = $this->devices->getBabyIdByShoeId($this->userInfo['uid'], $this->_sanReq['shoe_id']);
        if(!$babyId)
            $this->_showMsg(self::NON_SHOE, $this->di['flagmsg'][self::NON_SHOE]);
        $rel = $this->_checkRelation($this->userInfo['uid'], $babyId['baby_id']);
        //角色名
        if(!empty($rel) && $rel['family_rolename'] != '')
            $rolename = $rel['family_rolename'];
        else
            $rolename = '';

        //获取套餐
        $renew = $this->renew->getRenew($serviceId, 1);
        if(!$renew)
            $this->_showMsg(self::NON_PACKAGE, $this->di['flagmsg'][self::NON_PACKAGE]);

        //配置
        $alipayConf = $this->di->get('sysconfig')['payment']['alipay'];

        //签名参数
        $params['partner'] = $alipayConf['partner'];
        $params['seller_id'] = $alipayConf['seller_id'];
        $params['out_trade_no'] =  (string)Common::makeOrderSn();
        $params['subject'] = $renew['cr_name'];
        $params['body'] = $renew['cr_detail'];
        $params['total_fee'] = (string)number_format($renew['cr_real_price'], '2', '.', '');
        $params['notify_url'] = $this->di->get['sysconfig']['renewServer'] . $alipayConf['notify_url'];
        $params['service'] = 'mobile.securitypay.pay';
        $params['_input_charset'] = 'utf-8';
        $params['payment_type'] = '1';

        //生成续费订单，入库
        if(($res = $this->renew->renewUpdate($shoeId, $this->userInfo['uid'], $serviceId, $_SERVER['REQUEST_TIME']
        , 1, $params['out_trade_no'], 1, $params['total_fee'], $params['subject'], $renew['cr_period'],
        $renew['cr_coins'], $rolename, $babyId['baby_id'],  $babyId['dev_imei'])) != self::SUCCESS)
            $this->_showMsg($res, $this->di['flagmsg'][$res]);

        $data = makeSignstring(argSort(paraFilter($params)));
        $params['sign'] = urlencode(rsaSign($data,$alipay_config['private_key_path']));
        
        $this->_returnResult(array('flag' => self::SUCCESS, 'data' => urlencode($data . '&sign="' . $params['sign'] . '"&sign_type="RSA"'), 'order_no' => $params['out_trade_no']));
    }

    /**
     * 微信支付
     */
    public function wechatAction()
    {
        $babyId = $this->devices->getBabyIdByShoeId($this->userInfo['uid'], $this->_sanReq['shoe_id']);;
        if(!$babyId)
            $this->_showMsg(self::NON_SHOE, $this->di['flagmsg'][self::NON_SHOE]);

        //角色名
        if(!empty($rel) && $rel['family_rolename'] != '')
            $rolename = $rel['family_rolename'];
        else
            $rolename = '';

        //获取套餐
        $renew = $this->renew->getRenew($serviceId, 1);
        if(!$renew)
            $this->_showMsg(self::NON_PACKAGE, $this->di['flagmsg'][self::NON_PACKAGE]);

        //订单号
        $orderNo = (string)Common::makeOrderSn();

        //生成续费订单，入库
        if(($res = $this->renew->renewUpdate($shoeId, $this->userInfo['uid'], $serviceId, $_SERVER['REQUEST_TIME']
        , 1, $orderNo, 3, $renew['cr_real_price'], $renew['cr_name'], $renew['cr_period'],
        $renew['cr_coins'], $rolename, $babyId['baby_id'],  $babyId['dev_imei'])) != self::SUCCESS)
            $this->_showMsg($res, $this->di['flagmsg'][$res]);

        include_once __DIR__.'/../../utils/wechat/classes/RequestHandler.class.php';
        include_once __DIR__.'/../../utils/wechat/tenpay_config.php';
        include __DIR__.'/../../utils/wechat/classes/client/TenpayHttpClient.class.php';
        //获取token值
        $reqHandler = new RequestHandler();
        //配置
        $wechatConf = $this->di->get('sysconfig')['payment']['wechat'];

        $reqHandler->init($wechatConf['app_id'], $wechatConf['app_secret'], $wechatConf['partner_key'], $wechatConf['app_key']);
        $Token = $reqHandler->GetToken();
        
        if($Token != '')
        {
            //设置package支付参数
            $packageParams =array();
            $packageParams['bank_type'] = 'WX';             //支付类型
            $packageParams['body'] = $renew['cr_name'];                    //商品描述
            $packageParams['fee_type'] = '1';              //银行币种
            $packageParams['input_charset'] = 'UTF-8';          //字符集  renewServer
            $packageParams['notify_url'] = $this->di->get['sysconfig']['renewServer'] . $wechatConf['notify_url'];     //通知地址
            $packageParams['out_trade_no'] = $orderNo;        //商户订单号
            $packageParams['partner'] = $wechatConf['partner'];             //设置商户号
            $packageParams['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];  //支付机器IP
            $packageParams['total_fee'] = $renew['cr_real_price']*100;          //商品总金额,以分为单位
            $package = $reqHandler->genPackage($packageParams);
            
            $time_stamp = (string)time();
            $nonce_str = md5(rand());
            //设置支付参数
            $signParams =array();
            $signParams['appid'] =$APP_ID;
            $signParams['appkey'] =$APP_KEY;
            $signParams['noncestr'] =$nonce_str;
            $signParams['package'] =$package;
            $signParams['timestamp']=$time_stamp;
            $signParams['traceid'] = 'yunduo_wechat';
            //生成支付签名
            $sign = $reqHandler->createSHA1Sign($signParams);

            //增加非参与签名的额外参数
            $signParams['sign_method']      ='sha1';
            $signParams['app_signature']    =$sign;
            //剔除appkey
            unset($signParams['appkey']);
            //获取prepayid
            $prepayid=$reqHandler->sendPrepay($signParams);
            if ($prepayid != null)
            {
                $pack   = 'Sign=WXPay';
                //输出参数列表
                $prePayParams =array();
                $prePayParams['appid'] = $APP_ID;
                $prePayParams['appkey'] = $APP_KEY;
                $prePayParams['noncestr'] = $nonce_str;
                $prePayParams['package'] = $pack;
                $prePayParams['partnerid'] = $PARTNER;
                $prePayParams['prepayid'] = $prepayid;
                $prePayParams['timestamp']  = $time_stamp;
                //生成签名
                $sign=$reqHandler->createSHA1Sign($prePayParams);
            
                $outparams['retcode'] = '0';
                $outparams['retmsg'] = 'ok';
                $outparams['partnerid'] = $PARTNER;
                $outparams['noncestr'] = $nonce_str;
                $outparams['package'] = $pack;
                $outparams['prepayid'] = $prepayid;
                $outparams['timestamp'] = $time_stamp;
                $outparams['sign'] = $sign;

                //增加返回相关产品信息
                $outparams['order_no'] = $orderNo;
                $outparams['order_subject'] = $renew['cr_name'];
                $outparams['total_fee'] = $renew['cr_real_price'];
                $this->_returnResult(array('flag' => self::SUCCESS, 'data' => $outparams));
            }
            else
                $this->_showMsg(self::FAILED_GET_PREPAYID, $this->di['flagmsg'][self::FAILED_GET_PREPAYID]);
        }
        else
            $this->_showMsg(self::FAILED_GET_TOKEN, $this->di['flagmsg'][self::FAILED_GET_TOKEN]);
    }
}