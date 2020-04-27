<?php

namespace app\admin\model\match;

use think\Model;


class Matchruleitem extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'match_rule_item';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    /**
     * 查询所有规则id，name
     * @return false|\PDOStatement|string|\think\Collection
     */
    function selRuleName(){
        $ruleModel = new Matchrule();
       $list = $ruleModel->alias("a")
            ->field("a.rule_id,a.rule_name")
            ->select();
        return $list;
    }







}
