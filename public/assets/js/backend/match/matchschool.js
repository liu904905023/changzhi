define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchschool/index' + location.search,
                    add_url: 'match/matchschool/add',
                    edit_url: 'match/matchschool/edit',
                    del_url: 'match/matchschool/del',
                    multi_url: 'match/matchschool/multi',
                    table: 'match_school',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'school_id',
                sortName: 'school_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'school_id', title: __('School_id')},
                        {field: 'school_name', title: __('School_name')},
                        {field: 'city', title: __('City')},
                        {field: 'logo_image', title: __('Logo_image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'nickname', title: __('Admin_id')},
                        {field: 'createtime', title: __('Createtime'), formatter: Table.api.formatter.datetime},
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