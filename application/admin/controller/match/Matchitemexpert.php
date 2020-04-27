<?php

namespace app\admin\controller\match;

use app\admin\model\Admin;
use app\admin\model\match\Matchitem;
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
 * 半决赛项目评分
 *
 * @icon fa fa-circle-o
 */
class Matchitemexpert extends Backend
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
                ->field("matchitem.item_name,matchitem.audit_status,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname,s.is_score,s.score")
                ->where($where)
                ->where("matchitem.item_status","eq",'半决赛')
                ->where("s.expert_id","eq",$id)
                ->where("s.stage","eq","半决赛")
                ->join("match m","m.match_id = matchitem.match_id","left")
                ->join("match_group mg","mg.group_id = matchitem.group_id","left")
                ->join("match_track mt","mt.track_id = matchitem.track_id","left")
                ->join("user u","u.id = matchitem.user_id","left")
                ->join("match_school ms","ms.school_id = u.school","left")
                ->join("match_score s","s.item_id = matchitem.item_id","left")
                ->order($sort, 'desc')
                ->count();

            $list = $this->model
                ->alias("matchitem")
                ->field("matchitem.item_name,matchitem.audit_status,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname,s.is_score,s.score")
                ->where($where)
                ->where("matchitem.item_status","eq",'半决赛')
                ->where("s.expert_id","eq",$id)
                ->where("s.stage","eq","半决赛")
                ->join("match m","m.match_id = matchitem.match_id","left")
                ->join("match_group mg","mg.group_id = matchitem.group_id","left")
                ->join("match_track mt","mt.track_id = matchitem.track_id","left")
                ->join("user u","u.id = matchitem.user_id","left")
                ->join("match_school ms","ms.school_id = u.school","left")
                ->join("match_score s","s.item_id = matchitem.item_id","left")
                ->order('s.is_score','desc')
                ->order('s.score', 'desc')
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
     * 专家评分页面
     */
    function scoring_view(){
        $id = $this->request->get("id");
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

        //评审规则
        $ruleModel = new Matchrule();
        $item_group = $row[0]['group_name'];
        $ruleList = $ruleModel->alias("a")
            ->join("match_rule_item mr","mr.rule_id = a.rule_id","left")
            ->join("match_group g","a.rule_id = g.rule_id","left")
            ->join("match_item i","i.group_id = g.group_id","left")
            ->where("i.item_id","eq",$id)
            ->select();

        //总分
        $total_score = 0;
        //评语
        $comment = '';
        //评分回显
        $scoreModel = new Matchscore();
        $scoreList = $scoreModel
            ->alias("a")
            ->field("a.comment,a.score_id,a.score as totalScore,b.rule_name,b.score,b.rule_item_id")
            ->join("match_score_detail b","b.score_id =a.score_id","left")
            ->where("a.item_id","eq",$id)
            ->where("a.stage","eq",'半决赛')
            ->where("a.expert_id","eq",$this->auth->id)
            ->select();

        if(!empty($scoreList[0]['totalScore'])){
            $total_score = $scoreList[0]['totalScore'];
            $comment = $scoreList[0]['comment'];
        }

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
        $this->view->assign("ruleList", $ruleList);
        $this->view->assign("scoreList", $scoreList);
        $this->view->assign("total_score", $total_score);
        $this->view->assign("comment", $comment);
        $this->view->assign("item_id", $id);
        //传给js总分 若不为空 则为修改评分
        $this->assignconfig("totalSocre",$total_score);
        return $this->view->fetch('viewScore');
    }

    /**
     * 专家评分
     */
    function scoring(){
        $isType = $this->request->post("isType");
        $scoreList = $this->request->post("score/a");
        $rule_item_id = $this->request->post("rule_item_id/a");
        $totalScore = $this->request->post("totalScore/a");
        $rule_name = $this->request->post("rule_item_name/a");
        $ruleId = $this->request->post("ruleId");
        $itemId = $this->request->post("itemId");
        $comment = $this->request->post("comment");

        //根据专家id 和项目id  获取评分id
        $scoreModel = new Matchscore();
        $score_id = $scoreModel->field("score_id")
            ->where("item_id","eq",$itemId)
            ->where("expert_id","eq",$this->auth->id)
            ->where("stage","eq","半决赛")
            ->select();



        //判断当前是新增还是修改
        if($isType == 'update'){
            $scoreDeModel = new Matchscoredetail();
            $scoreDeModel->where("score_id","eq",$score_id[0]['score_id'])->delete();
        }

        $scoreSum = 0;
        for($i = 0;$i < sizeof($scoreList);$i++){
            //计算总分
            $scoreSum += $scoreList[$i];
            //插入评分详情表
            $arr['score_id'] = $score_id[0]['score_id'];
            $arr['rule_id'] = $ruleId;
            $arr['rule_item_id'] = $rule_item_id[$i];
            $arr['rule_name'] = $rule_name[$i];
            $arr['total_score'] = $totalScore[$i];
            $arr['score'] = $scoreList[$i];
            $scoreDetailModel = new Matchscoredetail();
            $scoreDetailModel->save($arr);
        }
        //更新评分表
        $row['score'] = $scoreSum;
        $row['is_score'] = 'Y';
        $row['scoretime'] = time();
        $row['comment'] = $comment;
        $result = $scoreModel->isUpdate(true)->where("score_id","eq",$score_id[0]['score_id'])->update($row);

        //更新fa_match_item 半决赛字段
        $is_scoreCount = $scoreModel->where('stage','半决赛')->where('is_score','N')->where('item_id',$itemId)->count('score_id');
        if ($is_scoreCount <= 0){
            $maxScore_id = Matchscore::where('item_id',$itemId)->where('stage','半决赛')->whereNotNull('score')->order('score desc')->value('score_id');
            $minScore_id = Matchscore::where('item_id',$itemId)->where('stage','半决赛')->whereNotNull('score')->order('score asc')->value('score_id');
            $avg = Matchscore::whereNotIn('score_id',"{$maxScore_id},{$minScore_id}")->where('item_id',$itemId)->where('stage','半决赛')->avg('score');
            $halfScore['half_score'] = $avg;
            $halfScore['half_status'] = 'Y';
            $matchitemModel = new Matchitem();
            $matchitemModel->where("item_id","eq",$itemId)->update($halfScore);
        }

        if($result){
            $this->success('评分成功');
        }else{
            $this->error('评分失败');
        }
    }

}
