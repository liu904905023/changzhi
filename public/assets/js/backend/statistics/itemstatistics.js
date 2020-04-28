define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {

        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'statistics/itemstatistics/index' + location.search,
                    table: 'match_item'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'item_id',
                sortName: 'item_id',
                paginationDetailHAlign:' hidden',//去掉分页 hidden前的空格不能去掉
                searchFormVisible: true,//普通搜索显示
                columns: [
                    [
                        {checkbox: true},
                        {field: 'school_name', title: __('学校'),operate:false},
                        {field: 'num', title: __('作品总数'),operate:false},
                        {field: 'pass', title: __('审核通过'),operate:false,formatter:function(index,value){
                            if(value.pass == null){
                                return 0;
                            }else{
                                return value.pass;
                            }
                        }},
                        {field: 'halfMatch', title: __('复赛'),operate:false,formatter:function(index,value){
                            if(value.halfMatch == null){
                                return 0;
                            }else{
                                return value.halfMatch;
                            }
                        }},
                        {field: 'totalMatch', title: __('总决赛'),operate:false,formatter:function(index,value){
                            if(value.totalMatch == null){
                                return 0;
                            }else{
                                return value.totalMatch;
                            }
                        }},
                        {field: 'defence', title: __('答辩'),operate:false,formatter:function(index,value){
                            if(value.defence == null){
                                return 0;
                            }else{
                                return value.defence;
                            }
                        }},
                        {field: 'i.createtime', title: __('创建时间'),visible:false, operate:'RANGE', addclass:'datetimerange'}
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