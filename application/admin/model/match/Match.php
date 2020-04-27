<?php

namespace app\admin\model\match;

use think\Model;


class Match extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'match';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['正常' => __('正常'), '归档' => __('归档')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * 查询全部比赛id，name
     * @return false|\PDOStatement|string|\think\Collection
     */
    function selMatch(){
        $matchModel = new Match();
        $list = $matchModel->alias("a")
            ->field("a.match_id,a.match_name")
            ->where("a.status","eq","正常")
            ->select();
        return $list;
    }

    /**
     * 查询全部赛道id，name
     * @return false|\PDOStatement|string|\think\Collection
     */
    function selTrack(){
        $trackModel = new Matchtrack();
        $list = $trackModel->alias("a")
            ->field("a.track_id,a.track_name")
            ->select();
        return $list;
    }

    /**
     * 查询全部组别id,name
     * @return false|\PDOStatement|string|\think\Collection
     */
    function selGroup(){
        $groupModel  = new Matchgroup();
        $list = $groupModel->alias("a")
            ->join("match_track mt","mt.track_id = a.track_id","left")
            ->field("a.group_id,a.group_name,mt.track_name")
            ->select();
        return $list;
    }


}
