define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchitemstock/index' + location.search,
                    add_url: 'match/matchitemstock/add',
                    edit_url: 'match/matchitemstock/edit',
                    del_url: 'match/matchitemstock/del',
                    multi_url: 'match/matchitemstock/multi',
                    table: 'match_item_stock',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'stock_id',
                sortName: 'stock_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'stock_id', title: __('Stock_id')},
                        {field: 'item_id', title: __('Item_id')},
                        {field: 'stockholder_type', title: __('Stockholder_type')},
                        {field: 'stockholder', title: __('Stockholder')},
                        {field: 'hold_ratio', title: __('Hold_ratio'), operate:'BETWEEN'},
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