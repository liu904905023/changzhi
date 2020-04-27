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
 * 对比图统计
 *
 * @icon fa fa-circle-o
 */
class Charts extends Backend
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
        if ($this->request->isAjax()) {
            $matchItemModel = new Matchitem();
            $matchSchoolModel = new Matchschool();
            $matchList = $matchItemModel
                ->alias("i")
                ->field("s.school_name,count(i.item_id) num")
                ->join("user u", "u.id=i.user_id", "left")
                ->join("match_school s", "s.school_id = u.school", "left")
                ->group("s.school_name")
                ->order("num", "desc")
                ->select();
            $schoolList = $matchSchoolModel
                ->alias("a")
                ->field("a.school_name,count(fa_user.id) num")
                ->join("user fa_user","fa_user.school = a.school_id","left")
                ->group("a.school_name")
                ->order("num", "desc")
                ->select();
            $matchList = collection($matchList)->toArray();
            $schoolList = collection($schoolList)->toArray();
            $result = array( "matchList" => $matchList,"studentList" => $schoolList);
            return $result;
        }
        return $this->view->fetch("index");
    }

}
