<?php
namespace Test\mdu;

class VerModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const FAILED_GET = 22222;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\VerModule;
    }

    public function testGetSoftVerInfo()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $type = 1;
        // 类型type不存在
        $this->assertEquals(self::FAILED_GET, $this->mdu->getSoftVerInfo(3, '1.0,1'));
        // 存在，获取高版本号
        $this->assertEquals('1.1.1', $this->mdu->getSoftVerInfo($type, '1.0.1')['verlist'][0]['version']);
    }

    // public function testGetHardInfo()
    // {
    //     $this->initData(__CLASS__, __FUNCTION__);
    //     $a = $this->mdu->getHardInfo(time(), 100);
    //     var_dump($a);
    // }
}