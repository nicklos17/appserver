<?php
namespace Test\v1;

use Appserver\v1\Controllers\UserController as UserController;

class UserControllerTest extends \UnitTestCase
{
    public function __construct()
    {
        parent::setUp();
        $this->ctrl = new UserController;
    }

    public function testRegAction()
    {
        $userMdu = $this->getMockBuilder('\\Appserver\\Mdu\\Modules\\UserModule')->disableOriginalConstructor()->getMock();
        $userMdu->expects($this->any())->method("reg")->willReturn(array());

        $this->ctrl->user = $userMdu;
        $this->ctrl->_sanReq['mobi'] = '15280255990';
        $this->ctrl->_sanReq['captcha'] = '1234';
        $this->ctrl->_sanReq['pass'] = '1234';
        $this->ctrl->_sanReq['type'] = '1';
        $this->ctrl->regAction();

        // $this->expectOutputString('{"flag":"1"}');
    }
}
