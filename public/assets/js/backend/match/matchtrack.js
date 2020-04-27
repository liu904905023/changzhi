define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    $matchId = Config.matchId;
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchtrack/index' + location.search,
                    add_url: 'match/matchtrack/add?matchId=' + $matchId,
                    edit_url: 'match/matchtrack/edit?matchId=' + $matchId,
                    del_url: 'match/matchtrack/del',
                    multi_url: 'match/matchtrack/multi',
                    table: 'match_track',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'track_id',
                sortName: 'track_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'match_name', title: __('Match_name')},
                        {field: 'track_name', title: __('Track_name')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'group',
                                    text: __('比赛组别'),
                                    title: __('比赛组别'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'match/matchgroup/index?trackId={ids}&matchId='+ Config.matchId
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