<?php
use Phalcon\DI,
    \Phalcon\Test\UnitTestCase as PhalconTestCase;

abstract class UnitTestCase extends PhalconTestCase {

    /**
     * @var \Voice\Cache
     */
    protected $_cache;

    /**
     * @var \Phalcon\Config
     */
    protected $_config;

    protected $_ctrl = 'index';
    protected $_act = 'index';
    protected $ver;
    /**
     * @var bool
     */
    private $_loaded = false;
    static private $pdoAppServ = null;
    static private $pdoUcenter = null;
    protected $conn = null;
    protected $di;

    public function setUp(Phalcon\DiInterface $di = NULL, Phalcon\Config $config = NULL) {
        // Load any additional services that might be required during testing
        include __DIR__ . '/../config/services.php';
        include __DIR__ . '/../config/loader.php';
        // $di = DI::getDefault();
        // get any DI components here. If you have a config, be sure to pass it to the parent
        $di->set('dispatcher', function(){
            $dispatcher = new Phalcon\Mvc\Dispatcher();
            $dispatcher->setControllerName($this->_ctrl);
            $dispatcher->setActionName($this->_act);
            return $dispatcher;
        }, true);
        $this->di = $di;
        parent::setUp($di);

        $this->_loaded = true;
    }

    // final public function getConnection()
    // {
    //     $dbCfg = include __DIR__ . '/../config/database.php';

    //     if ($this->conn === null) {
    //         if (self::$pdo == null) {
    //             $dsn = 'mysql:dbname=' . $dbCfg->database->dbname . ";host=" . $dbCfg->database->host;
    //             self::$pdo = new PDO($dsn, $dbCfg->database->username, $dbCfg->database->password);
    //         }
    //         $this->conn = $this->createDefaultDBConnection(self::$pdo, $dbCfg->database->dbname);
    //     }
    //     return $this->conn;
    // }

    /**
     * Check if the test case is setup properly
     * @throws \PHPUnit_Framework_IncompleteTestError;
     */
    public function __destruct() {
        if(!$this->_loaded) {
            throw new \PHPUnit_Framework_IncompleteTestError('Please run parent::setUp().');
        }
    }

    public function setCtrlAct($ctrl, $act)
    {
        $this->_ctrl = $ctrl;
        $this->_act = $act;
    }

    static public function pdo($type = '')
    {
        switch ($type) {
            case 'ucenter':
                if (self::$pdoUcenter == null) {
                    $dsn = 'mysql:dbname=cloud_ucenter;host=192.168.59.103:49154';
                    self::$pdoUcenter = new \PDO($dsn, 'root', '');
                    // self::$pdoUcenter = new \PDO($dsn, 'yunduo', 'yunduo123456');
                    self::$pdoUcenter->exec("SET CHARACTER SET UTF8");
                }
                return self::$pdoUcenter;
                break;
            case 'appserv':
            default:
                if (self::$pdoAppServ == null) {
                    $dbCfg = include __DIR__ . '/../config/database.php';
                    $dsn = 'mysql:dbname=' . $dbCfg->database->dbname . ";host=" . $dbCfg->database->host;
                    self::$pdoAppServ = new PDO($dsn, $dbCfg->database->username, $dbCfg->database->password);
                    self::$pdoAppServ->exec("SET CHARACTER SET UTF8"); 
                }
                return self::$pdoAppServ;
                break;
        }
        return self::$pdo;
    }

    public function initData($className, $funcName)
    {
        $expArr = explode('\\', $className);
        $fileName = array_pop($expArr);
        $xmlFile = 'initData/mdu/' . $fileName . '.xml';
        if (file_exists($xmlFile)) {
            $initData = simplexml_load_file($xmlFile);

            foreach ($initData->$funcName as $value) {
                $table = $value->attributes()->table;
                $method = $value->attributes()->method;
                $dbAdapter = $value->attributes()->dbAdapter ?: '';
                if (!$table || !$method) continue;
                
                $sql = $this->_sql($method, $table, $value->data);
                self::pdo($dbAdapter)->exec($sql);
                if(self::pdo($dbAdapter)->errorCode() != '00000') {
                    $this->fail('测试数据初始化失败: errCode : '.self::pdo($dbAdapter)->errorCode().'    sql:'. $sql);
                }
            }
        }
        
        return true;
    }

    private function _sql($method, $table, $data)
    {
        $sql = '';
        switch($method){
            case 'insert':
                $fields = '';
                $values = '';
                foreach ((array)$data as $field => $value) {
                    $fields .= $field . ',';
                    $values .= $value . ',';
                }
                $fields = rtrim($fields, ',');
                $values = rtrim($values, ',');
                $sql = "INSERT INTO {$table}({$fields}) VALUES({$values})";
                break;
            case 'update':
                    
                break;
            case 'delete':
                $sql = "DELETE FROM {$table} WHERE " . $data->where;
                break;
            case 'truncate':
                $sql = "TRUNCATE TABLE {$table}";
                break;
        }
        return $sql;
    }
}