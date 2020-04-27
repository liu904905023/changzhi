<?php

namespace app\admin\model\match;

use think\Model;
use think\response\Json;


class Matchschool extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'match_school';
    
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
     * 学校下拉
     */
    function selSchool(){
        $schoolModel = new Matchschool();
        $list = $schoolModel->field("school_id,school_name")->select();
        return $list;
    }
    







}
