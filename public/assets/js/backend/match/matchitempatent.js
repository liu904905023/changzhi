define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchitempatent/index' + location.search,
                    add_url: 'match/matchitempatent/add',
                    edit_url: 'match/matchitempatent/edit',
                    del_url: 'match/matchitempatent/del',
                    multi_url: 'match/matchitempatent/multi',
                    table: 'match_item_patent',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'patent_id',
                sortName: 'patent_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'patent_id', title: __('Patent_id')},
                        {field: 'item_id', title: __('Item_id')},
                        {field: 'patent_name', title: __('Patent_name')},
                        {field: 'patent_no', title: __('Patent_no')},
                        {field: 'gain_date', title: __('Gain_date'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'patent_type', title: __('Patent_type')},
                        {field: 'patent_image', title: __('Patent_image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
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