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

use Exception;
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
 * 半决赛参赛项目
 *
 * @icon fa fa-circle-o
 */
class Matchitem extends Backend
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
                ->field("matchitem.item_name,matchitem.half_score,matchitem.audit_status,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname,u.mobile,COUNT(s.score_id) AS expertCount,COUNT(s.score)AS scoreCount")
                ->where($where)
                ->where("matchitem.item_status","eq",'半决赛')
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
                ->field("matchitem.item_name,matchitem.half_score,matchitem.audit_status,matchitem.item_status,ms.school_name,matchitem.item_id,mg.group_name,mt.track_name,u.nickname,u.mobile,COUNT(s.score_id) AS expertCount,COUNT(s.score)AS scoreCount")
                ->where($where)
                ->where("matchitem.item_status","eq",'半决赛')
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
        $row[0]['category'] = str_replace('1','',$row[0]['category']);
        $row[0]['category'] = str_replace('2','',$row[0]['category']);
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
     * 审核
     */
    function audit(){
        $item_id = $this->request->post("item_id");
        $status = $this->request->post("status");
        $itemModel = new \app\admin\model\match\Matchitem();
        $arr['item_id'] = $item_id;
        if($status == 'success'){
            $arr['audit_status'] = '审核通过';
        }else{
            $arr['audit_status'] = '审核未通过';
        }
        $result = $itemModel->isUpdate(true)->save($arr);
        if($result){
            $this->success("审核成功！");
        }
    }

    /**
     * 晋级总决赛
     */
    function promotion(){
        $ids = $this->request->post("ids/a");
        for($i = 0;$i < sizeof($ids);$i++){
            $itemModel = new \app\admin\model\match\Matchitem();
            $arr['item_id'] = $ids[$i];
            $arr['item_status'] = '总决赛';
            $itemModel->isUpdate(true)->save($arr);
        }
        $this->success();
    }

    /**
     * 退回初赛
     */
    function reFirst(){
        $arr['item_id'] = $this->request->post("id");
        $arr['item_status'] = '初赛';
        $itemModel = new \app\admin\model\match\Matchitem();
        $itemModel->isUpdate(true)->save($arr);
        $this->success("退回成功！");
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
            ->where("s.stage","eq","半决赛")
            ->where("g.group_id","eq",5)
            ->where("s.is_score","eq","Y")
            ->group("a.nickname,s.score,s.comment")
            ->order('s.score desc')
            ->select();

        //查询项目平均分
        $half_score = $this->model->where('item_id',$item_id)->value('half_score');

        $this->view->assign("list",$list);
        $this->view->assign('half_score',$half_score);
        return $this->view->fetch("scoreDecord");
    }


    /**
     * 生成excel
     * @param $id
     * @param $fileRandomNum
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    function createExcel($id,$fileRandomNum){
        //查询选中id的项目内容
        $itemModel = new \app\admin\model\match\Matchitem();
        $total = $itemModel
            ->alias("a")
            ->field("m.match_name,mt.track_name,mg.group_name,a.*")
            ->join("match_group mg","mg.group_id = a.group_id","left")
            ->join("match_track mt","mt.track_id = a.track_id","left")
            ->join("match m","m.match_id = a.match_id","left")
            ->where("a.item_id","eq",$id)
            ->count();

        $list = $itemModel
            ->alias("a")
            ->field("m.match_name,mt.track_name,mg.group_name,a.*")
            ->join("match_group mg","mg.group_id = a.group_id","left")
            ->join("match_track mt","mt.track_id = a.track_id","left")
            ->join("match m","m.match_id = a.match_id","left")
            ->where("a.item_id","eq",$id)
            ->select();

        $list = collection($list)->toArray();
        $result = array("total" => $total, "rows" => $list);

        //股权结构查询
        $stockModel = new Matchitemstock();
        $stockList = $stockModel->where("item_id","eq",$id)->select();
        //投资情况查询
        $investModel = new Matchiteminvest();
        $investList = $investModel->where("item_id","eq",$id)->select();
        //团队成员查询
        $memberModel = new Matchitemmember();
        $memberList = $memberModel->alias("a")
            ->where("item_id","eq",$id)
            ->join("match_school b","b.school_id = a.school_id","left")
            ->order("a.member_id","asc")
            ->select();
        //指导教师查询
        $teacherModel = new Matchitemteacher();
        $teacherList = $teacherModel->alias("a")
            ->where("item_id","eq",$id)
            ->join("match_school b","b.school_id = a.school_id","left")
            ->select();
        //项目专利查询
        $patentModel = new Matchitempatent();
        $patentList = $patentModel->where("item_id","eq",$id)->select();

        //runtime下创建一个临时文件夹
        $dir = iconv("UTF-8", "GBK", RUNTIME_PATH .'/package_matchitem'.$fileRandomNum);

        //项目名称若有 / 则替换成 - 项目名称前加上id 并截取过长的名称
        $item_id = $list[0]['item_id'];
        $item_name = $item_id . '-' . '项目信息' . '-' . $list[0]['item_name'];
        if(strstr($item_name,'/')){
            $item_name = str_replace('/','-',$item_name);
        }
        if(mb_strlen($item_name,'UTF8') > 25){
            $item_name = mb_substr($item_name, 0, 25, 'utf-8').'。。。';
        }

        //项目名乱码 或有其他字符无法识别
        try{
            $son_dir = iconv("UTF-8", "GBK", RUNTIME_PATH .'/package_matchitem'.$fileRandomNum.'/'.$item_name);
        }catch (Exception $e){
            $item_name = $list[0]['item_id'];
            $son_dir = iconv("UTF-8", "GBK", RUNTIME_PATH .'/package_matchitem'.$fileRandomNum.'/'.$item_name);
        }
        if (!file_exists($dir)){
            //赋予权限和允许嵌套创建文件夹
            mkdir ($dir,0777,true);
        }
        if (!file_exists($son_dir)){
            mkdir ($son_dir,0777,true);
        }

        set_time_limit(0);
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()
            ->setCreator("Szz")
            ->setLastModifiedBy("Szz")
            ->setTitle("参赛项目")
            ->setSubject("Subject");
        $spreadsheet->getDefaultStyle()->getFont()->setName('Microsoft Yahei');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);

        $worksheet = $spreadsheet->setActiveSheetIndex(0);

        $first = array_keys($list[0]);
        foreach ($first as $index => $item) {
            $worksheet->setCellValueByColumnAndRow($index, 1, __($item));
        }

        $excel_url = iconv("UTF-8", "GBK", ROOT_PATH  . 'template/iteminfo.xls');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excel_url);  //读取模板
        $worksheet = $spreadsheet->getActiveSheet();     //指向激活的工作表
        $worksheet->setTitle('参赛项目数据');

        for($i=0;$i<$total;++$i){
            //向模板表中写入数据
            $worksheet->setCellValue('A1', '参赛项目数据');   //送入A1的内容
            $worksheet->getCell('B2')->setValue($result['rows'][$i]['match_name']);
            $worksheet->getCell('G2')->setValue($result['rows'][$i]['track_name']);
            $worksheet->getCell('K2')->setValue($result['rows'][$i]['group_name']);
            $worksheet->getCell('D3')->setValue($list[0]['item_name']);
            //判断项目logo路径下的图片是否存在
            $logo_prefix = str_replace("/../application/","",APP_PATH);
            if($result['rows'][$i]['logo_avatar'] != '' && file_exists($logo_prefix .$result['rows'][$i]['logo_avatar'])){
                //插入图片
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo');
                $drawing->setPath($logo_prefix.$result['rows'][$i]['logo_avatar']);
                $drawing->setHeight(80);
                //左上角相对图片的偏移量
                $drawing->setOffsetX(10);
                //设置图片在一个单元格
                $drawing->setCoordinates("B3");
                $drawing->setWorksheet($spreadsheet->getActiveSheet());
            }

            $worksheet->getCell('B4')->setValue($result['rows'][$i]['city']);
            $worksheet->getCell('H4')->setValue($result['rows'][$i]['category']);
            $worksheet->getCell('B5')->setValue($result['rows'][$i]['item_desc']);
            $worksheet->getCell('B6')->setValue($result['rows'][$i]['founder_desc']);
            if($result['rows'][$i]['is_hightech'] == 'N'){
                $worksheet->getCell('B7')->setValue('否');
            }else{
                $worksheet->getCell('B7')->setValue('是');
            }
            if($result['rows'][$i]['is_founder_lead'] == 'N'){
                $worksheet->getCell('F7')->setValue('否');
            }else{
                $worksheet->getCell('F7')->setValue('是');
            }
            if($result['rows'][$i]['is_together'] == 'N'){
                $worksheet->getCell('H7')->setValue('否');
            }else{
                $worksheet->getCell('H7')->setValue('是');
            }
            if($result['rows'][$i]['is_team'] == 'N'){
                $worksheet->getCell('J7')->setValue('否');
            }else{
                $worksheet->getCell('J7')->setValue('是');
            }
            //项目进展选择（三种情况判断）
            if($result['rows'][$i]['progress'] == '0'){
                $worksheet->getCell('L7')->setValue('创意计划阶段');
                //团队成员
                $memberNum = sizeof($memberList);
                if($memberNum > 0){
                    if($memberNum > 1){
                        $worksheet->insertNewRowBefore(21,$memberNum-1);//因为模版已经有一空行，所以减一(参数：行号，增加几行)
                    }
                    for($h = 0;$h < $memberNum;$h++){
                        //计算当前行的行数
                        $member_line_number = 20+$h;//模版本来所在行号+当前添加行数+添加的行数
                        //填充数据
                        $worksheet->getCell('A'.$member_line_number)->setValue($memberList[$h]['member_name']);
                        $worksheet->getCell('B'.$member_line_number)->setValue($memberList[$h]['team_role']);
                        $worksheet->getCell('C'.$member_line_number)->setValue($memberList[$h]['college']);
                        $worksheet->getCell('D'.$member_line_number)->setValue($memberList[$h]['school_name']);
                        $worksheet->getCell('E'.$member_line_number)->setValue($memberList[$h]['major']);
                        $worksheet->getCell('F'.$member_line_number)->setValue($memberList[$h]['grade']);
                        $worksheet->getCell('G'.$member_line_number)->setValue($memberList[$h]['education']);
                        $worksheet->getCell('H'.$member_line_number)->setValue($memberList[$h]['sno']);
                        $worksheet->getCell('I'.$member_line_number)->setValue($memberList[$h]['phone']);
                    }
                }else{
                    $memberNum = 1;
                }
                //指导教师
                $teacherNum = sizeof($teacherList);
                if($teacherNum > 0){
                    if($teacherNum > 1){
                        $worksheet->insertNewRowBefore(24+$memberNum-1,$teacherNum-1);//因为模版已经有一空行，所以减一(参数：行号，增加几行)
                    }
                    for($g = 0;$g < $teacherNum;$g++){
                        //计算当前行的行数
                        $teacher_line_number = 23+$g+$memberNum-1;//模版本来所在行号+当前添加行数+添加的行数
                        //填充数据
                        $worksheet->getCell('A'.$teacher_line_number)->setValue($teacherList[$g]['teacher_name']);
                        $worksheet->getCell('B'.$teacher_line_number)->setValue('指导教师');
                        $worksheet->getCell('C'.$teacher_line_number)->setValue($teacherList[$g]['school_name']);
                        $worksheet->getCell('D'.$teacher_line_number)->setValue($teacherList[$g]['department']);
                        $worksheet->getCell('E'.$teacher_line_number)->setValue($teacherList[$g]['positional_title']);
                        $worksheet->getCell('F'.$teacher_line_number)->setValue($teacherList[$g]['teacher_email']);
                        $worksheet->getCell('G'.$teacher_line_number)->setValue($teacherList[$g]['teacher_mobile']);
                    }
                }else{
                    $teacherNum = 1;
                }
                //已获专利
                $patentNum = sizeof($patentList);
                if($patentNum > 0){
                    for($f = 0;$f < $patentNum;$f++){
                        //计算当前行的行数
                        $patent_line_number = 26+$f+$memberNum-1+$teacherNum-1;//模版本来所在行号+当前添加行数+添加的行数
                        //填充数据
                        $worksheet->getCell('A'.$patent_line_number)->setValue($patentList[$f]['patent_name']);
                        $worksheet->getCell('B'.$patent_line_number)->setValue($patentList[$f]['patent_type']);
                        $worksheet->getCell('C'.$patent_line_number)->setValue($patentList[$f]['patent_no']);
                        $worksheet->getCell('D'.$patent_line_number)->setValue($patentList[$f]['gain_date']);
                    }
                }
            }else if($result['rows'][$i]['progress'] == '1'){
                $worksheet->getCell('L7')->setValue('已注册公司运营');
                $worksheet->getCell('B9')->setValue($result['rows'][$i]['company_name']);
                $worksheet->getCell('I9')->setValue($result['rows'][$i]['legal_person_type']);
                $worksheet->getCell('B10')->setValue($result['rows'][$i]['legal_person_name']);
                $worksheet->getCell('I10')->setValue($result['rows'][$i]['legal_person_post']);
                $worksheet->getCell('B11')->setValue($result['rows'][$i]['register_capital']);
                $worksheet->getCell('I11')->setValue($result['rows'][$i]['register_date']);
                $worksheet->getCell('B12')->setValue($result['rows'][$i]['register_address']);
                $worksheet->getCell('I12')->setValue($result['rows'][$i]['credit_code']);
                //股权结构多条数据
                $stockNum = sizeof($stockList);
                if($stockNum > 0){
                    //如果数据条数多于1一条则追加行
                    if($stockNum > 1){
                        $worksheet->insertNewRowBefore(15,$stockNum-1);//因为模版已经有一空行，所以减一(参数：行号，增加几行)
                    }
                    for($j = 0;$j < $stockNum;$j++){
                        //合并单元格
                        $spreadsheet->getActiveSheet()->mergeCells('B'.(14+ $j).':E'.(14+ $j));
                        $spreadsheet->getActiveSheet()->mergeCells('F'.(14+ $j).':I'.(14+ $j));
                        $spreadsheet->getActiveSheet()->mergeCells('J'.(14+ $j).':M'.(14+ $j));
                        //填充数据
                        $worksheet->getCell('B'.(14+ $j))->setValue($stockList[$j]['stockholder_type']);
                        $worksheet->getCell('F'.(14+ $j))->setValue($stockList[$j]['stockholder']);
                        $worksheet->getCell('J'.(14+ $j))->setValue($stockList[$j]['hold_ratio']);
                    }
                }else{
                    //如果股权结构没有数据 则有一行空行
                    $stockNum = 1;
                }
                //获得投资情况
                $investNum = sizeof($investList);
                if($result['rows'][$i]['is_invest'] == 'N'){
                    $worksheet->getCell('B'.(14+$stockNum))->setValue('否');
                    $investNum = 1;
                }else{
                    $worksheet->getCell('B'.(14+$stockNum))->setValue('是');
                    if($investNum > 0){
                        if($investNum > 1){
                            $worksheet->insertNewRowBefore(18+$stockNum-1,$investNum-1);//因为模版已经有一空行，所以减一(参数：行号，增加几行)
                        }
                        for($k = 0;$k < $investNum;$k++){
                            //计算当前行的行数
                            $stock_line_number = 17+$k+$stockNum-1;//模版本来所在行号+当前添加行数+股权添加的行数
                            //合并单元格
                            $spreadsheet->getActiveSheet()->mergeCells('B'.$stock_line_number.':D'.$stock_line_number);
                            $spreadsheet->getActiveSheet()->mergeCells('E'.$stock_line_number.':G'.$stock_line_number);
                            $spreadsheet->getActiveSheet()->mergeCells('H'.$stock_line_number.':J'.$stock_line_number);
                            $spreadsheet->getActiveSheet()->mergeCells('K'.$stock_line_number.':M'.$stock_line_number);
                            //填充数据
                            $worksheet->getCell('B'.$stock_line_number)->setValue($investList[$k]['invest_name']);
                            $worksheet->getCell('E'.$stock_line_number)->setValue($investList[$k]['invest_stage']);
                            $worksheet->getCell('H'.$stock_line_number)->setValue($investList[$k]['invest_amount']);
                            $worksheet->getCell('K'.$stock_line_number)->setValue($investList[$k]['gain_time']);
                        }
                    }else{
                        $investNum = 1;
                    }
                }

                //团队成员
                $memberNum = sizeof($memberList);
                if($memberNum > 0){
                    if($memberNum > 1){
                        $worksheet->insertNewRowBefore(21+$stockNum-1+$investNum-1,$memberNum-1);//因为模版已经有一空行，所以减一(参数：行号，增加几行)
                    }
                    for($h = 0;$h < $memberNum;$h++){
                        //计算当前行的行数
                        $member_line_number = 20+$h+$stockNum-1+$investNum-1;//模版本来所在行号+当前添加行数+添加的行数
                        //填充数据
                        $worksheet->getCell('A'.$member_line_number)->setValue($memberList[$h]['member_name']);
                        $worksheet->getCell('B'.$member_line_number)->setValue($memberList[$h]['team_role']);
                        $worksheet->getCell('C'.$member_line_number)->setValue($memberList[$h]['college']);
                        $worksheet->getCell('D'.$member_line_number)->setValue($memberList[$h]['school_name']);
                        $worksheet->getCell('E'.$member_line_number)->setValue($memberList[$h]['major']);
                        $worksheet->getCell('F'.$member_line_number)->setValue($memberList[$h]['grade']);
                        $worksheet->getCell('G'.$member_line_number)->setValue($memberList[$h]['education']);
                        $worksheet->getCell('H'.$member_line_number)->setValue($memberList[$h]['sno']);
                        $worksheet->getCell('I'.$member_line_number)->setValue($memberList[$h]['phone']);
                    }
                }else{
                    $memberNum = 1;
                }
                //指导教师
                $teacherNum = sizeof($teacherList);
                if($teacherNum > 0){
                    if($teacherNum > 1){
                        $worksheet->insertNewRowBefore(24+$stockNum-1+$investNum-1+$memberNum-1,$teacherNum-1);//因为模版已经有一空行，所以减一(参数：行号，增加几行)
                    }
                    for($g = 0;$g < $teacherNum;$g++){
                        //计算当前行的行数
                        $teacher_line_number = 23+$g+$stockNum-1+$investNum-1+$memberNum-1;//模版本来所在行号+当前添加行数+添加的行数
                        //填充数据
                        $worksheet->getCell('A'.$teacher_line_number)->setValue($teacherList[$g]['teacher_name']);
                        $worksheet->getCell('B'.$teacher_line_number)->setValue('指导教师');
                        $worksheet->getCell('C'.$teacher_line_number)->setValue($teacherList[$g]['school_name']);
                        $worksheet->getCell('D'.$teacher_line_number)->setValue($teacherList[$g]['department']);
                        $worksheet->getCell('E'.$teacher_line_number)->setValue($teacherList[$g]['positional_title']);
                        $worksheet->getCell('F'.$teacher_line_number)->setValue($teacherList[$g]['teacher_email']);
                        $worksheet->getCell('G'.$teacher_line_number)->setValue($teacherList[$g]['teacher_mobile']);
                    }
                }else{
                    $teacherNum = 1;
                }
                //已获专利
                $patentNum = sizeof($patentList);
                if($patentNum > 0){
                    for($f = 0;$f < $patentNum;$f++){
                        //计算当前行的行数
                        $patent_line_number = 26+$f+$stockNum-1+$investNum-1+$memberNum-1+$teacherNum-1;//模版本来所在行号+当前添加行数+添加的行数
                        //填充数据
                        $worksheet->getCell('A'.$patent_line_number)->setValue($patentList[$f]['patent_name']);
                        $worksheet->getCell('B'.$patent_line_number)->setValue($patentList[$f]['patent_type']);
                        $worksheet->getCell('C'.$patent_line_number)->setValue($patentList[$f]['patent_no']);
                        $worksheet->getCell('D'.$patent_line_number)->setValue($patentList[$f]['gain_date']);
                    }
                }

            }else{
                $worksheet->getCell('L7')->setValue('已注册社会组织');
                $worksheet->getCell('B9')->setValue($result['rows'][$i]['company_name']);
                $worksheet->getCell('B10')->setValue($result['rows'][$i]['legal_person_name']);
                $worksheet->getCell('I10')->setValue($result['rows'][$i]['legal_person_post']);
                $worksheet->getCell('I11')->setValue($result['rows'][$i]['register_date']);
                $worksheet->getCell('B12')->setValue($result['rows'][$i]['register_address']);
                $worksheet->getCell('I12')->setValue($result['rows'][$i]['credit_code']);
                if($result['rows'][$i]['is_invest'] == 'N'){
                    $worksheet->getCell('B15')->setValue('否');
                }else{
                    $worksheet->getCell('B15')->setValue('是');
                }

                //团队成员
                $memberNum = sizeof($memberList);
                if($memberNum > 0){
                    if($memberNum > 1){
                        $worksheet->insertNewRowBefore(21,$memberNum-1);//因为模版已经有一空行，所以减一(参数：行号，增加几行)
                    }
                    for($h = 0;$h < $memberNum;$h++){
                        //计算当前行的行数
                        $member_line_number = 20+$h;//模版本来所在行号+当前添加行数+添加的行数
                        //填充数据
                        $worksheet->getCell('A'.$member_line_number)->setValue($memberList[$h]['member_name']);
                        $worksheet->getCell('B'.$member_line_number)->setValue($memberList[$h]['team_role']);
                        $worksheet->getCell('C'.$member_line_number)->setValue($memberList[$h]['college']);
                        $worksheet->getCell('D'.$member_line_number)->setValue($memberList[$h]['school_name']);
                        $worksheet->getCell('E'.$member_line_number)->setValue($memberList[$h]['major']);
                        $worksheet->getCell('F'.$member_line_number)->setValue($memberList[$h]['grade']);
                        $worksheet->getCell('G'.$member_line_number)->setValue($memberList[$h]['education']);
                        $worksheet->getCell('H'.$member_line_number)->setValue($memberList[$h]['sno']);
                        $worksheet->getCell('I'.$member_line_number)->setValue($memberList[$h]['phone']);
                    }
                }else{
                    $memberNum = 1;
                }
                //指导教师
                $teacherNum = sizeof($teacherList);
                if($teacherNum > 0){
                    if($teacherNum > 1){
                        $worksheet->insertNewRowBefore(24+$memberNum-1,$teacherNum-1);//因为模版已经有一空行，所以减一(参数：行号，增加几行)
                    }
                    for($g = 0;$g < $teacherNum;$g++){
                        //计算当前行的行数
                        $teacher_line_number = 23+$g+$memberNum-1;//模版本来所在行号+当前添加行数+添加的行数
                        //填充数据
                        $worksheet->getCell('A'.$teacher_line_number)->setValue($teacherList[$g]['teacher_name']);
                        $worksheet->getCell('B'.$teacher_line_number)->setValue('指导教师');
                        $worksheet->getCell('C'.$teacher_line_number)->setValue($teacherList[$g]['school_name']);
                        $worksheet->getCell('D'.$teacher_line_number)->setValue($teacherList[$g]['department']);
                        $worksheet->getCell('E'.$teacher_line_number)->setValue($teacherList[$g]['positional_title']);
                        $worksheet->getCell('F'.$teacher_line_number)->setValue($teacherList[$g]['teacher_email']);
                        $worksheet->getCell('G'.$teacher_line_number)->setValue($teacherList[$g]['teacher_mobile']);
                    }
                }else{
                    $teacherNum = 1;
                }
                //已获专利
                $patentNum = sizeof($patentList);
                if($patentNum > 0){
                    for($f = 0;$f < $patentNum;$f++){
                        //计算当前行的行数
                        $patent_line_number = 26+$f+$memberNum-1+$teacherNum-1;//模版本来所在行号+当前添加行数+添加的行数
                        //填充数据
                        $worksheet->getCell('A'.$patent_line_number)->setValue($patentList[$f]['patent_name']);
                        $worksheet->getCell('B'.$patent_line_number)->setValue($patentList[$f]['patent_type']);
                        $worksheet->getCell('C'.$patent_line_number)->setValue($patentList[$f]['patent_no']);
                        $worksheet->getCell('D'.$patent_line_number)->setValue($patentList[$f]['gain_date']);
                    }
                }
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $excel_url = iconv("UTF-8", "GBK", RUNTIME_PATH .'package_matchitem'.$fileRandomNum.'/'.$item_name.'/'.$item_name.'.xlsx');
            $writer->save($excel_url);
        }
    }

    /**
     * 生成压缩包
     */
    function package(){
        //临时文件的随机后缀
        $fileRandomNum = \fast\Random::alnum();
        //处理传递过来的ids字符串
        $ids = $this->request->post("ids/a");
        $arr_ids = explode(',',$ids[0]);
        $arr = [];
        for($i = 0;$i < sizeof($arr_ids); $i++ ){
            $id = intval($arr_ids[$i]);
            array_push($arr,$id);
            //生成excel
            $this->createExcel($id,$fileRandomNum);
        }
        //查询项目name,项目计划书，一分钟展示视频，其他佐证材料
        $itemModel = new \app\admin\model\match\Matchitem();
        $sort = ['a.first_score'=>'desc','a.item_id'=>'desc'];
        $list = $itemModel->alias("a")
            ->field("a.item_id,a.item_name,a.plan_paper_file,a.vedio_file,a.other_file")
            ->where("a.item_id","in",$arr_ids)
            ->order($sort,'desc')
            ->select();
        //获取列表
        $filedir = RUNTIME_PATH. 'package_matchitem'.$fileRandomNum.'/';//设置文件路径

        $datalist = $this->printDir($filedir);
        $datalist = $this->arraysort($datalist);

        //重新构造数组
        $data = array();
        $pattern = '/\\\\/';
        $filedir = preg_replace($pattern,'/',$filedir);
        foreach( $datalist as $k => $val){
            $name = substr($val['content'],0,strrpos($val['content'],'.'));//文件夹名
            array_push($data,$filedir.$name.'/'.$val['content']);//构造文件路径
        }

        //zip文件后缀随机数
        $zipRandomNum = \fast\Random::alnum();
        $filename = "item_project".$zipRandomNum.".zip"; //最终生成的文件名

        /*
            1.先判断文件是否已存在
            2.文件已存在的情况下，则需要删除文件重新生成
            文件是否删除根据需求而定
        */
        if (file_exists($filename)) {
            unlink($filename);
        }
        if(!file_exists($filename)){
            //重新生成文件
            $zip = new \ZipArchive;//使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释 ，这里的反斜杠\一定不要写错，\表示调用的是PHP自带的类，不然会报not find错误
            // print_r($zip);exit;
            if ($zip->open(RUNTIME_PATH.$filename, \ZipArchive::CREATE)!==TRUE) {
                exit('无法打开文件，或者文件创建失败');
            }
            foreach( $data as $k => $val){
                if(file_exists($val)){
                    // 往压缩包里添加文件时，有2种方法，被注释的第一种是最常用的，只是我的文件名是中文开头的，压缩后出现乱码，故改用了第二种
                    // $zip->addFile( $val, basename( $val));//第二个参数是放在压缩包中的文件名称，如果文件可能会有重复，就需要注意一下
                    $fnm = preg_replace('/^.+[\\\\\\/]/', '', $val);
                    //项目名称若有 / 则替换成 -
                    $item_id = $list[$k]['item_id'];
                    $item_name = $item_id.'-'.$list[$k]['item_name'];
                    if(strstr($item_name,'/')){
                        $item_name = str_replace('/','-',$item_name);
                    }
                    if(mb_strlen($item_name,'UTF8') > 25){
                        $item_name = mb_substr($item_name, 0, 25, 'utf-8').'。。。';
                    }
                    //项目名乱码 或有其他字符无法识别
                    try{
                        $zip_item_name = iconv("UTF-8", "GBK",$item_name);
                    }catch (Exception $e){
                        $zip_item_name = $list[$k]['item_id'];
                    }
                    $zip->addFromString( 'item_project/'.$fnm, file_get_contents($val));//压缩文件中含中文的建议使用这个方法

                    //压缩包中添加三个附件
                    //附件路径前缀
                    $file_prefix = ROOT_PATH."public/";
                    if(!empty($list[$k]['plan_paper_file'])) {
                        $plan_paper_file_suffix = substr(strrchr($list[$k]['plan_paper_file'], '.'), 1);
                        $newPlanName = iconv("UTF-8", "GBK",'项目计划书.'.$plan_paper_file_suffix);
                        if(file_exists($file_prefix . $list[$k]['plan_paper_file'])){
                            $zip->addFromString('item_project/' . $item_id.'-' . $newPlanName, file_get_contents($file_prefix . $list[$k]['plan_paper_file']));
                        }
                    }
                    if(!empty($list[$k]['vedio_file'])){
                        $vedio_file_suffix = substr(strrchr($list[$k]['vedio_file'], '.'), 1);
                        $newVedioName = iconv("UTF-8", "GBK",'一分钟展示视频.'.$vedio_file_suffix);
                        if(file_exists($file_prefix . $list[$k]['vedio_file'])) {
                            $zip->addFromString('item_project/' . $item_id.'-' . $newVedioName, file_get_contents($file_prefix . $list[$k]['vedio_file']));
                        }
                    }
                    if(!empty($list[$k]['other_file'])){
                        $other_file_suffix = substr(strrchr($list[$k]['other_file'], '.'), 1);
                        $newOtherName = iconv("UTF-8", "GBK",'其他佐证资料.'.$other_file_suffix);
                        if(file_exists($file_prefix . $list[$k]['other_file'])) {
                            $zip->addFromString('item_project/' . $item_id.'-' . $newOtherName, file_get_contents($file_prefix . $list[$k]['other_file']));
                        }
                    }
                }
            }
            $zip->close();//关闭
        }
        if(!file_exists(RUNTIME_PATH.$filename)){
            exit("无法找到文件"); //即使创建，仍有可能失败。。。。
        }

        //删除临时文件
        foreach ($list as $key => $value) {
            //项目名称若有 / 则替换成 -
            if(strstr($value['item_name'],'/')){
                $value['item_name'] = str_replace('/','-',$value['item_name']);
            }
            if(mb_strlen($value['item_name'],'UTF8') > 25){
                $value['item_name'] = $value['item_id'].mb_substr($value['item_name'], 0, 25, 'utf-8').'。。。';
            }
            //项目名乱码 或有其他字符无法识别
            try{
                $value['item_name'] = iconv("UTF-8", "GBK", $value['item_name']);
            }catch(Exception $e){
                $value['item_name'] = $value['item_id'];
            }

            $tempFile = RUNTIME_PATH .'/package_matchitem'.$fileRandomNum;
            if(file_exists($tempFile)){
                $this->deldir($tempFile);
            }
        }


        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename='.basename($filename)); //文件名
        header("Content-Type: application/zip"); //zip格式的
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
        header('Content-Length: '. filesize(RUNTIME_PATH.$filename)); //告诉浏览器，文件大小
        @readfile(RUNTIME_PATH.$filename);

        $zipFile = RUNTIME_PATH.$filename;
        if(file_exists($zipFile)){
            unlink($zipFile);
        }
    }

    //遍历目录下文件方法
    function printdir($dir) {
        $files = array();
        //opendir() 打开目录句柄
        if($handle = @opendir($dir)){
            //readdir()从目录句柄中（resource，之前由opendir()打开）读取条目,
            // // 如果没有则返回false
            while(($file = readdir($handle)) !== false){
                //读取条目
                if( $file != ".." && $file != "."){
                    //排除根目录
                    if(is_dir($dir . "/" . $file)) {
                        //如果file 是目录，则递归
                        $files[$file] = $this->printdir($dir . "/" . $file);
                    } else {
                        //获取文件修改日期
                        $filetime = date('Y-m-d H:i:s', filemtime($dir . "/" . $file));
                        //文件修改时间作为健值
                        $files['time'] = $filetime;
                        $files['content'] = $file;
                    }
                }
            }
            @closedir($handle);
            return $files;
        }
    }

    //根据修改时间对数组排序
    function arraysort($aa) {
        $sort_arr = [];
        foreach($aa as $key => $value) {
            $sort_arr[] = $value;

        }
        array_multisort($sort_arr, SORT_ASC, $aa);
        return $aa;
    }


    function deldir($dir) {
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->deldir($fullpath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}
