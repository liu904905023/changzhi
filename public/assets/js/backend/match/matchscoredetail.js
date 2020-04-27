define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchscoredetail/index' + location.search,
                    add_url: 'match/matchscoredetail/add',
                    edit_url: 'match/matchscoredetail/edit',
                    del_url: 'match/matchscoredetail/del',
                    multi_url: 'match/matchscoredetail/multi',
                    table: 'match_score_detail',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'score_id', title: __('Score_id')},
                        {field: 'rule_id', title: __('Rule_id')},
                        {field: 'rule_name', title: __('Rule_name')},
                        {field: 'total_score', title: __('Total_score')},
                        {field: 'score', title: __('Score')},
                        {field: 'score_desc', title: __('Score_desc')},
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