<?php

namespace app\admin\model\match;

use think\Model;


class Matchtrack extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'match_track';
    
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
     * 查询比赛id，name
     * @param $match_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    function selMatchName($match_id){
        $matchModel = new Match();
        $list = $matchModel->alias("a")
            ->field("a.match_id,a.match_name")
            ->where("a.match_id","eq",$match_id)
            ->select();
        return $list;
    }
    







}
