<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\match\Matchitem;
use app\admin\model\User;
use app\common\controller\Backend;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $adminModel = new Admin();
        $userModel = new User();
        $matchItemModel = new Matchitem();
        $id = $this->auth->id;

        $authName = $adminModel->alias("a")
            ->field("c.name")
            ->join("auth_group_access b","b.uid = a.id","left")
            ->join("auth_group c","c.id = b.group_id","left")
            ->where("a.id","eq",$id)
            ->select();

        //注册人数
        $userNum = 0;
        //作品总数
        $worksNum = 0;
        //半决赛数
        $halfNum = 0;
        //总决赛数
        $totalNum = 0;

        if($authName[0]['name'] == '平台管理员' || $authName[0]['name'] == 'Admin group' || $authName[0]['name'] == '专家评委'){
            $userNum = $userModel->count();
            $worksNum = $matchItemModel->count();
            $halfNum = $matchItemModel->where("item_status","eq","半决赛")->count();
            $totalNum = $matchItemModel->where("item_status","eq","总决赛")->count();
        }else if($authName[0]['name'] == '学校管理员'){
            //学校id
            $school_id = $adminModel->field("school_id")->where("id","eq",$id)->select();
            if(!empty($school_id[0]['school_id'])){
                $school_id = $school_id[0]['school_id'];
            }

            $userNum = $userModel->alias("a")
                ->where("a.school","eq",$school_id)
                ->count();
            $worksNum = $matchItemModel->alias("a")
                ->join("user u","u.id = a.user_id","left")
                ->where("u.school","eq",$school_id)
                ->count();
            $halfNum = $matchItemModel->alias("a")
                ->join("user u","u.id = a.user_id","left")
                ->where("item_status","eq","半决赛")
                ->where("u.school","eq",$school_id)
                ->count();
            $totalNum = $matchItemModel->alias("a")
                ->join("user u","u.id = a.user_id","left")
                ->where("item_status","eq","总决赛")
                ->where("u.school","eq",$school_id)
                ->count();
        }

        $this->view->assign("userNum",$userNum);
        $this->view->assign("worksNum",$worksNum);
        $this->view->assign("halfNum",$halfNum);
        $this->view->assign("totalNum",$totalNum);
        return $this->view->fetch();
    }

}
