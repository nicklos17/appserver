<?php

class ServerTest extends \UnitTestCase
{
    public function __construct()
    {
        parent::setUp();
        // your code here
    }

    public function tearDown()
    {
        // your code here
    }

    public function testDb()
    {
        // $cfg = include ROOT_PATH . '/../config/database.php';
        // print_r($cfg);

        // $params = array('adapter', 'host', 'username', 'password', 'dbname');
        // foreach ($params as $param) {
        //     $this->assertArrayHasKey(
        //         $param, $cfg['database'], 'db缺少' . $param . '配置'
        //     );
        // }
        // $dbCfg = $cfg['database'];
        // $dsn = '' . strtolower($dbCfg['adapter']) . ':host=' . $dbCfg['host'] . ';dbname=' . $dbCfg['dbname'] . '';

        // new PDO($dsn, $dbCfg['username'], $dbCfg['password']);
    }

    public function testRedis()
    {
        $this->assertTrue(in_array('redis', get_loaded_extensions()), '缺少redis extension');

        $cfg = include ROOT_PATH . '/../config/sysconfig.php';
        $params = array('server', 'port', 'timeout');
        foreach ($params as $param) {
            $this->assertArrayHasKey(
                $param, $cfg['redisConf'], 'redis缺少' . $param . '配置'
            );
        }

        $r = new redis();
        $this->assertTrue(
            $r->connect($cfg['redisConf']['server'], $cfg['redisConf']['port'], $cfg['redisConf']['timeout']),
            'redis连接失败'
        );
    }

    public function testSwoole()
    {
        $this->assertTrue(in_array('swoole', get_loaded_extensions()), '缺少swoole extension');

        $cfg = include ROOT_PATH . '/../config/sysconfig.php';
        $params = array('ip', 'port');
        foreach ($params as $param) {
            $this->assertTrue(
                array_key_exists($param, $cfg['swooleConfig']) && !empty($cfg['swooleConfig'][$param]),
                'swoole缺少' . $param . '配置'
            );
        }

        // 测试连接
        $swoole = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $this->assertTrue(
            $swoole->connect($cfg['swooleConfig']['ip'], $cfg['swooleConfig']['port']),
            'swoole连接失败'
        );

        // 测试swoole数据传输
        $this->assertTrue(
            $swoole->send(json_encode(array('cmd' => 'checkMobi', 'args' => 18611740380))),
            'swoole send失败'
        );
        $this->assertTrue(
            !empty($swoole->recv()),
            'swoole recv失败'
        );
    }
}