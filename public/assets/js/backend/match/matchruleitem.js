define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    $ruleId = Config.ruleId;

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchruleitem/index' + location.search,
                    add_url: 'match/matchruleitem/add?ruleId=' + $ruleId,
                    edit_url: 'match/matchruleitem/edit?ruleId=' + $ruleId,
                    del_url: 'match/matchruleitem/del',
                    multi_url: 'match/matchruleitem/multi',
                    table: 'match_rule_item',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'rule_item_id',
                sortName: 'rule_item_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'rule_item_id', title: __('Rule_item_id')},
                        {field: 'rule_name', title: __('Rule_name')},
                        {field: 'rule_item_name', title: __('Rule_item_name')},
                        {field: 'rule_item_score', title: __('Rule_item_score')},
                        {field: 'score_level', title: __('Score_level')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});