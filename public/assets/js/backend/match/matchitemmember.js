define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchitemmember/index' + location.search,
                    add_url: 'match/matchitemmember/add',
                    edit_url: 'match/matchitemmember/edit',
                    del_url: 'match/matchitemmember/del',
                    multi_url: 'match/matchitemmember/multi',
                    table: 'match_item_member',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'member_id',
                sortName: 'member_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'member_id', title: __('Member_id')},
                        {field: 'item_id', title: __('Item_id')},
                        {field: 'member_name', title: __('Member_name')},
                        {field: 'school_id', title: __('School_id')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'team_role', title: __('Team_role')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'is_accept', title: __('Is_accept'), searchList: {"Y":__('Y'),"N":__('N')}, formatter: Table.api.formatter.normal},
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