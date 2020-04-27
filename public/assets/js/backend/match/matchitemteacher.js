define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchitemteacher/index' + location.search,
                    add_url: 'match/matchitemteacher/add',
                    edit_url: 'match/matchitemteacher/edit',
                    del_url: 'match/matchitemteacher/del',
                    multi_url: 'match/matchitemteacher/multi',
                    table: 'match_item_teacher',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'teacher_id',
                sortName: 'teacher_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'teacher_id', title: __('Teacher_id')},
                        {field: 'item_id', title: __('Item_id')},
                        {field: 'teacher_name', title: __('Teacher_name')},
                        {field: 'teacher_mobile', title: __('Teacher_mobile')},
                        {field: 'teacher_email', title: __('Teacher_email')},
                        {field: 'school_id', title: __('School_id')},
                        {field: 'department', title: __('Department')},
                        {field: 'positional_title', title: __('Positional_title')},
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