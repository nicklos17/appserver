<?php
namespace Test\mdu;

class FencesModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const FAILED_UPDATE_FENCES = 10080;
    const FAILED_DEL_FENCES = 10081;
    // const FAILED_GET = 33333;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\FencesModule;
    }

    public function testAddFences()
    {
        $babyId = 200;
        $coordinates = '24.48829,118.182456';
        $name = '家';
        $radius = '100';
        $place = '福建省厦门市思明区莲前街道虎仔山';
        $this->assertEquals(self::SUCCESS, $this->mdu->addFences($babyId, $coordinates, $name, $radius, $place, time(), time()));
    }

    /**
     * @depends testAddFences
     */
    public function testEditFences()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $fenceId = 400;
        $coordinates = '24.48829,118.182456';
        $name = '公司';
        $radius = '75';
        $place = '福建省厦门市思明区软件园二期';
        $this->assertEquals(self::SUCCESS, $this->mdu->editFences($fenceId, $coordinates, $name, $radius, $place, time(), time()));
    }

    public function testDelFences()
    {
        $fenceId = 400;
        $this->assertEquals(self::SUCCESS, $this->mdu->delFences($fenceId, time()));
    }

    public function testShowFenList()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $babyId = 201;
        $this->assertCount(1, $this->mdu->showFenList($babyId, 10));
    }
}