define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchscore/index' + location.search,
                    add_url: 'match/matchscore/add',
                    edit_url: 'match/matchscore/edit',
                    del_url: 'match/matchscore/del',
                    multi_url: 'match/matchscore/multi',
                    table: 'match_score',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'score_id',
                sortName: 'score_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'score_id', title: __('Score_id')},
                        {field: 'item_id', title: __('Item_id')},
                        {field: 'expert_id', title: __('Expert_id')},
                        {field: 'stage', title: __('Stage'), searchList: {"初赛":__('初赛'),"复赛":__('复赛'),"决赛":__('决赛'),"总决赛":__('总决赛')}, formatter: Table.api.formatter.normal},
                        {field: 'score', title: __('Score'), operate:'BETWEEN'},
                        {field: 'comment', title: __('Comment')},
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