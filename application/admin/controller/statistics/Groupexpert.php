<?php

namespace app\admin\controller\statistics;
use app\admin\model\match\Matchscore;
use app\common\controller\Backend;

/**
 * 参赛项目
 *
 * @icon fa fa-circle-o
 */
class Groupexpert extends Backend
{
    
    /**
     * Groupexpert模型对象
     * @var \app\admin\model\statistics\Groupexpert
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Matchscore();
    }
    
    public function index()
    {
        $this->relationSearch = true;
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total =  $this->model
                ->alias("s")
                ->field("
                a.nickname,
                sum(case  when s.is_score='Y' and s.stage = '初赛' and g.group_name = '创新组' then 1 else 0 end) as cs_cxz_y,
                sum(case when  s.is_score='N' and s.stage = '初赛' and g.group_name = '创新组' then 1 else 0 end) as cs_cxz_n,
                sum(case  when s.is_score='Y' and s.stage = '初赛' and g.group_name = '创意组' then 1 else 0 end) as cs_cyz_y,
                sum(case when  s.is_score='N' and s.stage = '初赛' and g.group_name = '创意组' then 1 else 0 end) as cs_cyz_n,
                sum(case  when s.is_score='Y' and s.stage = '初赛' and g.group_name like '%初创组%' then 1 else 0 end) as cs_ccz_y,
                sum(case when  s.is_score='N' and s.stage = '初赛' and g.group_name  like '%初创组%' then 1 else 0 end) as cs_ccz_n,
                sum(case  when s.is_score='Y' and s.stage = '初赛' and g.group_name like  '%成长组%' then 1 else 0 end) as cs_czz_y,
                sum(case when  s.is_score='N' and s.stage = '初赛' and g.group_name like  '%成长组%' then 1 else 0 end) as cs_czz_n,
                sum(case  when s.is_score='Y' and s.stage = '初赛' then 1 else 0 end) as cs_hj_y,
                sum(case when  s.is_score='N' and s.stage = '初赛' then 1 else 0 end) as cs_hj_n,
                sum(case  when s.is_score='Y' and s.stage = '复赛' and g.group_name = '创新组' then 1 else 0 end) as bjs_cxz_y,
                sum(case when  s.is_score='N' and s.stage = '复赛' and g.group_name = '创新组' then 1 else 0 end) as bjs_cxz_n,
                sum(case  when s.is_score='Y' and s.stage = '复赛' and g.group_name = '创意组' then 1 else 0 end) as bjs_cyz_y,
                sum(case when  s.is_score='N' and s.stage = '复赛' and g.group_name = '创意组' then 1 else 0 end) as bjs_cyz_n,
                sum(case  when s.is_score='Y' and s.stage = '复赛' and g.group_name like '%初创组%' then 1 else 0 end) as bjs_ccz_y,
                sum(case when  s.is_score='N' and s.stage = '复赛' and g.group_name  like '%初创组%' then 1 else 0 end) as bjs_ccz_n,
                sum(case  when s.is_score='Y' and s.stage = '复赛' and g.group_name like  '%成长组%' then 1 else 0 end) as bjs_czz_y,
                sum(case when  s.is_score='N' and s.stage = '复赛' and g.group_name like  '%成长组%' then 1 else 0 end) as bjs_czz_n,
                sum(case  when s.is_score='Y' and s.stage = '复赛' then 1 else 0 end) as bjs_hj_y,
                sum(case when  s.is_score='N' and s.stage = '复赛' then 1 else 0 end) as bjs_hj_n,
                sum(case  when s.is_score='Y' and s.stage = '总决赛' and g.group_name = '创新组' then 1 else 0 end) as zjs_cxz_y,
                sum(case when  s.is_score='N' and s.stage = '总决赛' and g.group_name = '创新组' then 1 else 0 end) as zjs_cxz_n,
                sum(case  when s.is_score='Y' and s.stage = '总决赛' and g.group_name = '创意组' then 1 else 0 end) as zjs_cyz_y,
                sum(case when  s.is_score='N' and s.stage = '总决赛' and g.group_name = '创意组' then 1 else 0 end) as zjs_cyz_n,
                sum(case  when s.is_score='Y' and s.stage = '总决赛' and g.group_name like '%初创组%' then 1 else 0 end) as zjs_ccz_y,
                sum(case when  s.is_score='N' and s.stage = '总决赛' and g.group_name  like '%初创组%' then 1 else 0 end) as zjs_ccz_n,
                sum(case  when s.is_score='Y' and s.stage = '总决赛' and g.group_name like  '%成长组%' then 1 else 0 end) as zjs_czz_y,
                sum(case when  s.is_score='N' and s.stage = '总决赛' and g.group_name like  '%成长组%' then 1 else 0 end) as zjs_czz_n,
                sum(case  when s.is_score='Y' and s.stage = '总决赛' then 1 else 0 end) as zjs_hj_y,
                sum(case when  s.is_score='N' and s.stage = '总决赛' then 1 else 0 end) as zjs_hj_n 
                        ")

                ->join("fa_admin a","s.expert_id=a.id","left")
                ->join("match_item i","i.item_id = s.item_id","left")
                ->join('match_group g','i.group_id=g.group_id','LEFT')
                ->where($where)
                ->where('i.item_status','复赛')
                ->group("a.nickname")
                ->count();

            $list = $this->model
                ->alias("s")
                ->field("
                a.nickname,
                sum(case  when s.is_score='Y' and s.stage = '复赛' and g.group_name = '创新组' then 1 else 0 end) as bjs_cxz_y,
                sum(case when  s.is_score='N' and s.stage = '复赛' and g.group_name = '创新组' then 1 else 0 end) as bjs_cxz_n,
                sum(case  when s.is_score='Y' and s.stage = '复赛' and g.group_name = '创意组' then 1 else 0 end) as bjs_cyz_y,
                sum(case when  s.is_score='N' and s.stage = '复赛' and g.group_name = '创意组' then 1 else 0 end) as bjs_cyz_n,
                sum(case  when s.is_score='Y' and s.stage = '复赛' and g.group_name like '%初创组%' then 1 else 0 end) as bjs_ccz_y,
                sum(case when  s.is_score='N' and s.stage = '复赛' and g.group_name  like '%初创组%' then 1 else 0 end) as bjs_ccz_n,
                sum(case  when s.is_score='Y' and s.stage = '复赛' and g.group_name like  '%成长组%' then 1 else 0 end) as bjs_czz_y,
                sum(case when  s.is_score='N' and s.stage = '复赛' and g.group_name like  '%成长组%' then 1 else 0 end) as bjs_czz_n,
                sum(case  when s.is_score='Y' and s.stage = '复赛' then 1 else 0 end) as bjs_hj_y,
                sum(case when  s.is_score='N' and s.stage = '复赛' then 1 else 0 end) as bjs_hj_n,
                sum(case  when s.is_score='Y' and s.stage = '总决赛' and g.group_name = '创新组' then 1 else 0 end) as zjs_cxz_y,
                sum(case when  s.is_score='N' and s.stage = '总决赛' and g.group_name = '创新组' then 1 else 0 end) as zjs_cxz_n,
                sum(case  when s.is_score='Y' and s.stage = '总决赛' and g.group_name = '创意组' then 1 else 0 end) as zjs_cyz_y,
                sum(case when  s.is_score='N' and s.stage = '总决赛' and g.group_name = '创意组' then 1 else 0 end) as zjs_cyz_n,
                sum(case  when s.is_score='Y' and s.stage = '总决赛' and g.group_name like '%初创组%' then 1 else 0 end) as zjs_ccz_y,
                sum(case when  s.is_score='N' and s.stage = '总决赛' and g.group_name  like '%初创组%' then 1 else 0 end) as zjs_ccz_n,
                sum(case  when s.is_score='Y' and s.stage = '总决赛' and g.group_name like  '%成长组%' then 1 else 0 end) as zjs_czz_y,
                sum(case when  s.is_score='N' and s.stage = '总决赛' and g.group_name like  '%成长组%' then 1 else 0 end) as zjs_czz_n,
                sum(case  when s.is_score='Y' and s.stage = '总决赛' then 1 else 0 end) as zjs_hj_y,
                sum(case when  s.is_score='N' and s.stage = '总决赛' then 1 else 0 end) as zjs_hj_n 
                        ")
                ->join("fa_admin a","s.expert_id=a.id","left")
                ->join("match_item i","i.item_id = s.item_id","left")
                ->join('match_group g','i.group_id=g.group_id','LEFT')
                ->where($where)
                ->group("a.nickname")
                ->limit($offset , $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

}
