<?php

namespace Appserver\Mdu\Models;

class CheckinlogsModel extends ModelBase
{

    /**
     * 签到日志
     * @param str $uid   用户id
     * @param str $checkinTime  签到时间
     */
    public function addCheckin($uid, $checkinTime, $coins)
    {
        $this->db->execute('INSERT INTO `cloud_checkin_logs` (`u_id`, `cl_addtime`, `cl_coins`) VALUES (?, ?, ?)',
                    array(
                        $uid,
                        $checkinTime,
                        $coins
                    )
                );
        return $this->db->affectedRows();
    }
}