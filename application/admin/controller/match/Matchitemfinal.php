<?php

namespace app\admin\controller\match;

use app\admin\model\Admin;
use app\admin\model\match\Matchiteminvest;
use app\admin\model\match\Matchitemmember;
use app\admin\model\match\Matchitempatent;
use app\admin\model\match\Matchitemstock;
use app\admin\model\match\Matchitemteacher;
use app\admin\model\match\Matchrule;
use app\admin\model\match\Matchscore;
use app\admin\model\match\Matchscoredetail;
use app\common\controller\Backend;
use app\common\model\User;

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * 总决赛参赛项目
 *
 * @icon fa fa-circle-o
 */
class Matchitemfinal extends Backend
{

    /**
     * Matchitem模型对象
     * @var \app\admin\model\match\Matchitem
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\match\Matchitem;
        $this->view->assign("isHightechList", $this->model->getIsHightechList());
        $this->view->assign("isFounderLeadList", $this->model->getIsFounderLeadList());
        $this->view->assign("isTogetherList", $this->model->getIsTogetherList());
        $this->view->assign("isTeamList", $this->model->getIsTeamList());
        $this->view->assign("isInvestList", $this->model->getIsInvestList());
        $this->view->assign("isPrivateList", $this->model->getIsPrivateList());
        $this->view->assign("isRedList", $this->model->getIsRedList());
        $this->view->assign("auditStatusList", $this->model->getAuditStatusList());
        $this->view->assign("itemStatusList", $this->model->getItemStatusList());
    }

    /**
     * 查看
     */
    public function index()
    {
        //开启关联查询
        $this->relationSearch = true;
        $adminModel = new Admin();
        $id = $this->auth->id;

        $authName = $adminModel->alias("a")
            ->field("c.name")
            ->join("auth_group_access b","b.uid = a.id","left")
            ->join("auth_group c","c.id = b.group_id","left")
            ->where("a.id","eq",$id)
            ->select();

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $sort = "matchitem.item_id";
            $total = $this->model
                ->alias("matchitem")
                ->field("matchitem.item_name,matchitem.audit_status,matchitem.final_score,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname,u.mobile,count(s.stage='总决赛' or 0) AS expertCount, COUNT(s.score and s.stage='总决赛' or 0) AS scoreCount")
                ->where($where)
                ->where("matchitem.item_status","eq",'总决赛')
                ->join("match m","m.match_id = matchitem.match_id","left")
                ->join("match_group mg","mg.group_id = matchitem.group_id","left")
                ->join("match_track mt","mt.track_id = matchitem.track_id","left")
                ->join("user u","u.id = matchitem.user_id","left")
                ->join("match_school ms","ms.school_id = u.school","left")
                ->join("match_score s","s.item_id = matchitem.item_id","left")
                ->group("matchitem.item_name,matchitem.audit_status,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname")
                ->order($sort, 'desc')
                ->count();

            $list = $this->model
                ->alias("matchitem")
                ->field("matchitem.item_name,matchitem.audit_status,matchitem.final_score,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname,u.mobile,count(s.stage='总决赛' or null) AS expertCount, COUNT(s.score and s.stage='总决赛' or null) AS scoreCount")
                //->field("matchitem.item_name,matchitem.audit_status,matchitem.final_score,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname,u.mobile,COUNT(s.score_id and s.stage='总决赛') AS expertCount,COUNT(s.score and s.stage='总决赛')AS scoreCount")
                ->where($where)
                ->where("matchitem.item_status","eq",'总决赛')
                ->join("match m","m.match_id = matchitem.match_id","left")
                ->join("match_group mg","mg.group_id = matchitem.group_id","left")
                ->join("match_track mt","mt.track_id = matchitem.track_id","left")
                ->join("user u","u.id = matchitem.user_id","left")
                ->join("match_school ms","ms.school_id = u.school","left")
                ->join("match_score s","s.item_id = matchitem.item_id","left")
                ->group("matchitem.item_name,matchitem.audit_status,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname")
                ->order($sort, 'desc')
                ->limit($offset, $limit)
                ->select();


            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $this->assignconfig("loginName",$authName[0]['name']);
        return $this->view->fetch();
    }

