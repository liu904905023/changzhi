<?php

namespace app\admin\model\match;

use think\Model;


class Matchgroup extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'match_group';
    
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
     * 查询赛道id，name
     * @param $track_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    function selTrackName($track_id){
        $trackModel = new Matchtrack();
        $list = $trackModel->alias("a")
            ->field("a.track_id,a.track_name")
            ->where("a.match_id","eq",$track_id)
            ->select();
        return $list;
    }

}
