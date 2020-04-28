define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'statistics/group/index' + location.search,
                    add_url: 'statistics/group/add',
                    edit_url: 'statistics/group/edit',
                    del_url: 'statistics/group/del',
                    multi_url: 'statistics/group/multi',
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
                        {title: __(''),colspan:1},
                        {title: __('初赛'),colspan:5},
                        {title: __('复赛'),colspan:5},
                        {title: __('总决赛'),colspan:5}
                    ],
                    [
                        {field: 'school_name', title: __('学校'),operate:false},
                        {field: 'cs_cxz', title: __('创新组'),operate:false},
                        {field: 'cs_cyz', title: __('创意组'),operate:false},
                        {field: 'cs_ccz', title: __('初创组'),operate:false},
                        {field: 'cs_czz', title: __('成长组'),operate:false},
                        {field: 'cs_num', title: __('合计'),operate:false},

                        {field: 'bjs_cxz', title: __('创新组'),operate:false},
                        {field: 'bjs_cyz', title: __('创意组'),operate:false},
                        {field: 'bjs_ccz', title: __('初创组'),operate:false},
                        {field: 'bjs_czz', title: __('成长组'),operate:false},
                        {field: 'bjs_num', title: __('合计'),operate:false},

                        {field: 'zjs_cxz', title: __('创新组'),operate:false},
                        {field: 'zjs_cyz', title: __('创意组'),operate:false},
                        {field: 'zjs_ccz', title: __('初创组'),operate:false},
                        {field: 'zjs_czz', title: __('成长组'),operate:false},
                        {field: 'zjs_num', title: __('合计'),operate:false}
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