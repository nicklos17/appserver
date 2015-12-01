<?php
namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\LocateModule as Locate;

class LocateController extends ControllerBase
{

    public $userInfo;
    public $locate;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        //判断用户与宝贝是否有关系
        if(isset($this->_sanReq['baby_id']))
            $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);

        $this->locate = new Locate;
    }

    /**
     * [定位信息]
     * @return [type] [description]
     */
    public function indexAction()
    {
        $this->_returnResult(
            array('flag' => '1', 'locatelist' =>
                $this->locate->locateInfo(
                    $this->userInfo['uid'],
                    $this->_sanReq['baby_id'],
                    isset($this->_sanReq['lasttime']) ? $this->_sanReq['lasttime'] : ''
                )
            )
        );
    }

    /**
     * 返回某一天的轨迹点
     * @return [type] [description]
     */
    public function dayAction()
    {
        $res = $this->locate->showDayLocate($this->_sanReq['locus_id']);
        if(is_array($res))
            $this->_returnResult(array('flag' => '1', 'locustitle' => array($res)));
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 获取宝贝最近一次出现的点
     */
    public function shoecoorAction()
    {
        $res = $this->locate->showNearlyLocate($this->_sanReq['baby_id']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * [点击获取定位点]
     * @return [type] [description]
     */
    public function findAction()
    {
        $res = $this->locate->getNewLocate(
            $this->_sanReq['baby_id'],
            isset($this->_sanReq['type']) ? $this->_sanReq['type'] : ''
        );
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 返回计算轨迹圈的页面
     */
    public function circleAction()
    {
        $fileUrl = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/public' . $this->di['sysconfig']['locateCircleUrl'];
        if(!file_exists($fileUrl))
        {
            $this->showMsg('22222', $this->config->item('flagMsg')['22222']);
        }
        $md = md5_file($fileUrl);
        
        //如果上传和md5值和文件md不相等，则通知客户端需要更新文件
        if($this->_sanReq['md5'] !== $md)
        {
            $content = file_get_contents($fileUrl);
        }
        else
        {
            $content = '';
            $md = '';
        }
        $this->_returnResult(array('flag' => '1', 'content' => $content, 'md5' => $md));
    }

}
