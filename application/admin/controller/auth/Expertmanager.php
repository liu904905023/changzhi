<?php

namespace app\admin\controller\auth;

use app\admin\model\Admin;
use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use app\common\controller\Backend;
use fast\Random;
use fast\Tree;

/**
 * 专家管理
 *
 * @icon fa fa-users
 *
 */
class Expertmanager extends Backend
{

    /**
     * @var \app\admin\model\Admin
     */
    protected $model = null;
    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];

    public function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 查看
     */
    public function index()
    {
        //开启关联查询
        $this->relationSearch = true;
        $adminModel = new Admin();
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $adminModel->alias("admin")
                    ->join("auth_group_access b","b.uid = admin.id","left")
                    ->join("auth_group c","c.id = b.group_id","left")
                    ->where("c.name","eq","专家评委")
                    ->where($where)
                    ->order("admin.id", $order)
                    ->count();

            $list = $adminModel->alias("admin")
                    ->field("admin.*,c.name")
                    ->join("auth_group_access b","b.uid = admin.id","left")
                    ->join("auth_group c","c.id = b.group_id","left")
                    ->where("c.name","eq","专家评委")
                    ->where($where)
                    ->order("admin.id", $order)
                    ->limit($offset, $limit)
                    ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        $adminModel = new Admin();
        $adminAccessModel = new AuthGroupAccess();
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                $params['salt'] = Random::alnum();
                $params['password'] = md5(md5($params['password']) . $params['salt']);
                $params['avatar'] = '/assets/img/avatar.png'; //设置新管理员默认头像。
                $result = $adminModel->validate('Admin.add')->save($params);
                //增加专家的角色
                $arr['uid'] = $adminModel->getLastInsID();
                $arr['group_id'] = 5;
                $adminAccessModel->save($arr);
                if ($result === false)
                {
                    $this->error($adminModel->getError());
                }
                $this->success();
            }
            $this->error();
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $adminModel = new Admin();
        $row = $adminModel->get(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                if ($params['password'])
                {
                    $params['salt'] = Random::alnum();
                    $params['password'] = md5(md5($params['password']) . $params['salt']);
                }
                else
                {
                    unset($params['password'], $params['salt']);
                }
                //这里需要针对username和email做唯一验证
                $adminValidate = \think\Loader::validate('Admin');
                $adminValidate->rule([
                    'username' => 'require|max:50|unique:admin,username,' . $row->id,
                    'email'    => 'require|email|unique:admin,email,' . $row->id
                ]);
                $result = $row->validate('Admin.edit')->save($params);
                if ($result === false)
                {
                    $this->error($row->getError());
                }
                $this->success();
            }
            $this->error();
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        $adminModel = new Admin();
        $adminAccessModel = new AuthGroupAccess();
        if ($ids)
        {
            $del_ids = explode(',',$ids);
            $adminModel->where("id","in",$del_ids)->delete();
            $adminAccessModel->where('uid', 'in', $del_ids)->delete();
            $this->success();
        }
        $this->error();
    }

    /**
     * 批量更新
     * @internal
     */
    public function multi($ids = "")
    {
        // 管理员禁止批量操作
        $this->error();
    }

    /**
     * 下拉搜索
     */
    public function selectpage()
    {
        $this->dataLimit = 'auth';
        $this->dataLimitField = 'id';
        return parent::selectpage();
    }

}
