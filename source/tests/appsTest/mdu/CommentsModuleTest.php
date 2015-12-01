<?php
namespace Test\mdu;

use Appserver\Mdu\Modules\CommentsModule as CommentsModule;

class CommentsModuleTest extends \UnitTestCase
{
    const SUCCESS = 1;
    const NON_LOCUS = 10062;
    const NO_OAUTH = 99999;
    const NON_REPLY_USER = 10089;
    const FAILED_COMMENT = 11065;
    const NOT_USER_COMMENT = 10066;
    const FAILED_UPDATE = 33333;
    const FAILED_DEL = 10067;

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new CommentsModule;
    }

    public function testAdd()
    {
        // $this->mdu->add();
    }

    public function testDelComment()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 100;
        $noneUid = 101;
        $babyId = 200;
        $lcId = 300;
        $noneLcId = 301;

        // 当前在线用户无权限删除该comment
        $this->assertEquals(self::NOT_USER_COMMENT, $this->mdu->delComment($noneUid, $lcId));

        // locus comment 存在    locus 不存在
        $this->assertEquals(self::NON_LOCUS, $this->mdu->delComment($uid, $noneLcId));

        // 删除成功
        $this->assertEquals(array('flag' => self::SUCCESS, 'comments' => 4), $this->mdu->delComment($uid, $lcId));
    }

    public function testGetCommentList()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        // 无评论
        $this->assertCount(0, $this->mdu->getCommentList(402, 10, 0)['commlist']);
        // 有评论
        $this->assertCount(1, $this->mdu->getCommentList(401, 10, 0)['commlist']);
    }
}