    /**
     * 审核查看
     * @return string
     */
    public function audit_view(){
        $id = $this->request->get("id");
        $type = $this->request->get("type");
        $investModel = new Matchiteminvest();
        $memberModel = new Matchitemmember();
        $patentModel = new Matchitempatent();
        $stockModel = new Matchitemstock();
        $teacherModel = new Matchitemteacher();
        $row = $this->model->alias("a")
            ->field("a.*,m.match_name,mg.group_name,mt.track_name,u.nickname,mi.*,mb.*,mp.*,ms.*,mit.*")
            ->join("match m","m.match_id = a.match_id","left")
            ->join("match_group mg","mg.group_id = a.group_id","left")
            ->join("match_track mt","mt.track_id = a.track_id","left")
            ->join("user u","u.id = a.user_id","left")
            ->join("match_item_invest mi","mi.item_id = a.item_id","left")
            ->join("match_item_member mb","mb.item_id = a.item_id","left")
            ->join("match_item_patent mp","mp.item_id = a.item_id","left")
            ->join("match_item_stock ms","ms.item_id = a.item_id","left")
            ->join("match_item_teacher mit","mit.item_id = a.item_id","left")
            ->where("a.item_id","eq",$id)
            ->select();
        //个人信息
        $userInfo = $this->model->alias("a")->field("u.*")->join("user u","u.id = a.user_id","left")->where("a.item_id","eq",$id)->select();
        //投资数据
        $investList = $investModel->alias("a")
            ->field("a.*")
            ->where("a.item_id","eq",$id)
            ->select();

        //成员数据
        $memberList = $memberModel->alias("a")
            ->field("a.*,s.school_name")
            ->join("match_school s","s.school_id = a.school_id","left")
            ->where("a.item_id","eq",$id)
            ->order("a.member_id","asc")
            ->select();

        //项目专利
        $patentList = $patentModel->where("item_id","eq",$id)->select();

        //公司股权
        $stockList = $stockModel->where("item_id","eq",$id)->select();

        //指导教师
        $teacherList = $teacherModel->alias("a")
            ->field("a.*,s.school_name")
            ->join("match_school s","s.school_id = a.school_id","left")
            ->where("item_id","eq",$id)
            ->select();

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign("row", $row[0]);
        $this->view->assign("userInfo", $userInfo[0]);
        $this->view->assign("investList", $investList);
        $this->view->assign("memberList", $memberList);
        $this->view->assign("patentList", $patentList);
        $this->view->assign("stockList", $stockList);
        $this->view->assign("teacherList", $teacherList);
        $this->view->assign("item_id", $id);
        if($type == 'audit'){
            return $this->view->fetch("edit");
        }else{
            return $this->view->fetch("viewDetail");
        }

    }

    /**
     * 分配专家
     */
    function distribute(){
        //传过来的项目id是字符串都好分隔，转成数组
        $projectIds = $this->request->post("projectIds/a");
        $project_ids = explode(',',$projectIds[0]);

        $expertIds = $this->request->post("expertIds/a");
        $stage = $this->request->post("stage");
        $resetScore = $this->request->post("resetScore");
        //若勾选 重置已评分 则先删除再新增
        if(!empty($resetScore)){
            for($i = 0;$i < sizeof($project_ids);$i++){
                $matchScoreModel = new Matchscore();
                $arr['item_id'] = $project_ids[$i];
                $matchScoreModel->where("item_id","eq",$arr['item_id'])->where("stage","eq",$stage)->delete();
                for($j = 0;$j < sizeof($expertIds);$j++){
                    $scoreModel = new Matchscore();
                    $arr['expert_id'] = $expertIds[$j];
                    $arr['stage'] = $stage;
                    $scoreModel->save($arr);
                }
            }
        }else{
            for($i = 0;$i < sizeof($project_ids);$i++){
                $arr['item_id'] = $project_ids[$i];
                for($j = 0;$j < sizeof($expertIds);$j++){
                    $scoreModel = new Matchscore();
                    $arr['expert_id'] = $expertIds[$j];
                    $arr['stage'] = $stage;
                    $scoreModel->save($arr);
                }
            }
        }

        $this->success();
    }

    /**
     * 查看已分配专家
     */
    function viewDistributeExpert(){
        $item_id = $this->request->get("id");
        $matchScoreModel = new Matchscore();
        $list = $matchScoreModel
            ->alias("a")
            ->field("a.score_id,a.stage,a.is_score,m.item_name,ad.technical_title,ad.email,ad.mobile,ad.nickname")
            ->where("a.item_id","eq",$item_id)
            ->join("match_item m","m.item_id = a.item_id","left")
            ->join("admin ad","ad.id = a.expert_id","left")
            ->select();
        $this->view->assign("distributeExpertList",$list);
        return $this->view->fetch("viewDistributeExpert");
    }

    /**
     * 专家评分记录
     */
    function scoreRecord(){
        $item_id = $this->request->get("id");

        $matchScoreModel = new Matchscore();

        //获取专家姓名，评分和评语
        $list = $matchScoreModel->alias("s")
            ->field("a.nickname,s.score,s.comment,GROUP_CONCAT(CONCAT(d.rule_name,':',d.score,'分') SEPARATOR ',') score_detail")
            ->join("fa_match_score_detail d","s.score_id=d.score_id","left")
            ->join("fa_admin a","s.expert_id = a.id","left")
            ->join("fa_auth_group_access g","g.uid=s.expert_id","left")
            ->where("s.item_id","eq",$item_id)
            ->where("s.stage","eq","总决赛")
            ->where("g.group_id","eq",5)
            ->where("s.is_score","eq","Y")
            ->group("a.nickname,s.score,s.comment")
            ->select();


        //查询项目平均分
        $final_score = $this->model->where('item_id',$item_id)->value('final_score');

        $this->view->assign('final_score',$final_score);

        $this->view->assign("list",$list);
        return $this->view->fetch("scoreDecord");
    }

}
