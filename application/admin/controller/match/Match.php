<?php

namespace app\admin\controller\match;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 比赛管理
 *
 * @icon fa fa-circle-o
 */
class Match extends Backend
{
    
    /**
     * Match模型对象
     * @var \app\admin\model\match\Match
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\match\Match;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            //验证若比赛表已存在一个状态为正常的比赛，则不允许再次添加状态正常的比赛
            $matchStatus = $params['status'];
            $matchModel = new \app\admin\model\match\Match();
            $matchList = $matchModel->selMatch();
            if(sizeof($matchList) != 0 && $matchStatus == '正常'){
                $this->error("已经有一个正在进行的比赛了！");
                return false;
            }

            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $params['admin_id'] = $this->auth->id;
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }
    

}
