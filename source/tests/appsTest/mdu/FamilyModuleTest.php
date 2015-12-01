<?php
namespace Test\mdu;

class FamilyModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const FAILED_UPDATE = 22222;
    const FAILED_GET = 33333;
    const DELELE_BABY_FAILED = 11082;
    const FAILED_UNBIND_REL = 11083;
    const BABY_HAS_HOST = 10075;
    const FAILED_ADD_FAMILY = 10052;
    const NO_OAUTH = 99999;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\FamilyModule;
    }

    public function testCheckRelation()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 100;
        $babyId = 200;
        $this->assertEquals($uid, $this->mdu->checkRelation($uid, $babyId)['u_id']);
    }

    public function testDelBaby()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $babyId = 201;
        $delTime = time();
        $this->assertEquals(self::SUCCESS, $this->mdu->delBaby($babyId, $delTime));
        $pdo = self::pdo()->prepare("SELECT * FROM `cloud_family` WHERE `baby_id` = ?");
        $pdo->execute(array($babyId));
        $res = $pdo->fetch();
        // family_status   = = 3  删除状态
        $this->assertEquals(3, $res['family_status']);
    }

    public function testCancelRel()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 102;
        $babyId = 202;
        $delTime = time();

        $this->assertEquals(self::SUCCESS, $this->mdu->cancelRel($babyId, $uid, $delTime));
        $pdo = self::pdo()->prepare("SELECT * FROM `cloud_family` WHERE `baby_id` = ? AND `u_id` = ?");
        $pdo->execute(array($babyId, $uid));
        $res = $pdo->fetch();
        // family_status   = = 3  解除状态
        $this->assertEquals(3, $res['family_status']);
    }

    public function testGetUserInfo()
    {

    }

    public function testGetUserInfoByMobi()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $mobi = '15280222222';
        $userInfo = $this->mdu->getUserInfoByMobi($mobi);
        $this->assertCount(1, $userInfo['data']);
    }

    public function testUserInfoByIds()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $ids = '100, 101';
        $userInfo = $this->mdu->userInfoByIds($ids);
        $this->assertCount(2, $userInfo['data']);
    }

    public function testIssetHost()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        // 监护设置已存在
        $this->assertEquals(self::BABY_HAS_HOST, $this->mdu->issetHost(204));
        // 设置成功
        $this->assertEquals(self::SUCCESS, $this->mdu->issetHost(203));
    }

    public function testRddRel()
    {
        $uid = 105;
        $babyId = 205;
        $roleName = "妈妈";
        $this->mdu->addRel($babyId, $uid, $roleName, '1',  time(), '1');

        $pdo = self::pdo()->prepare("SELECT * FROM `cloud_family` WHERE `baby_id` = ? AND `u_id` = ?");
        $pdo->execute(array($babyId, $uid));
        $res = $pdo->fetch();
        $this->assertEquals($uid, $res['u_id']);
        $this->assertEquals($babyId, $res['baby_id']);
        $this->assertEquals($roleName, $res['family_rolename']);
    }

    public function testGuardian()
    {

    }

    public function testShowFamList()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $babyId = 206;
        $count = 10;
        $this->assertCount(2, $this->mdu->showFamList($babyId, $count));
    }
}