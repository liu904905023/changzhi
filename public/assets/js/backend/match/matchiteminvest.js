define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchiteminvest/index' + location.search,
                    add_url: 'match/matchiteminvest/add',
                    edit_url: 'match/matchiteminvest/edit',
                    del_url: 'match/matchiteminvest/del',
                    multi_url: 'match/matchiteminvest/multi',
                    table: 'match_item_invest',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'invest_id',
                sortName: 'invest_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'invest_id', title: __('Invest_id')},
                        {field: 'match_id', title: __('Match_id')},
                        {field: 'invest_name', title: __('Invest_name')},
                        {field: 'invest_stage', title: __('Invest_stage')},
                        {field: 'invest_amount', title: __('Invest_amount')},
                        {field: 'gain_time', title: __('Gain_time'), operate:'RANGE', addclass:'datetimerange'},
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