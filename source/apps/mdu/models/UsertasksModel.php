<?php

namespace Appserver\Mdu\Models;

class UsertasksModel extends ModelBase
{

    /**
     * 添加任务
     * @param str $uid 用户id
     * @param str $tid 任务id
     * @param str $name 任务名
     * @param str $desc 任务内容
     * @param str $type 任务类型
     * @param str $pic  任务图标
     * @param str $reward  奖励云币
     * @param str $addtime 添加时间
     * @param str $progress 任务当前进度
     * @param str $total 任务总进度
     * @param str $group 任务组
     * @param str $finishtime 任务完成时间
     */
    public function add($uid, $tid, $tcid, $name, $summary, $type, $pic, $status, $reward, $addtime, $progress, $total, $group, $finishtime = '')
    {
        return $this->db->execute('INSERT INTO `cloud_user_tasks` (`u_id`, `t_id`, `tc_id`, ' .
        '`t_name`, `t_summary`, `t_type`, `t_pic`, `ut_status`, `t_reward`, `ut_addtime`, `ut_progress`, `t_total`, `t_group`, `ut_finishtime`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            array(
                $uid,
                $tid,
                $tcid,
                $name,
                $summary,
                $type,
                $pic,
                $status,
                $reward,
                $addtime,
                $progress,
                $total,
                $group,
                $finishtime
            )
        );
    }

    /**
     * 更新任务组的任务进度
     * @param unknown $uid 用户id
     * @param unknown $group 任务组
     * @param unknown $status 任务状态
     */
    public function updateProgress($uid, $group, $status)
    {
        $query = $this->db->query('UPDATE cloud_user_tasks set ut_progress = ut_progress+1 WHERE u_id = ? AND t_group = ? AND ut_status = ?',
                array($uid, $group, $status)
            );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 获取用户已领取的任务
     * @param str $uid 用户id
     * @param str $tcid 任务分类id
     */
    public function receivedTasks($uid, $tcid)
    {
        $query = $this->db->query('SELECT t_id, ut_status as status, t_total, t_group, tc_id, t_name, t_summary as intr, t_type, t_pic, t_reward FROM cloud_user_tasks WHERE (ut_status = 1 OR ut_status = 3) AND u_id = ? AND tc_id = ? order by ut_status asc',
            array($uid, $tcid)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 取出各任务组最进添加的任务信息
     * @param unknown $uid 用户id
     * @param unknown $tcid 分类id
     */
    public function getLastTaskByGroup($uid, $tcid)
    {
        $query = $this->db->query('SELECT s.t_id, s.ut_status, s.t_total, s.t_group FROM (SELECT t_id, ut_status, t_total, t_group FROM cloud_user_tasks WHERE (ut_status = 1 OR ut_status = 3) AND u_id = ? AND tc_id = ? order by t_total desc) s group by t_group',
            array($uid, $tcid)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 检查用户是否已添加过该任务
     * @param unknown $uid
     * @param unknown $tid
     */
    public function checkTask($uid, $tid)
    {
        $query = $this->db->query('SELECT t_id FROM cloud_user_tasks WHERE u_id = ? AND t_id = ?', array($uid, $tid));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 获取用户正在进行的任务的信息
     * @param str $uid 用户id
     * @param str $tid 任务id
     */
    public function getTaskInfo($uid, $tid)
    {
        $query = $this->db->query('SELECT t_id, t_reward, t_type, ut_progress, t_total, ut_status, t_group FROM cloud_user_tasks WHERE u_id = ? AND t_id = ? LIMIT 1',
            array($uid, $tid)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 完成任务
     * @param str $uid 用户id
     * @param str $tid 任务id
     */
    public function complete($uid, $tid, $finishtime)
    {
        $this->db->execute('UPDATE `cloud_user_tasks` SET `ut_status` = ?, `ut_finishtime` = ? ' .
            'WHERE `u_id` = ? AND `t_id` = ?',
            array(
                3,
                $finishtime,
                $uid,
                $tid
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 删除任务
     * 注：只有正在进行中的任务才能删除
     * @param str $uid 用户id
     * @param str $tid 任务id
     */
    public function del($uid, $tid)
    {
        $this->db->execute('DELETE FROM `cloud_user_tasks`WHERE `u_id` = ? AND `t_id` = ?',
            array(
                $uid,
                $tid
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 返回用户任务列表
     * @param str $uid 用户id
     * @param str $status 任务状态
     */
    public function getUserTasks($uid, $status, $count)
    {
        $query = $this->db->query('SELECT t_id, ut_status, t_name as name, t_summary as intr, t_pic as pic, t_reward as reward, ut_addtime as time, '.
                  'ut_progress, t_total FROM cloud_user_tasks WHERE u_id = ? AND ut_status = ? ORDER BY ut_addtime DESC LIMIT ?', array($uid, $status, $count));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据sinceTime返回用户任务列表
     * @param str $uid 用户id
     * @param str $status 任务状态
     */
    public function getUserTasksBySTime($uid, $status, $count, $sTime)
    {
        $query = $this->db->query('SELECT t_id, ut_status, t_name as name, t_summary as intr, t_pic as pic, t_reward as reward, ut_addtime as time, '.
                  'ut_progress, t_total FROM cloud_user_tasks WHERE u_id = ? AND ut_status = ? AND ut_addtime > ? ORDER BY ut_addtime DESC LIMIT ?', array($uid, $status, $sTime, $count));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据maxTime返回用户任务列表
     * @param str $uid 用户id
     * @param str $status 任务状态
     */
    public function getUserTasksByMTime($uid, $status, $count, $maxTime)
    {
        $query = $this->db->query('SELECT t_id, ut_status, t_name as name, t_summary as intr, t_pic as pic, t_reward as reward, ut_addtime as time, '.
                  'ut_progress, t_total FROM cloud_user_tasks WHERE u_id = ? AND ut_status = ? AND ut_addtime < ? ORDER BY ut_addtime DESC LIMIT ?', array($uid, $status, $maxTime, $count));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据任务分组获取
     */
    public function getUserByGroup($group)
    {
        $query = $this->db->query('SELECT u_id, ut_addtime as addtime, t_id FROM cloud_user_tasks WHERE t_group = ? AND ut_status = 1 group by u_id', array($group));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 直接更新任务进度
     * @param unknown $uid
     * @param unknown $group
     */
    public function editProgress($uid, $group, $progress)
    {
        $this->db->query('UPDATE cloud_user_tasks set ut_progress = ? WHERE u_id = ? AND t_group = ? AND ut_status = 1',
                array($progress, $uid, $group));
        return $this->db->affectedRows();
    }
}