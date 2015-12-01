<?php
namespace Test\mdu;

class MyselfModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const UNTOKEN = '00000';
    const NON_EXIST_MOBILE = '00001';
    const FAILED_GET = 22222;
    const FAILED_SET_DISTURB = 11062;
    const SAME_MODE = 11063;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\MyselfModule;
    }

    public function testGetUserLevelInfo()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        // 用户不存在
        $userInfo = array('uid' => 100, 'mobi' => 15280222222);
        $this->assertEquals(self::NON_EXIST_MOBILE, $this->mdu->getUserLevelInfo($this->di, $userInfo));
        // 用户存在，无云币
        $userInfo = array('uid' => 101, 'mobi' => 15280222223);
        $this->assertEquals(self::NON_EXIST_MOBILE, $this->mdu->getUserLevelInfo($this->di, $userInfo));
        // 用户存在，有云币
        $userInfo = array('uid' => 100, 'mobi' => 15280222223);
        $this->assertEquals(150, $this->mdu->getUserLevelInfo($this->di, $userInfo)['coins']);
    }

    public function testDisturb()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 102;
        // 数据不存在
        $this->assertEquals(self::UNTOKEN, $this->mdu->disturb(103, 1));
        // 同模式
        $this->assertEquals(self::SAME_MODE, $this->mdu->disturb(102, 1));
        // success
        $this->assertEquals(self::SUCCESS, $this->mdu->disturb(102, 3));
    }
}