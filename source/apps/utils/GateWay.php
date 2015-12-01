<?php
namespace Appserver\Utils;

class GateWay
{
    const IDEN_FAILD = 101;
    const CTRL_ERROR = 102;

    public function __construct($di)
    {
        $this->di = $di;
    }

    public function check($ctrl, $act)
    {
        $verConfig = $this->di['verConfig'];
        $ver = $this->di['request']->getPost('ver');

        if (in_array($ver, $verConfig['auth']))
        {
            // todo  身份认证
            $stoken = $this->di['request']->getPost('stoken');
            if($stoken != 'qwertyuiopasdfghjklzxcvbnm')
            {
                $this->errCode = self::IDEN_FAILD;
                return false;
            }
        }

        //判断该接口是否废弃
        if(isset($verConfig['exclude'][$ver]) && in_array($ctrl . ':' . $act, $verConfig['exclude'][$ver]))
        {
            $this->errCode = self::IDEN_FAILD;
            return false;
        }

        if(!$vers = $verConfig['map'][$ctrl . ':' . $act])
        {
            $this->errCode = self::CTRL_ERROR;
            return false;
        }
        $this->ver = in_array($ver, $vers) ? $ver : max($vers);
        return true;
    }

    public function getVer()
    {
        return $this->ver;
    }

    public function errMsg()
    {
        $err = array(
            '101' => 'Identity faild',
            '102' => 'ctrl not exists'
        );
        return $err[$this->errCode];
    }
}