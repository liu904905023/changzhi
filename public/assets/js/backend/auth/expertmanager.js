define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'auth/expertmanager/index',
                    add_url: 'auth/expertmanager/add',
                    edit_url: 'auth/expertmanager/edit',
                    del_url: 'auth/expertmanager/del',
                    multi_url: 'auth/expertmanager/multi',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID'},
                        {field: 'nickname', title: __('ExpertName')},
                        {field: 'email', title: __('Email')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'technical_title', title: __('Technical_title')}
                    ]
                ]
            });


            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});