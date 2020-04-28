<?php

namespace app\admin\controller\statistics;

use app\admin\model\match\Matchgroup;
use app\admin\model\match\Matchitem;
use app\common\controller\Backend;

/**
 * 比赛组别管理
 *
 * @icon fa fa-circle-o
 */
class Group extends Backend
{
    
    /**
     * Group模型对象
     * @var \app\admin\model\statistics\Group
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function index()
    {
        $this->relationSearch = true;
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            $matchItemModel =  new Matchitem();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total =  $matchItemModel
                ->alias("i")
                ->field("
                s.school_name AS 学校,
	sum( CASE WHEN g.group_name='创新组' AND i.item_status='初赛' THEN 1 ELSE 0 END) AS 初赛创新组,
	sum( CASE WHEN g.group_name='创意组' AND i.item_status='初赛' THEN 1 ELSE 0 END) AS 初赛创意组,
	sum( CASE WHEN g.group_name='初创组' AND i.item_status='初赛' THEN 1 ELSE 0 END) AS 初赛初创组,
	sum( CASE WHEN g.group_name='成长组' AND i.item_status='初赛' THEN 1 ELSE 0 END) AS 初赛成长组,
	sum(CASE WHEN i.item_status='初赛' THEN 1 ELSE 0 END) as 初赛总数 ,
	sum( CASE WHEN g.group_name='创新组' AND i.item_status='复赛' THEN 1 ELSE 0 END) AS 复赛创新组,
	sum( CASE WHEN g.group_name='创意组' AND i.item_status='复赛' THEN 1 ELSE 0 END) AS 复赛创意组,
	sum( CASE WHEN g.group_name='初创组' AND i.item_status='复赛' THEN 1 ELSE 0 END) AS 复赛初创组,
	sum( CASE WHEN g.group_name='成长组' AND i.item_status='复赛' THEN 1 ELSE 0 END) AS 复赛成长组,
	sum(CASE WHEN i.item_status='复赛' THEN 1 ELSE 0 END) as 复赛总数 ,
	sum( CASE WHEN g.group_name='创新组' AND i.item_status='总决赛' THEN 1 ELSE 0 END) AS 总决赛创新组,
	sum( CASE WHEN g.group_name='创意组' AND i.item_status='总决赛' THEN 1 ELSE 0 END) AS 总决赛创意组,
	sum( CASE WHEN g.group_name='初创组' AND i.item_status='总决赛' THEN 1 ELSE 0 END) AS 总决赛初创组,
	sum( CASE WHEN g.group_name='成长组' AND i.item_status='总决赛' THEN 1 ELSE 0 END) AS 总决赛成长组,
	sum(CASE WHEN i.item_status='总决赛' THEN 1 ELSE 0 END) as 总决赛总数 
                        ")

                ->join("user u","u.id=i.user_id","left")
                ->join("match_school s","s.school_id = u.school","left")
                ->join('match_group g','i.group_id=g.group_id','LEFT')
                ->where($where)
                ->where('i.item_status','复赛')
                ->group("s.school_name")
                ->count();

            $list = $matchItemModel
                ->alias("i")
                ->field("
                s.school_name,
	sum( CASE WHEN g.group_name='创新组' AND i.item_status='初赛' THEN 1 ELSE 0 END) AS cs_cxz,
	sum( CASE WHEN g.group_name='创意组' AND i.item_status='初赛' THEN 1 ELSE 0 END) AS cs_cyz,
	sum( CASE WHEN g.group_name like '%初创组%' AND i.item_status='初赛' THEN 1 ELSE 0 END) AS cs_ccz,
	sum( CASE WHEN g.group_name like  '%成长组%' AND i.item_status='初赛' THEN 1 ELSE 0 END) AS cs_czz,
	sum(CASE WHEN i.item_status='初赛' THEN 1 ELSE 0 END) as cs_num ,
	sum( CASE WHEN g.group_name='创新组' AND i.item_status='复赛' THEN 1 ELSE 0 END) AS bjs_cxz,
	sum( CASE WHEN g.group_name='创意组' AND i.item_status='复赛' THEN 1 ELSE 0 END) AS bjs_cyz,
	sum( CASE WHEN g.group_name like '%初创组%' AND i.item_status='复赛' THEN 1 ELSE 0 END) AS bjs_ccz,
	sum( CASE WHEN g.group_name like  '%成长组%' AND i.item_status='复赛' THEN 1 ELSE 0 END) AS bjs_czz,
	sum(CASE WHEN i.item_status='复赛' THEN 1 ELSE 0 END) as bjs_num ,
	sum( CASE WHEN g.group_name='创新组' AND i.item_status='总决赛' THEN 1 ELSE 0 END) AS zjs_cxz,
	sum( CASE WHEN g.group_name='创意组' AND i.item_status='总决赛' THEN 1 ELSE 0 END) AS zjs_cyz,
	sum( CASE WHEN g.group_name like '%初创组%' AND i.item_status='总决赛' THEN 1 ELSE 0 END) AS zjs_ccz,
	sum( CASE WHEN g.group_name like  '%成长组%' AND i.item_status='总决赛' THEN 1 ELSE 0 END) AS zjs_czz,
	sum(CASE WHEN i.item_status='总决赛' THEN 1 ELSE 0 END) as zjs_num 
                        ")
                ->join("user u","u.id=i.user_id","left")
                ->join("match_school s","s.school_id = u.school","left")
                ->join('match_group g','i.group_id=g.group_id','left')
                ->where($where)
                ->group("s.school_name")
                ->limit($offset , $limit)
                ->select();


            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

}
