define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/match/index' + location.search,
                    add_url: 'match/match/add',
                    edit_url: 'match/match/edit',
                    del_url: 'match/match/del',
                    multi_url: 'match/match/multi',
                    table: 'match',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'match_id',
                sortName: 'match_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'match_id', title: __('Match_id')},
                        {field: 'match_name', title: __('Match_name')},
                        {field: 'sign_start_time', title: __('Sign_start_time'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'sign_end_time', title: __('Sign_end_time'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'status', title: __('Status'), searchList: {"归档":__('归档'),"正常":__('正常')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'track',
                                    text: __('比赛赛道'),
                                    title: __('比赛赛道'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'match/matchtrack/index?id={ids}'
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