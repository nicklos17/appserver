<?php
namespace Test\mdu;

class UnionModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const NON_EXIST_SHOE = 10034;
    const ADDED_SHOE = 10032;

    const FAILED_UPDATE_DATA = 10040;
    const SHOE_BINDED = 10037;
    const FAILED_UNBIND = 10036;
    const EMPTY_GET = 0;
    const FAILED_CHANGE_MODE = 10039;
    const FAILED_DEL_DEV = 10029;
    const SHOE_ADDED = 10032;
    const FAILED_ADD_DEV = 10015;
    const NEW_USER = 1;
    const NOT_NEW_USER = 3;
    const NON_IMG_UPLOAD =10098;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new \Appserver\Mdu\Modules\UnionModule;
    }

    public function testAdd()
    {
        $uid = 100;
        $nick = 'yd';
        $birthday = microtime('2011-1-1');
        $shoeQr = '816b7d6773de015e';
        $rolename = '妈妈';
        $file = array();
        // no image
        // $this->assertEquals(self::NON_IMG_UPLOAD, $this->mdu->add($uid, $nick, 1, $birthday, time(), $shoeQr, $rolename, $file));
        
        // $a = $this->mdu->add($uid, $nick, 1, $birthday, time(), $shoeQr, $rolename, array('file' => array('tmp_name' => 'dfasdf')));
        // var_dump($a);   
    }

    public function testCheckuser()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 101;
        // 既没有添加过宝贝也没有添加过童鞋
        $this->assertEquals(array('flag' => 1, 'isnew' => self::NEW_USER), $this->mdu->checkuser(102));
        // 非新用户
        $this->assertEquals(array('flag' => 1, 'isnew' => self::NOT_NEW_USER), $this->mdu->checkuser($uid));
    }
}