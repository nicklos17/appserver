<?php
namespace Appserver\v1\Controllers;

use Appserver\Utils\AlipayNotify;


class PaynotifyController extends ControllerBase
{

    private $renew;

    public function initialize()
    {
        $this->devices = new Devices;
        $this->renew = new Renew;
    }

    /**
     * 支付宝回调地址
     */
    public function alipay()
    {
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($this->di);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result)
        {
            //商户订单号
            $out_trade_no = $this->_sanReq['out_trade_no'];
        
            //支付宝交易号
            $trade_no = $this->_sanReq['trade_no'];
        
            //交易状态
            $trade_status = $this->_sanReq['trade_status'];
            if($this->_sanReq['trade_status'] == 'TRADE_FINISHED')
            {
               if($this->renew->renewOperate($out_trade_no, $_SERVER['REQUEST_TIME'], $trade_no) != self::SUCCESS)
               {
                    echo "fail";
                    return;
               }
            }
            elseif($this->_sanReq['trade_status'] == 'TRADE_SUCCESS')
            {

            }
            echo "success";
        }
        else
        {
            $_SESSION[$out_trade_no] = "fail";
            //验证失败
            echo "fail";
        }
    }

    /**
     * 微信支付回调结果
     */
    public function wechat()
    {
        include_once　__DIR__.'/../../utils/wechat/classes/ResponseHandler.class.php';
        include __DIR__.'/../../utils/wechat/classes/client/TenpayHttpClient.class.php';

        //log_result("进入后台回调页面");
        
        /* 创建支付应答对象 */
        $resHandler = new ResponseHandler();
        $resHandler->setKey($PARTNER_KEY);
        //判断签名
        if($resHandler->isTenpaySign() == true)
        {
            //商户在收到后台通知后根据通知ID向财付通发起验证确认，采用后台系统调用交互模式
            $notify_id = $resHandler->getParameter("notify_id");//通知id
        
            //商户交易单号
            $out_trade_no = $resHandler->getParameter("out_trade_no");
                
            //财付通订单号
            $transaction_id = $resHandler->getParameter("transaction_id");
        
            //商品金额,以分为单位
            $total_fee = $resHandler->getParameter("total_fee");
        
            //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
            $discount = $resHandler->getParameter("discount");
        
            //支付结果
            $trade_state = $resHandler->getParameter("trade_state");
            //可获取的其他参数还有
            //bank_type         银行类型,默认：BL
            //fee_type          现金支付币种,目前只支持人民币,默认值是1-人民币
            //input_charset     字符编码,取值：GBK、UTF-8，默认：GBK。
            //partner           商户号,由财付通统一分配的10位正整数(120XXXXXXX)号
            //product_fee       物品费用，单位分。如果有值，必须保证transport_fee + product_fee=total_fee
            //sign_type         签名类型，取值：MD5、RSA，默认：MD5
            //time_end          支付完成时间
            //transport_fee     物流费用，单位分，默认0。如果有值，必须保证transport_fee +  product_fee = total_fee
            
            //判断签名及结果
            if("0" == $trade_state)
            {
               if($this->renew->renewOperate($out_trade_no, $_SERVER['REQUEST_TIME'], $transaction_id)) != self::SUCCESS)
               {
                    echo "fail";
                    return;
               }
            }
            else
            {
                $this->renew->wechatFailedPay($out_trade_no, $_SERVER['REQUEST_TIME'], $transaction_id);
            }
            //回复服务器处理成功
            echo "Success";
        }
        else
        {
            echo "<br/>" . "验证签名失败" . "<br/>";
        }
    }
}