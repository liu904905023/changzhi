<?php

namespace app\admin\controller\student;

use app\admin\model\Admin;
use app\admin\model\match\Matchschool;
use app\common\controller\Backend;
use fast\Random;
use think\Validate;
use app\common\library\Auth;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class Student extends Backend
{
    
    /**
     * Student模型对象
     * @var \app\admin\model\student\Student
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\student\Student;
        $matchSchool = new Matchschool();
        $this->view->assign("schoolList",$matchSchool->selSchool());

    }

    /**
     * 查看（学校管理员查看本校注册学生,平台管理员查看所有审核通过学生）
     */
    public function index()
    {
        //根据学校管理登陆id 查询 所在学校id
        $adminModel = new Admin();
        $school_manager_id = $this->auth->id;
        $school_id= $adminModel->field("school_id")->where("id","eq",$school_manager_id)->select();
        if(!empty($school_id) && !empty($school_id[0])){
            $school_id = $school_id[0]['school_id'];
        }

        //查询当前登录管理员权限名称
        $authName = $adminModel->alias("a")
            ->field("c.name")
            ->join("auth_group_access b","b.uid = a.id","left")
            ->join("auth_group c","c.id = b.group_id","left")
            ->where("a.id","eq",$school_manager_id)
            ->select();

        if($authName[0]['name'] == 'Admin group'){
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->alias("a")
                ->field("a.*,ms.school_id,ms.school_name")
                ->where($where)
                ->join("match_school ms","ms.school_id = a.school","left")
                ->order("a.createtime", "desc")
                ->count();

            $list = $this->model
                ->alias("a")
                ->field("a.*,ms.school_id,ms.school_name")
                ->where($where)
                ->join("match_school ms","ms.school_id = a.school","left")
                ->order("a.createtime", "desc")
                ->limit($offset, $limit)
                ->select();

        }else if($authName[0]['name'] == '平台管理员'){

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->alias("a")
                ->field("a.*,ms.school_id,ms.school_name")
                ->where($where)
                ->join("match_school ms","ms.school_id = a.school","left")
                ->order("a.createtime", "desc")
                ->count();

            $list = $this->model
                ->alias("a")
                ->field("a.*,ms.school_id,ms.school_name")
                ->where($where)
                ->join("match_school ms","ms.school_id = a.school","left")
                ->order("a.createtime", "desc")
                ->limit($offset, $limit)
                ->select();

        }else if($authName[0]['name'] == '学校管理员'){

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->alias("a")
                ->where($where)
                ->where("a.school","eq",$school_id)
                ->join("match_school ms","ms.school_id = a.school","left")
                ->order("a.createtime", "desc")
                ->count();

            $list = $this->model
                ->alias("a")
                ->where($where)
                ->where("a.school","eq",$school_id)
                ->join("match_school ms","ms.school_id = a.school","left")
                ->order("a.createtime", "desc")
                ->limit($offset, $limit)
                ->select();
        }

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $this->assignconfig("authName",$authName[0]['name']);
        return $this->view->fetch();
    }

    /**
     * 审核页面
     */
    public function audit_view($ids = null)
    {
        $id = $this->request->get("id");
        $row = $this->model->alias("a")
                           ->field("a.*,mc.school_name")
                           ->join("match_school mc","mc.school_id = a.school","left")
                           ->where("a.id","eq",$id)
                           ->select();
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign("row", $row[0]);
        $this->view->assign("id", $id);
        return $this->view->fetch("audit_view");
    }

    /**
     * 审核
     */
    public function audit(){
        $id = $this->request->post("audit_id");
        $audit_status = $this->request->post("audit_status");
        if($audit_status == 'success'){
            $arr['audit_status'] = '1';
            $arr['status'] = 'normal';
            $arr['id'] = $id;
            $result = $this->model->isUpdate(true)->save($arr);
            if($result){
                $this->success("审核成功！");
            }
        }else if($audit_status == 'failed'){
            $arr['audit_status'] = '2';
            $arr['id'] = $id;
            $result = $this->model->isUpdate(true)->save($arr);
            if($result){
                $this->success("审核成功！");
            }
        }
    }

    /**
     * 重置密码
     */
    public function resetpwd(){
        if ($this->request->isPost()) {
            $id = $this->request->post("id");
            $defaultPwd = '123456';
            $arr['salt'] = Random::alnum();
            $newpassword = md5(md5($defaultPwd) . $arr['salt']);
            $arr['password'] = $newpassword;
            $result = $this->model->update($arr,['id'=>$id]);
            if($result){
                $this->success("重置成功！",'','success');
            }
        }
        $this->error("重置失败！",'','error');
    }

}
