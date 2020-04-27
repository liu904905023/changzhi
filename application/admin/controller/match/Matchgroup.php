<?php

namespace app\admin\controller\match;

use app\admin\model\match\Match;
use app\admin\model\match\Matchruleitem;
use app\admin\model\match\Matchtrack;
use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 比赛组别管理
 *
 * @icon fa fa-circle-o
 */
class Matchgroup extends Backend
{
    
    /**
     * Matchgroup模型对象
     * @var \app\admin\model\match\Matchgroup
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\match\Matchgroup;

    }


    /**
     * 查看
     */
    public function index()
    {
        $trackId = $this->request->get("trackId");
        $matchId = $this->request->get("matchId");
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->alias("a")
                ->where($where)
                ->where("a.track_id","eq",$trackId)
                ->join("match_track mt","mt.track_id = a.track_id","left")
                ->join("match m","m.match_id = a.match_id","left")
                ->join("match_rule mr","mr.rule_id = a.rule_id","left")
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->alias("a")
                ->where($where)
                ->where("a.track_id","eq",$trackId)
                ->join("match_track mt","mt.track_id = a.track_id","left")
                ->join("match m","m.match_id = a.match_id","left")
                ->join("match_rule mr","mr.rule_id = a.rule_id","left")
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $this->assignconfig("trackId",$trackId);
        $this->assignconfig("matchId",$matchId);
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
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
        //下拉
        $matchTrackModel = new Matchtrack();
        $ruleModel = new Matchruleitem();
        $trackId = $this->request->get("trackId");
        $matchId = $this->request->get("matchId");
        $trackNameList = $this->model->selTrackName($matchId);
        $matchNameList = $matchTrackModel->selMatchName($matchId);
        $ruleNameList = $ruleModel->selRuleName();
        $this->view->assign("trackNameList",$trackNameList);
        $this->view->assign("matchNameList",$matchNameList);
        $this->view->assign("ruleNameList",$ruleNameList);
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
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
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        //下拉
        $matchTrackModel = new Matchtrack();
        $ruleModel = new Matchruleitem();
        $trackId = $this->request->get("trackId");
        $matchId = $this->request->get("matchId");
        $trackNameList = $this->model->selTrackName($trackId);
        $matchNameList = $matchTrackModel->selMatchName($matchId);
        $ruleNameList = $ruleModel->selRuleName();
        $this->view->assign("trackNameList",$trackNameList);
        $this->view->assign("matchNameList",$matchNameList);
        $this->view->assign("ruleNameList",$ruleNameList);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
}
