<?php

namespace app\admin\model\match;

use think\Model;


class Matchitemmember extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'match_item_member';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'is_accept_text'
    ];
    

    
    public function getIsAcceptList()
    {
        return ['Y' => __('Y'), 'N' => __('N')];
    }


    public function getIsAcceptTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_accept']) ? $data['is_accept'] : '');
        $list = $this->getIsAcceptList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
