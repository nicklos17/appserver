<?php
namespace Test\mdu;

class LocateModuleTest extends \UnitTestCase
{
    const NON_LOCUS = 10062;
    const NON_BABY = 10020;
    const NON_LOCATE = 10024;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\LocateModule;
    }

    public function testLocateInfo()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 100;
        $babyId = 200;
        $startTime = '1435766644';
        $this->assertCount(1, $this->mdu->locateInfo($uid, $babyId, $startTime)['locatelist']);
        $this->assertCount(0, $this->mdu->locateInfo($uid, $babyId, '')['locatelist']);
    }

}