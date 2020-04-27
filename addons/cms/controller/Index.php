<?php

namespace addons\cms\controller;

use app\admin\model\match\Match;
use think\Config;

/**
 * CMS首页控制器
 * Class Index
 * @package addons\cms\controller
 */
class Index extends Base
{
    public function index()
    {
        //首页动态显示
        $matchModel = new Match();
        $matchList = $matchModel->where("status","eq","正常")->select();
        $committee = '';
        $match_org = '';
        $match_org_contact = '';
        $match_group = '';
        $match_schedule = '';
        $match_prize = '';
        if(sizeof($matchList) != 0){
            $committee = $this->replace_p($matchList[0]['committee']);
            $match_org = $this->replace_p($matchList[0]['match_org']);
            $match_org_contact = $this->replace_p($matchList[0]['match_org_contact']);
            $match_group = $this->replace_p($matchList[0]['match_group']);
            $match_schedule = $this->replace_p($matchList[0]['match_schedule']);
            $match_prize =  $this->replace_p($matchList[0]['match_prize']);
        }
        $title =$this->auth->nickname?"就业指导中心比赛"."/".$this->auth->nickname:"就业指导中心比赛/姓名";
        $this->view->assign("title",$title);

        $this->view->assign("committee",$committee);
        $this->view->assign("match_org",$match_org);
        $this->view->assign("match_org_contact",$match_org_contact);
        $this->view->assign("match_group",$match_group);
        $this->view->assign("match_schedule",$match_schedule);
        $this->view->assign("match_prize",$match_prize);
        Config::set('cms.title', Config::get('cms.title') ? Config::get('cms.title') : __('Home'));
        return $this->view->fetch('/index');
    }

    public function index2()
    {
        Config::set('cms.title', Config::get('cms.title') ? Config::get('cms.title') : __('Home'));
        return $this->view->fetch('/index2');
    }

    public function get_index_list()
    {
        $this->view->engine->layout(false);
        $this->success("", "", $this->view->fetch('common/index_list'));
    }

    /**
     * 去掉内容的p标签
     * @param $str
     * @return mixed
     */
    public function replace_p($str){
        $news = preg_replace('/<p.*?>|<\/p>/is','', $str);
        return $news;
    }
}
