<?php

namespace Appserver\Mdu\Models;

class TasksModel extends ModelBase
{
    /**
     * 获取所有任务分类
     * @return array
     */
    public function getTaskCats()
    {
        $query = $this->db->query('SELECT tc_id, tc_name, tc_desc as intr, tc_pic FROM cloud_task_cats');
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据分类id获取该分类的所有任务
     * @param str $Tcid
     * @return array
     */
    public function getListByTcid($tcid)
    {
        $query = $this->db->query('SELECT t_id, tc_id, t_name, t_summary as intr, t_type, t_pic, t_reward FROM cloud_tasks WHERE t_status = 1 AND tc_id = ?',
            array($tcid)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据条件获取多任务信息
     * @param unknown $tcid 分类id
     * @param unknown $where
     */
    public function getTaskByWhere($tcid, $where)
    {
        $query = $this->db->query('SELECT t_id, tc_id, t_name, t_summary as intr, t_type, t_pic, t_reward FROM '.
                'cloud_tasks WHERE tc_id = ? AND t_status = 1 AND t_type = 3 AND (' . $where . ') group by t_group order by t_total asc',
                array($tcid)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 取出各任务组最进添加的任务信息
     * @param unknown $uid 用户id
     * @param unknown $tcid 分类id
     */
    public function getLastTaskByGroup($tcid)
    {
        $query = $this->db->query('SELECT t_id, tc_id, t_name, t_summary as intr, t_type, t_pic, t_reward FROM cloud_tasks WHERE tc_id = ? and t_status = 1 group by t_group order by t_total asc', array($tcid));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据任务id获取这条任务的信息
     * @param str $tid 任务id
     * @return array
     */
    public function getTaskByTid($tid)
    {
        $query = $this->db->query('SELECT t_id, tc_id, t_name, t_summary as intr, t_type, t_pic, t_condition, t_reward, t_total, t_group FROM cloud_tasks WHERE t_status = 1 AND t_id = ? limit 1',
            array($tid)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 如果有用户添加任务，则该任务的统计数+1
     * @param str $tid 任务id
     */
    public function taskCount($tid)
    {
        $this->db->execute('UPDATE `cloud_tasks` SET `t_counts` = `t_counts` +1 ' .
            'WHERE `t_id` = ?',
            array(
                $tid
            )
        );
        return $this->db->affectedRows();
    }
}