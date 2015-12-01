<?php

namespace Appserver\Utils\Tasks;

interface TasksInterface
{
    /**
     * 查询用户是否满足完成任务的条件
     * @param unknown $uid
     * @param unknown $taskInfo
     */
    public function checkTaskComplete($uid, $condition);

    /**
     * 添加任务
     * @param str $uid
     * @param array $taskInfo 任务信息
     */
    public function addTask($uid, $taskInfo);

//  public function getList($uid);

//  public function setProgress($uid, $tid);

    /**
     * 点击完成任务
     * @param unknown $uid 用户id
     * @param unknown $tid 任务id
     * @param unknown $coin 奖励云币
     */
    public function completeTask($uid, $tid);

    /**
     * 领取奖励
     * @param unknown $uid
     * @param unknown $coin
     */
    public function receive($uid, $coin);

}