<?php

namespace Appserver\Mdu\Models;

class VerModel extends ModelBase
{
    /**
     * 获取最新的软件版本信息
     * 
     */
    public function getVerByType($type)
    {
        $query = $this->db->query('SELECT `av_version` as version, `av_logs` as logs, `av_url` ' .
        'as url FROM `cloud_app_version` WHERE `av_type` = ? ORDER BY `av_id` DESC LIMIT 1',
            array(
                $type
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }
}
