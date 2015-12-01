<?php
namespace Test\mdu;

class TasksModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const DEL_FAILED = 11023;
    const INVALID_OPERATE = 11111;
    const NON_TASK = 11021;
    const WORKING = 11030;

    const ADD_FAILED = 11022;
    const NO_FINISHED = 11029;
    const UPDATE_FAILED = 33333;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\TasksModule;
    }

    public function testGetCats()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $this->assertCount(3, $this->mdu->getCats());
    }

    public function testGetTasksList()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 100;
        // 系统任务
        $sysTaskList = $this->mdu->getTasksList($uid, 1)['tasklist'][0];
        $this->assertEquals(1, $sysTaskList['tc_id']);
        $this->assertEquals("设置个人资料(昵称、头像)", $sysTaskList['t_name']);
        $this->assertEquals(10, $sysTaskList['t_reward']);

        // 非系统任务
        $sysTaskList = $this->mdu->getTasksList($uid, 2)['tasklist'][0];
        $this->assertEquals(2, $sysTaskList['tc_id']);
        $this->assertEquals("首次设置宝贝步行目标", $sysTaskList['t_name']);
        $this->assertEquals(5, $sysTaskList['t_reward']);
    }

    /**
     * @dependsd testGetTasksList
     */
    public function testGetUserTasksList()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 100;
        // 未完成任务
        $taskList = $this->mdu->getUserTasksList('', $uid, 10, 0, 0)['usertasks'][0];
        $this->assertEquals(1, $taskList['ut_status']);
        $this->assertEquals("设置个人资料(昵称、头像)", $taskList['name']);
        $this->assertEquals(0, $taskList['ut_progress']);
        // 已完成任务
        $taskList = $this->mdu->getUserTasksList(1, $uid, 10, 0, 0)['usertasks'][0];
        $this->assertEquals(3, $taskList['ut_status']);
        $this->assertEquals("首次设置宝贝步行目标", $taskList['name']);
        $this->assertEquals(1, $taskList['ut_progress']);
    }

    public function testAddTask()
    {
        $uid = 100;
        $token = 'asdf';
        // user task已存在
        $this->assertEquals(self::WORKING, $this->mdu->addTask($uid, 1, $token));
        // task 主信息不存在
        $this->assertEquals(self::NON_TASK, $this->mdu->addTask($uid, 3, $token));
        // SingleTask    无condition
        // $this->assertEquals(self::ADD_FAILED, $this->mdu->addTask($uid, 11, $token));
        // SingleTask    有condition
        // $this->assertEquals(self::ADD_FAILED, $this->mdu->addTask($uid, 11, $token));
        // $a = $this->mdu->addTask($uid, 11, $token);
        // print_r($a);
    }
}