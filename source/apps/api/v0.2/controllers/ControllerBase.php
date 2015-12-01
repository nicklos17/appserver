<?php
namespace Appserver\v2\Controllers;

use Phalcon\Mvc\Controller,
    Appserver\Utils;

class ControllerBase extends Controller
{

    const NO_OAUTH = 99999;
    const NO_RELATION = 10079;

    protected $warnMsg = array();     // 校验错误提示信息，包括code和msg
    protected $warnMsgCode = false;     // 校验错误提示信息，仅包括code
    protected $warnMsgMsg = false;     // 校验错误提示信息，仅包括msg
    protected $validFlag = true;    // 校验结果标识  true - 通过   false - 拒绝
    public $_sanReq = array();   // 经处理过的参数
    protected $ctrlName;    // 当前访问控制器名
    protected $actName;    // 当前访问方法名

    public function beforeExecuteRoute($dispatcher)
    {
        $this->ctrlName = $this->dispatcher->getControllerName();
        $this->actName = $this->dispatcher->getActionName();

        // 获取校验规则
        $rulesFile = __DIR__ . '/../config/rules/' . $this->ctrlName . 'Rules.php';
        $rules = file_exists($rulesFile) ? include $rulesFile : false;
        $actionRules = $rules && isset($rules[$this->actName]) ? $rules[$this->actName] : false;
        if (!$rules || !$actionRules)
        {
            $this->_sanReq = $this->request->get();
            return true;
        }
        $utils = new Utils\RulesParse($actionRules, $this->di);
        $utils->parse();
        if (!$utils->resFlag)
        {
            $this->validFlag = false;
            $this->warnMsg = $utils->warnMsg;
            $this->warnMsgCode = $utils->warnMsgCode;
            $this->warnMsgMsg = $utils->warnMsgMsg;
            foreach ($this->warnMsg as $warn)
            {
                echo json_encode(array('flag' => $warn['msg'], 'msg' => $this->di['flagmsg'][$warn['msg']]));
                exit;
            }
        }
        else
        {
            $this->_sanReq = $utils->_sanReq;
        }
    }

    /**
     * [判断用户是否有操作权限]
     */
    protected function _oauthrity($uid, $babyId)
    {
        $relObj = new \Appserver\Mdu\Modules\FamilyModule();
        $rel = $relObj->checkRelation($uid, $babyId);
        if(!($rel['family_relation'] == 1 || $rel['family_relation'] == 5))
            $this->_showMsg(self::NO_OAUTH, $this->di['flagmsg'][self::NO_OAUTH]);
    }

    /**
     * [判断用户与宝贝是否有关系]
     */
    protected function _checkRelation($uid, $babyId)
    {
        $relObj = new \Appserver\Mdu\Modules\FamilyModule();
        $rel = $relObj->checkRelation($uid, $babyId);
        if(empty($rel))
            $this->_showMsg(self::NO_RELATION, $this->di['flagmsg'][self::NO_RELATION]);
        else
            return $rel;
    }

    /**
     * token验证是否已经登录,如已登录，则返回用户的信息,否则返回错误信息
     * @param str $token 获得post请求过来的token
     * return array|json
     */ 
    protected function _getToken($token)
    {
        $redisObj = new Utils\RedisLib($this->di);
        $redis = $redisObj->getRedis();
        $userInfo = $redis->get('token:' . $token);
        if($userInfo)
        {
            //如果flag为1,说明正常
            if($userInfo['tokenFlag'] == '1')
            {
                //如果token有效，则延长token的有效时间
                $redis->setex('token:' . $token, $this->di->get('sysconfig')['tokenTime'], $userInfo);
                return $userInfo;
            }
            else
                $this->_showMsg('00009', $this->di->get('flagmsg')['00009']);
        }
        else 
            $this->_showMsg('00000', $this->di->get('flagmsg')['00000']);
    }

    /**
     * 输出json格式的结果
     */
    protected function _returnResult($ArrayResult)
    {
        exit(json_encode($ArrayResult));
    }

    /**
     * 输出提示信息到接口
     */
    protected function _showMsg($flag, $msg = FALSE)
    {
        if($msg)
            return $this->_returnResult(array('flag' => (string)$flag, 'msg' => $msg));
        else 
            return $this->_returnResult(array('flag' => (string)$flag));
    }
}