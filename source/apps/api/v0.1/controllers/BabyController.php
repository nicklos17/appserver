<?php
namespace Appserver\v1\Controllers;

use Appserver\Utils\ImgUpload,
       Appserver\Utils\Common,
       Appserver\Mdu\Modules\BabyModule as Baby,
       Appserver\Mdu\Modules\FamilyModule as Family;

class BabyController extends ControllerBase
{
    const NOT_BABY_FAMILY = 10079;
    const INVALID_OPERATE = 11111;
    const SUCCESS = '1';
    const BABY_BIND_SHOE = 11084;

    public $baby;
    public $family;
    public $userInfo;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->baby = new Baby;
        $this->family = new Family;
    }

    /**
     * 添加宝贝信息
     */
    public function addAction()
    {
        $res = $this->baby->addBaby($this->userInfo['uid'], $this->_sanReq['name'], $this->_sanReq['sex'],
        $this->_sanReq['birthday'], $_SERVER['REQUEST_TIME'], $_FILES, '');
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 添加宝贝信息
     */
    public function addrelAction()
    {
        $res = $this->baby->addBaby($this->userInfo['uid'], $this->_sanReq['name'], $this->_sanReq['sex'],
        $this->_sanReq['birthday'], $_SERVER['REQUEST_TIME'], $_FILES, $this->_sanReq['rel']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 宝贝列表
     */
    public function listAction($code = '')
    {
        if($code == '1')
            $result = $this->baby->getListByUidDev($this->userInfo['uid'], $this->_sanReq['count']);
        elseif($code == '')
            $result = $this->baby->getListByUid($this->userInfo['uid'] , $this->_sanReq['count']);
        else
            $this->_showMsg(self::INVALID_OPERATE, $this->di['flagmsg'][self::INVALID_OPERATE]);

        if(!empty($result))
        {
            foreach($result as $key => $value)
            {
                $result[$key]['baby_pic'] = $this->di['sysconfig']['babyPicServer'] . $value['baby_pic'];
            }
        }

        return $this->_returnResult(array('flag' => self::SUCCESS, 'babylist' =>$result));
    }

    /**
     * 宝贝编辑
     */
    public function editAction()
    {
        //判断是否有操作权限
        $this->_oauthrity($this->userInfo['uid'], $this->_sanReq['baby_id']);
        $data = array();
        if(!empty($_FILES['file']['tmp_name']))
            $data['baby_pic'] = $_FILES;

        $data['baby_sex'] = $this->_sanReq['sex'];
        $data['baby_nick'] = $this->_sanReq['nick'];
        $data['baby_id'] = $this->_sanReq['baby_id'];
        $data['baby_birthday'] = $this->_sanReq['birthday'];
        $res = $this->baby->editBaby($data);
        if($res['ret'] == self::SUCCESS)
        {
            if($res['data'])
                $this->_returnResult(array('flag' => self::SUCCESS, 'baby_pic' => $res['data']));
            else
                $this->_returnResult(array('flag' => self::SUCCESS));
        }
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res['ret']]);
    }

    /**
     * 返回宝贝过期的童鞋个数
     */
    public function invalidAction()
    {
        $shoes = $this->baby->getExpireDevs($this->_sanReq['baby_id'], $_SERVER['REQUEST_TIME']);
        $num = sizeof($shoes);
        if($num == 0)
        {
            $shoeId = '';
            $num = '';
            $target = '';
        }
        elseif($num == 1)
        {
            $shoeId = $shoes[0]['dev_id'];
            //获取截止续费的时间戳:判断设备是否可以续费 target为1可续，为3不可续
            $deadline = Common::expires($shoes[0]['dev_expires']);
            if($_SERVER['REQUEST_TIME'] <= $deadline)
                $target = self::SUCCESS;
            else
                $target = '3';
        }
        else
        {
            $shoeId = '';
            $target = '';
        }
        $this->_returnResult(array('flag' => self::SUCCESS, 'shoe_id' => $shoeId,
            'shoe_count' => (string)$num, 'target' => $target));
    }

    /**
     * 主号删除宝贝，或者监护人和其他亲情号取消关注
     */
    public function cancelAction()
    {
        $rel = $this->family->checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);
        if(empty($rel))
            $this->_showMsg((string)self::NOT_BABY_FAMILY, $this->di['flagmsg'][self::NOT_BABY_FAMILY]);

        //如果关系是主号，则为删除宝贝，否则为取消关注
        if($rel['family_relation'] == 1)
        {
            //如果宝贝绑有鞋子，则不让删除
            if($this->baby->babyIdGetShoeId($this->_sanReq['baby_id']))
                $this->_showMsg(self::BABY_BIND_SHOE, $this->di['flagmsg'][self::BABY_BIND_SHOE]);

            $res = $this->family->delBaby($this->_sanReq['baby_id'], $_SERVER['REQUEST_TIME']);
            if($res == 1)
                $this->_showMsg($res);
            else
                $this->_showMsg($res, $this->di['flagmsg'][$res]);
        }
        else
        {
            //如果关系不是主号，则只需要取消该用户与宝贝的关系即可
            if($res = $this->family->cancelRel($this->_sanReq['baby_id'], $this->userInfo['uid'], $_SERVER['REQUEST_TIME']) == self::SUCCESS)
                $this->_showMsg($res);
            else
                $this->_showMsg($res, $this->di['flagmsg'][$res]);
        }
    }

    /**
     * [设置宝贝目标步数]
     * @return [type] [description]
     */
    public function stepsAction()
    {
        //判断是否有操作权限
        $this->_oauthrity($this->userInfo['uid'], $this->_sanReq['baby_id']);
        if(($res = $this->baby->setSteps($this->_getToken($this->_sanReq['token'])['uid'], $this->_sanReq['baby_id'], $this->_sanReq['steps'])) == self::SUCCESS)
            $this->_showMsg($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}