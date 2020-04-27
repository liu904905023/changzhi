<?php

namespace app\admin\model\match;

use think\Model;


class Matchscore extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'match_score';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'stage_text'
    ];
    

    
    public function getStageList()
    {
        return ['初赛' => __('初赛'), '复赛' => __('复赛'), '决赛' => __('决赛'), '总决赛' => __('总决赛')];
    }


    public function getStageTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['stage']) ? $data['stage'] : '');
        $list = $this->getStageList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
