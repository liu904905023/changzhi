<?php

namespace app\admin\model\match;

use think\Model;


class Matchitem extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'match_item';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'is_hightech_text',
        'is_founder_lead_text',
        'is_together_text',
        'is_team_text',
        'is_invest_text',
        'is_private_text',
        'is_red_text',
        'audit_status_text',
        'item_status_text'
    ];
    

    
    public function getIsHightechList()
    {
        return ['Y' => __('Y'), 'N' => __('N')];
    }

    public function getIsFounderLeadList()
    {
        return ['Y' => __('Y'), 'N' => __('N')];
    }

    public function getIsTogetherList()
    {
        return ['Y' => __('Y'), 'N' => __('N')];
    }

    public function getIsTeamList()
    {
        return ['Y' => __('Y'), 'N' => __('N')];
    }

    public function getIsInvestList()
    {
        return ['Y' => __('Y'), 'N' => __('N')];
    }

    public function getIsPrivateList()
    {
        return ['Y' => __('Y'), 'N' => __('N')];
    }

    public function getIsRedList()
    {
        return ['Y' => __('Y'), 'N' => __('N')];
    }

    public function getAuditStatusList()
    {
        return ['草稿' => __('草稿'), '待审核' => __('待审核'), '审核通过' => __('审核通过'), '审核未通过' => __('审核未通过'), '归档' => __('归档')];
    }

    public function getItemStatusList()
    {
        return ['初赛' => __('初赛'), '复赛' => __('复赛'), '总决赛' => __('总决赛'), '答辩' => __('答辩')];
    }


    public function getIsHightechTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_hightech']) ? $data['is_hightech'] : '');
        $list = $this->getIsHightechList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsFounderLeadTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_founder_lead']) ? $data['is_founder_lead'] : '');
        $list = $this->getIsFounderLeadList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsTogetherTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_together']) ? $data['is_together'] : '');
        $list = $this->getIsTogetherList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsTeamTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_team']) ? $data['is_team'] : '');
        $list = $this->getIsTeamList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsInvestTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_invest']) ? $data['is_invest'] : '');
        $list = $this->getIsInvestList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsPrivateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_private']) ? $data['is_private'] : '');
        $list = $this->getIsPrivateList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsRedTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_red']) ? $data['is_red'] : '');
        $list = $this->getIsRedList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAuditStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['audit_status']) ? $data['audit_status'] : '');
        $list = $this->getAuditStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getItemStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['item_status']) ? $data['item_status'] : '');
        $list = $this->getItemStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
