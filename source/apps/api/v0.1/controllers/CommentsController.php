<?php

namespace Appserver\v1\Controllers;
use Appserver\Mdu\Modules\CommentsModule as Comments;

class CommentsController extends ControllerBase
{
    const SUCCESS = '1';
    const INVALID_OPERATE = 11111;

    private $comments;
    private $userInfo;

    public function initialize()
    {
        $this->comments = new Comments;
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
    }
    
    /**
     * 添加评论
     */
    public function addAction()
    {
        $res = $this->comments->add(
            $this->_sanReq['token'],
            $this->_sanReq['locus_id'],
            $this->_sanReq['lc_content'], isset($this->_sanReq['lc_uid']) ? $this->_sanReq['lc_uid'] : ''
        );
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 删除评论
     */
    public function delAction()
    {
        $res = $this->comments->delComment($this->userInfo['uid'], $this->_sanReq['lc_id']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 评论列表
     */
    public function listAction()
    {
        //如果locusid或者count为0，这说明操作时非法的
        if($this->_sanReq['locus_id'] == 0 || $this->_sanReq['count'] == 0)
            $this->showMsg(self::INVALID_OPERATE, $this->di['flagmsg'][$res][self::INVALID_OPERATE]);
        $this->_returnResult(
            $this->comments->getCommentList(
                $this->_sanReq['locus_id'],
                $this->_sanReq['count'],
                isset($this->_sanReq['max_id']) ? $this->_sanReq['max_id'] : ''
            )
        );
    }
}
