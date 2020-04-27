<?php

namespace app\admin\controller\statistics;

use app\admin\model\Admin;
use app\admin\model\match\Matchitem;
use app\admin\model\match\Matchschool;
use app\admin\model\User;
use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 项目统计
 *
 * @icon fa fa-circle-o
 */
class Itemstatistics extends Backend
{
    

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 查看
     */
    public function index()
    {
        $this->relationSearch = true;
        $matchItemModel = new Matchitem();
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $matchItemModel
                ->alias("i")
                ->field("s.school_name,count(i.item_id) num,
                        sum(i.audit_status = '审核通过') pass,
                        sum(i.item_status = '半决赛') halfMatch,
                        sum(i.item_status = '总决赛') totalMatch,
                        sum(i.item_status = '答辩') defence")
                ->join("user u","u.id=i.user_id","left")
                ->join("match_school s","s.school_id = u.school","left")
                ->where($where)
                ->group("s.school_name")
                ->order("num", "desc")
                ->select();
            $list = collection($list)->toArray();

            //查询合计数据
            $totalList = $matchItemModel
                ->alias("i")
                ->field("'合计' as school_name,count(i.item_id) num,
                        sum(i.audit_status='审核通过') pass,
                        sum(i.item_status = '半决赛') halfMatch,
                        sum(i.item_status = '总决赛') totalMatch,
                        sum(i.item_status = '答辩') defence")
                ->where($where)
                ->select();

            $arr = ['school_name' => '合计','num' => 0,'pass' => 0,'halfMatch' => 0,'totalMatch' => 0,'defence' => 0];
            if(!empty($totalList[0])){
                $arr['school_name'] = $totalList[0]['school_name'];
                $arr['num'] = $totalList[0]['num'];
                $arr['pass'] = $totalList[0]['pass'];
                $arr['halfMatch'] = $totalList[0]['halfMatch'];
                $arr['totalMatch'] = $totalList[0]['totalMatch'];
                $arr['defence'] = $totalList[0]['defence'];
            }

            //合并数组
            array_push($list,$arr);
            $result = array( "rows" => $list);

            return json($result);
        }
        return $this->view->fetch("index");
    }

}
