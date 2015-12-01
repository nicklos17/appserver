<?php
namespace Test\mdu;

use Appserver\Mdu\Modules\CaptchaModule as CaptchaModule;

class CaptchaModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const FAILED = 10041;
    const GET_TOO_CAPTCHA = 10044;
    const EXIST_MOBILE = 10003;
    const NON_EXIST_MOBILE = '00001';
    const ERROR_CAPTCHA = 10005;
    const INVALID_CAPTCHA = '00002';

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new CaptchaModule;
        $this->mdu->captcha = new \Appserver\Mdu\Models\CaptchaModel;

        parent::pdo('ucenter')->exec('DELETE FROM cloud_users WHERE `u_mobi` = "15280255990"');
        parent::pdo()->exec('DELETE FROM cloud_mobi_captcha WHERE `mc_mobi` = "15280255990"');
        parent::pdo('ucenter')->exec('INSERT INTO cloud_users(`u_name`, `u_pass`, `u_mobi`) VALUES("test", "asdf", "15280255990")');

        parent::pdo('ucenter')->exec('DELETE FROM cloud_users WHERE `u_mobi` = "15280255991"');
        parent::pdo()->exec('DELETE FROM cloud_mobi_captcha WHERE `mc_mobi` = "15280255991"');

        parent::pdo('ucenter')->exec('DELETE FROM cloud_users WHERE `u_mobi` = "15280255992"');
    }

    public function testMakeCaptcha()
    {
        $this->assertEquals(self::EXIST_MOBILE, $this->mdu->makeCaptcha(1, '15280255990'));
        $this->assertEquals(self::EXIST_MOBILE, $this->mdu->makeCaptcha(11, '15280255990'));

        $this->assertEquals(self::NON_EXIST_MOBILE, $this->mdu->makeCaptcha(3, '15280255992'));
        $this->assertEquals(self::NON_EXIST_MOBILE, $this->mdu->makeCaptcha(7, '15280255992'));
        $this->assertEquals(self::NON_EXIST_MOBILE, $this->mdu->makeCaptcha(9, '15280255992'));

        $this->assertEquals(self::SUCCESS, $this->mdu->makeCaptcha(1, '15280255991'));
        $this->assertEquals(self::GET_TOO_CAPTCHA, $this->mdu->makeCaptcha(1, '15280255991'));
    }

    /**
     * @depends testMakeCaptcha
     */
    public function testCheckCaptcha()
    {
        $capTime = $_SERVER['REQUEST_TIME'];
        
        $this->assertEquals(self::EXIST_MOBILE, $this->mdu->checkCaptcha('15280255990', 1, '1234', $capTime));
        $this->assertEquals(self::EXIST_MOBILE, $this->mdu->checkCaptcha('15280255990', 11, '1234', $capTime));

        $this->assertEquals(self::NON_EXIST_MOBILE, $this->mdu->checkCaptcha('15280255992', 3, '1234', $capTime));
        $this->assertEquals(self::NON_EXIST_MOBILE, $this->mdu->checkCaptcha('15280255992', 7, '1234', $capTime));
        $this->assertEquals(self::NON_EXIST_MOBILE, $this->mdu->checkCaptcha('15280255992', 9, '1234', $capTime));

        parent::pdo()->exec('UPDATE cloud_mobi_captcha SET `mc_captcha` = "6789", `mc_validtime` = 10 WHERE `mc_mobi` = "15280255991"');
        $this->assertEquals(self::ERROR_CAPTCHA, $this->mdu->checkCaptcha('15280255991', 1, '1234', $capTime));
        $this->assertEquals(self::INVALID_CAPTCHA, $this->mdu->checkCaptcha('15280255991', 1, '6789', $capTime));

        // 当前时间小于验证码添加时间
        $mcAddtime = $capTime + 60;
        parent::pdo()->exec('UPDATE cloud_mobi_captcha SET `mc_validtime` = 0, `mc_addtime` = ' . $mcAddtime . ' WHERE `mc_mobi` = "15280255991"');
        $this->assertEquals(self::INVALID_CAPTCHA, $this->mdu->checkCaptcha('15280255991', 1, '6789', $capTime));
        // 验证码过期
        $mcAddtime = $capTime - $this->di['sysconfig']['capTime'] - 1000;
        parent::pdo()->exec('UPDATE cloud_mobi_captcha SET `mc_validtime` = 0, `mc_addtime` = ' . $mcAddtime . ' WHERE `mc_mobi` = "15280255991"');
        $this->assertEquals(self::INVALID_CAPTCHA, $this->mdu->checkCaptcha('15280255991', 1, '6789', $capTime));
        // 验证码有效
        $mcAddtime = $capTime - $this->di['sysconfig']['capTime'] + 1000;
        parent::pdo()->exec('UPDATE cloud_mobi_captcha SET `mc_validtime` = 0, `mc_addtime` = ' . $mcAddtime . ' WHERE `mc_mobi` = "15280255991"');
        $this->assertEquals(self::SUCCESS, $this->mdu->checkCaptcha('15280255991', 1, '6789', $capTime));
    }
}
