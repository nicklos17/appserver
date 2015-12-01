<?php
namespace Test\mdu;

class LocusModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const ILLEGAL = 11111;
    const NON_LOCUS = 10062;
    const NON_OAUTH = 99999;
    const FAILED_MARK = 10063;
    const NO_OAUTH = 99999;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\LocusModule;
    }

    public function testGetLocusList()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 100;
        $babyId = 200;
        $this->assertCount(1, $this->mdu->getLocusList($uid, $babyId, 10, '', '')['locuslist']);
    }

    public function testGetCalList()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $babyId = 201;
        $date = '2015-6';   // 1433088000
        $this->assertCount(1, $this->mdu->getCalList($babyId, $date)['callist']);
    }

    public function testMark()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 102;
        $locusId = 302;
        // locus 不存在
        $this->assertEquals(self::NON_LOCUS, $this->mdu->mark($uid, 303, '', ''));
        // mark 成功
        $this->assertEquals(self::SUCCESS, $this->mdu->mark($uid, $locusId, 'locusTitle', '厦门市软件园,维众传媒'));
        // 身份认证失败
        $this->assertEquals(self::NO_OAUTH, $this->mdu->mark(103, $locusId, 'locusTitle', '厦门市软件园,维众传媒'));
    }

    public function testGetMessList()
    {

    }

    public function testGetNewInfo()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 104;
        $locusId = 304;
        // locus 不存在
        $this->assertEquals(self::NON_LOCUS, $this->mdu->getNewInfo($uid, 305));
        // 获取 成功
        $this->assertEquals(1, $this->mdu->getNewInfo($uid, $locusId)['flag']);
        // 身份认证失败
        $this->assertEquals(self::NO_OAUTH, $this->mdu->getNewInfo(105, $locusId));
    }

    public function testGetBabyId()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $babyId = 206;
        $locusId = 306;
        $this->assertEquals($babyId, $this->mdu->getBabyId($locusId)['baby_id']);
    }
}