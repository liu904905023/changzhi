define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'statistics/groupexpert/index' + location.search,
                    add_url: 'statistics/groupexpert/add',
                    edit_url: 'statistics/groupexpert/edit',
                    del_url: 'statistics/groupexpert/del',
                    multi_url: 'statistics/groupexpert/multi',
                    table: 'match_item',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'item_id',
                sortName: 'item_id',
                columns: [
                    [
                        {title: __(''),colspan:1},

                        {title: __('复赛'),colspan:5},
                        {title: __('总决赛'),colspan:5}
                    ],
                    [
                        {field: 'nickname', title: __('评委')},

                        {field: 'bjs_cxz', title: __('创新组'),operate:false,formatter:function(index,value){
                                var total = parseFloat(value.bjs_cxz_y)+parseFloat(value.bjs_cxz_n);
                                return  value.bjs_cxz_y+"/"+total;
                            }},//cs_cxz_y
                        {field: 'bjs_cyz', title: __('创意组'),operate:false,formatter:function(index,value){
                                var total = parseFloat(value.bjs_cyz_y)+parseFloat(value.bjs_cyz_n);
                                return  value.bjs_cyz_y+"/"+total;
                            }},
                        {field: 'bjs_ccz', title: __('初创组'),operate:false,formatter:function(index,value){
                                var total = parseFloat(value.bjs_ccz_y)+parseFloat(value.bjs_ccz_n);
                                return  value.bjs_ccz_y+"/"+total;
                            }},
                        {field: 'bjs_czz', title: __('成长组'),operate:false,formatter:function(index,value){
                                var total = parseFloat(value.bjs_czz_y)+parseFloat(value.bjs_czz_n);
                                return  value.bjs_czz_y+"/"+total;
                            }},
                        {field: 'bjs_hj', title: __('合计'),operate:false,formatter:function(index,value){
                                var html = '';
                                var total = parseFloat(value.bjs_hj_n)+parseFloat(value.bjs_hj_y);
                                var proBar = (value.bjs_hj_y/(total))*100;
                                html = '<div class="progress">\n' +
                                    '    <div class="progress-bar progress-bar-info" role="progressbar"' +
                                    '        aria-valuemin="0" aria-valuemax="100" style="width: '+proBar+'%;">\n' +
                                    //'        <span class="sr-only">40% 完成</span>\n' +
                                    '<span style="color: black">'+value.bjs_hj_y + '/'+total+'</span>'+
                                    '    </div>\n' +
                                    '</div>';
                                return html;
                            }},

                        {field: 'zjs_cxz',title: __('创新组'),operate:false,formatter:function(index,value){
                                var total = parseFloat(value.zjs_cxz_y)+parseFloat(value.zjs_cxz_n);
                                return  value.zjs_cxz_y+"/"+total;
                            }},//cs_cxz_y
                        {field: 'zjs_cyz', title: __('创意组'),operate:false,formatter:function(index,value){
                                var total = parseFloat(value.zjs_cyz_y)+parseFloat(value.zjs_cyz_n);
                                return  value.zjs_cyz_y+"/"+total;
                            }},
                        {field: 'zjs_ccz', title: __('初创组'),operate:false,formatter:function(index,value){
                                var total = parseFloat(value.zjs_ccz_y)+parseFloat(value.zjs_ccz_n);
                                return  value.zjs_ccz_y+"/"+total;
                            }},
                        {field: 'zjs_czz', title: __('成长组'),operate:false,formatter:function(index,value){
                                var total = parseFloat(value.zjs_czz_y)+parseFloat(value.zjs_czz_n);
                                return  value.zjs_czz_y+"/"+total;
                            }},
                        {field: 'zjs_hj', title: __('合计'),operate:false,formatter:function(index,value){
                                var html = '';
                                var total = parseFloat(value.zjs_hj_y)+parseFloat(value.zjs_hj_n);
                                var proBar = (value.zjs_hj_y/total)*100;
                                html = '<div class="progress">\n' +
                                    '    <div class="progress-bar progress-bar-info" role="progressbar"' +
                                    '        aria-valuemin="0" aria-valuemax="100" style="width: '+proBar+'%;">\n' +
                                    //'        <span class="sr-only">40% 完成</span>\n' +
                                    '<span style="color: black">'+value.zjs_hj_y + '/'+total+'</span>'+
                                    '    </div>\n' +
                                    '</div>';
                                return html;
                            }}
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