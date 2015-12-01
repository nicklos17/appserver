<?php

namespace Appserver\Mdu\Modules;

class MsgModule extends ModuleBase
{
    const SUCCESS = '1';
    protected $msg;

    public function __construct()
    {
        $this->msg = $this->initModel('\Appserver\Mdu\Models\MsgModel');
    }

    public function getMsgList($babyId, $count, $sinceId, $maxId, $type)
    {
        if($sinceId == '' && $maxId == '')
            $msgList = $this->msg->getMsgInfo($babyId, $type, $count);
        elseif($sinceId && $maxId == '')
            $msgList = $this->msg->getMsgBySinceId($babyId, $sinceId, $type, $count);
        else
        {
            $result = $this->msg->getMsgInfo($babyId, $type, $count);
            $msgList = array();
            if($result)
            {
                //拿最新数据的最后一条id和请求的this->_sanReq['max_id']比较，如果最后一条id小于this->_sanReq['max_id'],则返回最新的数据到this->_sanReq['max_id']这段数据，反之则返回全部最新的数据
                $num = sizeof($result) -1;
                //如果最新的赞id等于请求的this->_sanReq['max_id']，说明还没有新的赞生成
                if($result['0']['msg_id'] == $maxId)
                    $msgList = array();
                else
                {
                    if($result[$num]['msg_id'] <= $maxId)
                        $msgList = $this->msg->getMsgByMaxId($babyId, $maxId, $type, $count);
                    else
                        $msgList = $result;
                }
            }
        }

        if(!empty($msgList))
        {
            $msgUrl = $this->di['sysconfig']['msgsPicServer'] . '/' . $this->di['sysconfig']['msgPic'];
            foreach($msgList as $k => $v)
            {
                $msgList[$k]['pic'] = $msgUrl . '/' .  $v['msg_type'] . '.png';
                $msgList[$k]['title'] =  $this->di['sysconfig']['msgTitle'][$v['msg_type']];
            }
        }
        return $msgList;
    }
}