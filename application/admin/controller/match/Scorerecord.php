<?php

namespace app\admin\controller\match;

use app\admin\model\Admin;
use app\admin\model\match\Matchiteminvest;
use app\admin\model\match\Matchitemmember;
use app\admin\model\match\Matchitempatent;
use app\admin\model\match\Matchitemstock;
use app\admin\model\match\Matchitemteacher;
use app\admin\model\match\Matchrule;
use app\admin\model\match\Matchruleitem;
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

use think\Log;

/**
 * 评分记录查询
 *
 * @icon fa fa-circle-o
 */
class Scorerecord extends Backend
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
    }

    /**
     * 查看
     */
    public function index()
    {
        //开启关联查询
        $this->relationSearch = true;
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
                ->field("matchitem.item_name,matchitem.half_score,matchitem.final_score,matchitem.audit_status,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname,COUNT(s.score_id) AS expertCount,COUNT(s.score)AS scoreCount")
                ->where($where)
                ->whereOr("matchitem.item_status","eq",'复赛')
                ->whereOr("matchitem.item_status","eq",'总决赛')
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
                ->field("matchitem.item_name,matchitem.half_score,matchitem.final_score,matchitem.audit_status,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname,COUNT(s.score_id) AS expertCount,COUNT(s.score)AS scoreCount,GROUP_CONCAT(distinct ad.nickname) ad_name,GROUP_CONCAT(distinct ad.nickname,s.stage,'：',s.score) score_record, GROUP_CONCAT(CONCAT(ad.nickname,s.stage,d.rule_name,':',d.score,'分') order by s.stage SEPARATOR ',') score_detail")
                ->where($where)
                ->where("matchitem.item_status","in","复赛,总决赛")
                //->whereOr("matchitem.item_status","eq",'总决赛')
                ->join("match m","m.match_id = matchitem.match_id","left")
                ->join("match_group mg","mg.group_id = matchitem.group_id","left")
                ->join("match_track mt","mt.track_id = matchitem.track_id","left")
                ->join("user u","u.id = matchitem.user_id","left")
                ->join("match_school ms","ms.school_id = u.school","left")
                ->join("match_score s","s.item_id = matchitem.item_id","left")
                ->join("fa_match_score_detail d","s.score_id=d.score_id","left")
                ->join("admin ad","s.expert_id = ad.id","left")
                ->group("matchitem.item_name,matchitem.audit_status,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname")
                ->order($sort, 'desc')
                ->limit($offset, $limit)
                ->select();

           // echo $this->model->getLastSql();die;

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

}
