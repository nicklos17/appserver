<?php
namespace Test\mdu;

class PraisesModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const NON_LOCUS = 10062;
    const PRAISED = 10057;
    const FAILED_PRAISE = 10054;
    const GET_EMPTY_DATA = 22222;
    const NO_PRAISE = 10055;
    const FAILED_CANCLE_PRAISE = 10056;
    const FAILED_UPDATE = 33333;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\PraisesModule;
    }

    public function testHit()
    {

    }

}