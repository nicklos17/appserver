<?php

namespace Appserver\Mdu\Models;

class ModelBase extends \Phalcon\Mvc\Model
{
    protected $di;
    protected $db;

    public function onConstruct()
    {
        $this->di = self::getDI();
        $this->db = $this->di['db'];
    }
}