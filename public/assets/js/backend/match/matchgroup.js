define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    $trackId = Config.trackId;
    $matchId = Config.matchId;
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchgroup/index' + location.search,
                    add_url: 'match/matchgroup/add?trackId=' + $trackId +'&matchId=' + $matchId,
                    edit_url: 'match/matchgroup/edit?trackId=' + $trackId +'&matchId=' + $matchId,
                    del_url: 'match/matchgroup/del',
                    multi_url: 'match/matchgroup/multi',
                    table: 'match_group',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'group_id',
                sortName: 'group_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'match_name', title: __('Match_name')},
                        {field: 'track_name', title: __('Track_name')},
                        {field: 'group_name', title: __('Group_name')},
                        {field: 'rule_name', title: __('Rule_name')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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