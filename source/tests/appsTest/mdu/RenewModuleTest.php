<?php
namespace Test\mdu;

class RenewModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const FAILED_ADD = 11071;
    const FAILED_OPERATE = 11111;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\RenewModule;
    }

    public function testGetRenew()
    {
        $crId = 300;
        $crStatus = 1;
        $this->mdu->getRenew($crId, $crStatus);
    }
}