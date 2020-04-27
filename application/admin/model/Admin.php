<?php

namespace app\admin\model;

use think\Model;
use think\Session;

class Admin extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 重置用户密码
     * @author baiyouwen
     */
    public function resetPassword($uid, $NewPassword)
    {
        $passwd = $this->encryptPassword($NewPassword);
        $ret = $this->where(['id' => $uid])->update(['password' => $passwd]);
        return $ret;
    }

    // 密码加密
    protected function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($password . $salt);
    }

    /**
     * 查询所有学校管理员
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function selSchoolManager(){
        $list = $this->alias("a")
                    ->field("a.id,a.nickname")
                    ->join("auth_group_access c","c.uid = a.id","left")
                    ->join("auth_group b","b.id = c.group_id","left")
                    ->where("b.id","eq",3)
                    ->select();
        return $list;
    }

}
