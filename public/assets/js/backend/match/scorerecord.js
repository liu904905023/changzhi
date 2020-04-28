define(['jquery', 'bootstrap', 'backend', 'table', 'form','template'], function ($, undefined, Backend, Table, Form,Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/scorerecord/index' + location.search,
                    table: 'match_item'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                searchFormVisible: true,//普通搜索显示
                pageSize:50,
                pk: 'item_id',
                sortName: 'item_id',
                exportOptions: {
                    ignoreColumn: [0, 'operate'] //默认不导出第一列(checkbox)与操作(operate)列
                },
                columns: [
                    [
                        {checkbox: true,rowspan: 2},
                        {field: 'item_id', title: __('Id'),rowspan: 2,operate:false},
                        {field: 'track_name', title: __('Track_id'),rowspan: 2,operate:false},
                        // {field: 'group_name', title: __('Group_id'),rowspan: 2,operate:false},
                        {field: 'item_name', title: __('Item_name'),rowspan: 2,operate:'like'},
                        {field: 'school_name', title: __('School_name'),rowspan: 2,operate:false},
                        {field: 'nickname', title: __('User_id'),rowspan: 2,operate:false},
                        {field: 'ms.school_id', title: __('School_name'),rowspan: 2, visible:false, searchList: $.getJSON("ajax/schoolselect")},
                        {field: 'trackgroup',rowspan: 2, visible:false, title: __('Track_group'), searchList: function (column) {
                            return Template('sourcetpl', {});
                        }},
                        {field: 'scoreCount', title: __('Score_count'),rowspan: 2,operate:false,formatter:function(index,value){
                            if(value.expertCount == 0){
                                return "无";
                            }else if(value.expertCount != 0 && value.scoreCount == 0){
                                return "<span style='color: red'>未评分</span>";
                            }else if(value.expertCount != 0 && value.scoreCount > 0 && value.scoreCount < value.expertCount){
                                return "<span style='color: blue'>评分中</span>";
                            }else if(value.expertCount != 0 && value.scoreCount == value.expertCount){
                                return "<span style='color: green'>已评分</span>";
                            }
                        }},
                        {field: '', title: __('Half_match'),colspan: 3,operate:false},
                        {field: '', title: __('Final_match'),colspan: 3,operate:false}
                    ],
                    [
                        {
                            field: 'half_score',
                            title: __('Half_match_score'),
                            operate:false,
                            align:"center",
                            formatter:function(index,value){
                                if(value.expertCount != 0 && value.scoreCount == value.expertCount){
                                    return value.half_score;
                                }
                            }
                        },
                        {
                            field: 'score_record',
                            title: __('Score_record'),
                            operate:false,
                            align:"center",
                            formatter:function(index,value){
                                if(value.expertCount != 0 && value.scoreCount == value.expertCount){
                                    var arr = value.score_record.split(",");
                                    var res = "";
                                    for(var i = 0;i < arr.length;i++){
                                        if(arr[i].search("复赛") > -1){
                                            res += arr[i].replace("复赛","") + "<br />";
                                        }
                                    }
                                    return res;
                                }
                            }
                        },
                        {
                            field: 'score_detail',
                            title: __('Score_detail'),
                            operate:false,
                            align:"center",
                            formatter:function(index,value){
                                if(value.expertCount != 0 && value.scoreCount == value.expertCount){
                                    if(value.score_detail) {
                                        var arr = value.score_detail.split(",");
                                    }else {
                                        arr = '';
                                    }
                                    var names = value.ad_name.split(",");
                                    var res = "";
                                    for(var i = 0;i < arr.length;i++){
                                        for(var j = 0;j < arr.length;j++) {
                                            if (arr[i].indexOf(names[j]) > -1 && arr[i].search("复赛") > -1) {
                                                if(i == 0){
                                                    res += "【" + names[j] + "】：" + arr[i].replace(names[j],"").replace("复赛","") + "，";
                                                }else if((i + 1) % 4 == 0){
                                                    res += arr[i].replace(names[j],"").replace("复赛","") + "<br />";
                                                }else if(i % 4 == 0){
                                                    res += "【" + names[j] + "】：" + arr[i].replace(names[j],"").replace("复赛","") + "，";
                                                }else{
                                                    res += arr[i].replace(names[j],"").replace("复赛","") + "，";
                                                }
                                            }
                                        }
                                    }
                                    return res;
                                }
                            }
                        },
                        {
                            field: 'final_score',
                            title: __('Half_match_score'),
                            operate:false,
                            align:"center",
                            formatter:function(index,value){
                                if(value.expertCount != 0 && value.scoreCount == value.expertCount){
                                    return value.final_score;
                                }
                            }
                        },
                        {
                            field: 'score_record',
                            title: __('Score_record'),
                            operate:false,
                            align:"center",
                            formatter:function(index,value){
                                if(value.expertCount != 0 && value.scoreCount == value.expertCount){
                                    var arr = value.score_record.split(",");
                                    var res = "";
                                    for(var i = 0;i < arr.length;i++){
                                        if(arr[i].search("总决赛") > -1){
                                            res += arr[i].replace("总决赛","") + "<br />";
                                        }
                                    }
                                    return res;
                                }
                            }
                        },
                        {
                            field: 'score_detail',
                            title: __('Score_detail'),
                            operate:false,
                            align:"center",
                            formatter:function(index,value){
                                if(value.expertCount != 0 && value.scoreCount == value.expertCount){
                                    if(value.score_detail) {
                                        var arr = value.score_detail.split(",");
                                    }else {
                                        arr = '';
                                    }
                                    var names = value.ad_name.split(",");
                                    var res = "";
                                    for(var i = 0;i < arr.length;i++){
                                        for(var j = 0;j < arr.length;j++) {
                                            if (arr[i].indexOf(names[j]) > -1 && arr[i].search("总决赛") > -1) {
                                                if(i == 0){
                                                    res += "【" + names[j] + "】：" + arr[i].replace(names[j],"").replace("总决赛","") + "，";
                                                }else if((i + 1) % 4 == 0){
                                                    res += arr[i].replace(names[j],"").replace("总决赛","") + "<br />";
                                                }else if(i % 4 == 0){
                                                    res += "【" + names[j] + "】：" + arr[i].replace(names[j],"").replace("总决赛","") + "，";
                                                }else{
                                                    res += arr[i].replace(names[j],"").replace("总决赛","") + "，";
                                                }
                                            }
                                        }
                                    }
                                    return res;
                                }
                            }
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