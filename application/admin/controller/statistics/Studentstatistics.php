<?php

namespace app\admin\controller\statistics;

use app\admin\model\Admin;
use app\admin\model\match\Matchschool;
use app\admin\model\User;
use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 学生统计
 *
 * @icon fa fa-circle-o
 */
class Studentstatistics extends Backend
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
        $matchSchoolModel = new Matchschool();
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $matchSchoolModel
                ->alias("a")
                ->field("a.school_name as schoolName,count(fa_user.id) num")
                ->join("user fa_user","fa_user.school = a.school_id","left")
                ->where($where)
                ->group("a.school_name")
                ->order("num", "desc")
                ->select();
            $list = collection($list)->toArray();

            //查询合计数据
            $userModel = new User();
            $totalList = $userModel
                ->alias("fa_user")
                ->field("'合计' as schoolName,count(fa_user.id) num")
                ->where($where)
                ->select();

            $arr = ['schoolName' => '合计','num' => 0];
            if(!empty($totalList[0])){
                $arr['schoolName'] = $totalList[0]['schoolName'];
                $arr['num'] = $totalList[0]['num'];
            }

            //合并数组
            array_push($list,$arr);
            $result = array( "rows" => $list);

            return json($result);
        }
        return $this->view->fetch("index");
    }

}
