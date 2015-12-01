<?php
namespace Test\mdu;

class RankModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const GET_BABY_FAILED = 10014;
    const INVALID_OPERATE = 11111;
    const NON_DATA = 22222;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\RankModule;
    }

    public function testGetOldRank()
    {
        $this->initData(__CLASS__, __FUNCTION__);

        // 宝贝不存在
        $this->assertEquals(self::GET_BABY_FAILED, $this->mdu->getOldRank(201));
        // 宝贝存在，获取信息
        $babyId = 200;
        $redisLib = new \Appserver\Utils\RedisLib($this->di);
        $redis = $redisLib->getRedis($this->di);
                
        $equalArr = array(
            'flag' => self::SUCCESS,
            'pic' => $this->di['sysconfig']['babyPicServer']."/static/baby_pic.png",
            'days' => 87,
            'rank' => (string)99,
            'mileage' => (string)ceil(336/1000)
        );
        $this->assertEquals($equalArr, $this->mdu->getOldRank($babyId));
    }

    public function testGetTodayRank()
    {
        $babyId = 201;
        $a = $this->mdu->getTodayRank($babyId, 10);
        // 错误操作
        $this->assertEquals(self::INVALID_OPERATE, $this->mdu->getTodayRank($babyId, 0));
        
        $this->assertEquals(1, $this->mdu->getTodayRank($babyId, 10)['flag']);
    }
}