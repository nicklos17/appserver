<?php
namespace Test\v1;

use Appserver\v1\Controllers\BabyController as BabyController;

class BabyControllerTest extends \UnitTestCase
{
    protected $babyController;

    public function __construct()
    {
        parent::setUp();
        $this->ctrl = new BabyController;
    }

    public function testAddAction()
    {
        $babyMdu = $this->getMockBuilder('\\Appserver\\Mdu\\Modules\\BabyModule')->getMock();
        $babyMdu->expects($this->any())->method("addBaby")->willReturn(1);

        $this->ctrl->_sanReq['name'] = 'zy';
        $this->ctrl->_sanReq['sex'] = 'f';
        $this->ctrl->_sanReq['birthday'] = '1989';
        $this->ctrl->baby = $babyMdu;
        $this->ctrl->addAction();

        $this->expectOutputString('');
    }
}
