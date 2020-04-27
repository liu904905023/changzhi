define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchrule/index' + location.search,
                    add_url: 'match/matchrule/add',
                    edit_url: 'match/matchrule/edit',
                    del_url: 'match/matchrule/del',
                    multi_url: 'match/matchrule/multi',
                    table: 'match_rule',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'rule_id',
                sortName: 'rule_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'rule_id', title: __('Rule_id')},
                        {field: 'rule_name', title: __('Rule_name')},
                        {field: 'rule_desc', title: __('Rule_desc')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'ruleItem',
                                    text: __('评分规则明细'),
                                    title: __('评分规则明细'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'match/matchruleitem/index?ruleId={ids}'
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        }
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