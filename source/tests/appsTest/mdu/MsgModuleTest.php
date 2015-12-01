<?php
namespace Test\mdu;

class MsgModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\MsgModule;
    }

    public function testGetMsgList()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $babyId = 200;
        $type = array(11, 15);
        $this->assertCount(1, $this->mdu->getMsgList($babyId, $type, 10, '', ''));
    }
}