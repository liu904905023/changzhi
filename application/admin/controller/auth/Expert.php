<?php

namespace app\admin\controller\auth;

use app\admin\model\Admin;
use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use app\common\controller\Backend;
use fast\Random;
use fast\Tree;

/**
 * 分配专家
 *
 * @icon fa fa-users
 * @remark 一个管理员可以有多个角色组,左侧的菜单根据管理员所拥有的权限进行生成
 */
class Expert extends Backend
{

    /**
     * @var \app\admin\model\Admin
     */
    protected $model = null;
    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];
    protected $searchFields = 'admin.id,admin.nickname';

    public function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 分配专家查看
     */
    public function index()
    {
        $projectIds = $this->request->get("projectIds/a");
        $stage = $this->request->get("stage");
        //开启关联查询
        $this->relationSearch = true;
        $adminModel = new Admin();
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
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

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $this->assignconfig("projectIds",$projectIds);
        $this->assignconfig("stage",$stage);
        return $this->view->fetch();
    }

}
