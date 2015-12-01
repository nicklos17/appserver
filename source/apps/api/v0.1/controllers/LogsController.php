<?php

namespace Appserver\v1\Controllers;

use Appserver\Utils\Common;

class LogsController extends ControllerBase
{
    const SUCCESS = '1';
    const INVALID_CONTENT = 66666;
    const NON_FILE = 10098;
    const FILE_TOO_BIG = 10099;

    /**
     * [报告android客户端错误]
     * @return [type] [description]
     */
    public function androidAction()
    {
        //手机型号
        $mode = isset($this->_sanReq['mode']) ? $this->_sanReq['mode'] : '';
        $filename =  $mode . '_' . $this->_sanReq['time'];

        if($_FILES)
        {
            //判断文件大小
            if($_FILES['logs']['size'] > 50000)
            {
                return $this->_showMsg(self::FILE_TOO_BIG, $this->di['flagmsg'][self::FILE_TOO_BIG]);
            }
            //判断文件类型
            if(pathinfo($_FILES['logs']['name'])['extension'] != 'log')
            {
                return $this->_showMsg(self::INVALID_CONTENT, $this->di['flagmsg'][self::INVALID_CONTENT]);
            }

            $url = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . $this->di['sysconfig']['clientErrorLogs'];

            if(!file_exists($url))
            {
                Common::_createdir($url);
            }
            move_uploaded_file($_FILES['logs']['tmp_name'], $url . '/' . $filename . '.log');

            $this->_showMsg(self::SUCCESS);
        }
        else
            return $this->_showMsg(self::NON_FILE, $this->di['flagmsg'][self::NON_FILE]);
    }
}