<?php
namespace Test\mdu;

use Appserver\Mdu\Modules\DevicesModule as DevicesModule;

class DevicesModuleTest extends \UnitTestCase
{
    const EMPTY_GET = 0;
    const SUCCESS = '1';
    const FAILED_ADD_DEV = 10015;
    const NOT_USER_DEV = 10028;
    const FAILED_DEL_DEV = 10029;
    const NOT_BIND_BABY = 10030;
    const ADDED_BY_OTHER = 10031;
    const SHOE_ADDED = 10032;
    const NON_EXIST_SHOE = 10034;
    const FAILED_UNBIND = 10036;
    const SHOE_BINDED = 10037;
    const FAILED_CHANGE_MODE = 10039;
    const FAILED_UPDATE_DATA = 33333;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new DevicesModule;
    }

    public function testShoeListByUid()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 100;
        $count = 10;
        $this->assertCount(1, $this->mdu->shoeListByUid($uid, $count));
    }

    /**
     * @depends testShoeListByUid
     */
    public function testShoeListByBabyId()
    {
        $babyId = 1000;
        $this->assertCount(1, $this->mdu->shoeListByBabyId($babyId));
    }

    public function testShoeListUnbind()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 101;
        $this->assertCount(1, $this->mdu->shoeListUnbind($uid));
    }

    public function testBabyCount()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 102;
        $bindShoe = 200;
        $unbindShoe = 201;
        // 已绑定
        $this->assertEquals(self::SHOE_BINDED, $this->mdu->babyCount($uid, $bindShoe));
        
        // 未绑定
        $this->assertEquals(self::EMPTY_GET, $this->mdu->babyCount($uid, $unbindShoe));
    }

    public function testGetShoeMode()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 103;
        $shoeId = 203;
        $workMode = 3;

        $this->assertEquals($workMode, $this->mdu->getShoeMode($uid, $shoeId)['dev_work_mode']);
    }

    public function testBindBabyShoe()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $babyId = 204;
        $shoeId = 304;
        $this->assertEquals(self::SUCCESS, $this->mdu->bindBabyShoe($babyId, $shoeId));
    }

    /**
     * @depends testBindBabyShoe
     */
    public function testUnbindBabyShoe()
    {
        $uid = 104;
        $shoeId = 304;
        $equalData = array('flag' => 1, 'baby_id' => 204, 'devs' => 1);
        $this->assertEquals($equalData, $this->mdu->unbindBabyShoe($uid, $shoeId));
    }

    public function testGetUidByDev()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 105;
        $shoeId = 305;
        $this->assertEquals($uid, $this->mdu->getUidByDev($shoeId)['u_id']);
    }

    public function testDevOff()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $shoeId = 306;
        $this->assertEquals(1, $this->mdu->devOff($shoeId));
    }

    public function testGetBabyIdByShoeId()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 107;
        $babyId = 207;
        $shoeId = 307;
        $this->assertEquals($babyId, $this->mdu->getBabyIdByShoeId($uid, $shoeId)['baby_id']);
    }
}
