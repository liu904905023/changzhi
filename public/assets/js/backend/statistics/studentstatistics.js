define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {

        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'statistics/studentstatistics/index' + location.search,
                    table: 'match_school'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'school_id',
                sortName: 'school_id',
                paginationDetailHAlign:' hidden',//去掉分页 hidden前的空格不能去掉
                searchFormVisible: true,//普通搜索显示
                columns: [
                    [
                        {checkbox: true},
                        {field: 'schoolName', title: __('学校'),operate:false},
                        {field: 'num', title: __('注册人数'),operate:false},
                        {field: 'fa_user.createtime', title: __('注册时间'),visible:false, operate:'RANGE', addclass:'datetimerange'}
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