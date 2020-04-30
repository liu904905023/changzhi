define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var auth_group_id = Config.auth_group_id;

    //审核按钮点击事件
    $("#success").click(function(){
        var item_id = $("#item_id").val();
        Fast.api.ajax({
            url:'match/matchitemschool/audit',
            data:{item_id:item_id,status:"success"}
        },function (data,ret) {
            Fast.api.close(data);
            return false;
        });
    });

    $("#failed").click(function(){
        var item_id = $("#item_id").val();
        Fast.api.ajax({
            url:'match/matchitemschool/audit',
            data:{item_id:item_id,status:'failed'}
        },function (data,ret) {
            Fast.api.close(data);
            return false;
        });
    });

    //关闭查看页面
    $("#closeButton").click(function (data) {
        var index = parent.Layer.getFrameIndex(window.name);
        parent.Layer.close(index);
    });

    //初赛评分按钮
    $("#scoreButton").click(function (data) {
        var item_id = $("#item_id").val();
        var firstScore = $("#first_score").val();
        if(parseFloat(firstScore) > 100){
            alert("评分不能高于100");
            return false;
        }
        Fast.api.ajax({
            url:'match/matchitemschool/firstScore',
            data:{item_id:item_id,firstScore:firstScore}
        },function (data,ret) {
            Fast.api.close(data);
            return false;
        });
    });

    //初赛评分文本输入验证
    $("#first_score").keyup(function(){
        var first_score = $(this).val();
        //限定只允许输入小数或数字
        $(this).val(first_score.replace(/[^0-9.]/g, ''));
        //判断 int 还是float
        var s = 0;
        if(first_score % 1 != 0) {
            // float 则保留两位小数
            s = parseFloat(first_score.substring(0, first_score.indexOf(".") + 3));
            $(this).val(isNaN(s) ? '' : s);
        }
    });

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchitemschool/index' + location.search,
                    add_url: 'match/matchitemschool/add',
                    edit_url: 'match/matchitemschool/edit',
                    del_url: 'match/matchitemschool/del',
                    multi_url: 'match/matchitemschool/multi',
                    table: 'match_item',
                }
            });

            var table = $("#table");
            if(auth_group_id == '2'){
                // 初始化表格-平台管理员
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    searchFormVisible: true,//普通搜索显示
                    pageSize:50,
                    pk: 'item_id',
                    sortName: 'item_id',
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'item_id', title: __('Id'),operate:false},
                            {field: 'track_name', title: __('Track_id'),operate:false},
                            {field: 'track_id', title: __('Track_id'),visible:false, searchList: $.getJSON("ajax/trackList")},
                            // {field: 'group_id', title: __('Group_id'),visible:false, searchList: $.getJSON("ajax/groupList")},

                            {field: 'item_name', title: __('Item_name'),operate:'like'},
                            {field: 'audit_status', title: __('Audit_status'), searchList: {"草稿":__('草稿'),"待审核":__('待审核'),"审核通过":__('审核通过'),"审核未通过":__('审核未通过'),"归档":__('归档')}},
                            {field: 'item_status', title: __('Item_status'), searchList: {"初赛":__('初赛'),"复赛":__('复赛'),"总决赛":__('总决赛')},"答辩":__('答辩'), formatter: Table.api.formatter.status},
                            {field: 'scoreCount', title: __('Score_count'),operate:false,formatter:function(index,value){
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
                            {field: 'first_score', title: __('First_score'),operate:false},
                            {field: 'half_score', title: __('Half_match_score'),operate:false,formatter:function(index,value){
                                if(value.expertCount != 0 && value.scoreCount == value.expertCount){
                                    return value.half_score;
                                }
                            }},
                            {field: 'final_score', title: __('Final_match_score'),operate:false,formatter:function(index,value){
                                if(value.expertCount != 0 && value.scoreCount == value.expertCount && value.item_status == '总决赛'){
                                    return value.final_score;
                                }
                            }},
                            {field: 'nickname', title: __('User_id'),operate:false},
                            {field: 'school_name', title: __('School_name'),operate:false},
                            {field: 'ms.school_id', title: __('School_name'), visible:false,
                                searchList:$.getJSON("ajax/schoolselect"),
                            },
                            // {field: 'college', title: __('College'),operate:false},
                            // {field: 'major', title: __('Major'),operate:false},
                            // {field: 'education', title: __('Education'),operate:false},
                            {field: 'createtime', title: __('Signtime'),visible:false, operate:'RANGE', addclass:'datetimerange'},
                            {field: 'operate', title: __('Operate'), table: table,
                                buttons: [
                                    {
                                        name: 'audit',
                                        text: __('审核'),
                                        title: __('审核'),
                                        hidden:function(row){
                                            if(row.audit_status != '待审核'){
                                                return true;
                                            }
                                            if(auth_group_id == '2'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-audit'
                                    },
                                    {
                                        name: 'recommend',
                                        text: __('推荐'),
                                        title: __('推荐'),
                                        hidden:function(row){
                                            if(row.audit_status != '审核通过' || row.item_status == '复赛' ||  row.item_status == '总决赛'){
                                                return true;
                                            }
                                            if(auth_group_id == '2'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-recommend'
                                    },
                                    {
                                        name: 'viewDetail',
                                        text: __('查看'),
                                        title: __('查看'),
                                        classname: 'btn btn-xs btn-primary btn-viewDetail'
                                    },
                                    {
                                        name: 'reBack',
                                        text: __('退回修改'),
                                        title: __('退回修改'),
                                        hidden:function(row){
                                            if(row.audit_status != '审核通过' || (row.item_status != '初赛')){
                                                return true;
                                            }
                                            if(auth_group_id == '2'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-reBack'
                                    },
                                    {
                                        name: 'firstScore',
                                        text: __('初赛评分'),
                                        title: __('初赛评分'),
                                        hidden:function(row){
                                            if(row.item_status != '初赛' || row.audit_status == '退回修改'){
                                                return true;
                                            }
                                            if(auth_group_id == '2'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-firstScore'
                                    }
                                ],
                                events: Controller.api.events.operate,
                                formatter: Table.api.formatter.buttons
                            }                    ]
                    ]
                });
            }else if(auth_group_id == '3'){
                // 初始化表格-学校管理员
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    searchFormVisible: true,//普通搜索显示
                    pageSize:50,
                    pk: 'item_id',
                    sortName: 'item_id',
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'item_id', title: __('Id'),operate:false},
                            {field: 'track_name', title: __('Track_id'),operate:false},
                            {field: 'track_id', title: __('Track_id'),visible:false, searchList: $.getJSON("ajax/trackList")},
                            // {field: 'group_name', title: __('Group_id'),operate:false},
                            {field: 'group_id', title: __('Group_id'),visible:false, searchList: $.getJSON("ajax/groupList")},

                            {field: 'item_name', title: __('Item_name'),operate:'like'},
                            {field: 'audit_status', title: __('Audit_status'), searchList: {"草稿":__('草稿'),"待审核":__('待审核'),"审核通过":__('审核通过'),"审核未通过":__('审核未通过'),"归档":__('归档')}},
                            {field: 'item_status', title: __('Item_status'), searchList: {"初赛":__('初赛'),"复赛":__('复赛'),"总决赛":__('总决赛')},"答辩":__('答辩'), formatter: Table.api.formatter.status},
                            {field: 'scoreCount', title: __('Score_count'),operate:false,formatter:function(index,value){
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
                            {field: 'first_score', title: __('First_score'),operate:false},

                            {field: 'nickname', title: __('User_id'),operate:false},
                            // {field: 'teacher_name', title: __('Teacher_name'),operate:false},
                            // {field: 'school_name', title: __('School_name'),operate:false},
                            // {field: 'college', title: __('College'),operate:false},
                            // {field: 'major', title: __('Major'),operate:false},
                            // {field: 'education', title: __('Education'),operate:false},
                            // {field: 'createtime', title: __('Signtime'),visible:false, operate:'RANGE', addclass:'datetimerange'},
                            {field: 'operate', title: __('Operate'), table: table,
                                buttons: [
                                    {
                                        name: 'audit',
                                        text: __('审核'),
                                        title: __('审核'),
                                        hidden:function(row){
                                            if(row.audit_status != '待审核'){
                                                return true;
                                            }
                                            if(auth_group_id == '2'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-audit'
                                    },
                                    {
                                        name: 'recommend',
                                        text: __('推荐'),
                                        title: __('推荐'),
                                        hidden:function(row){
                                            if(row.audit_status != '审核通过' || row.item_status == '复赛' ||  row.item_status == '总决赛'){
                                                return true;
                                            }
                                            if(auth_group_id == '2'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-recommend'
                                    },
                                    {
                                        name: 'viewDetail',
                                        text: __('查看'),
                                        title: __('查看'),
                                        classname: 'btn btn-xs btn-primary btn-viewDetail'
                                    },
                                    {
                                        name: 'reBack',
                                        text: __('退回修改'),
                                        title: __('退回修改'),
                                        hidden:function(row){
                                            if(row.audit_status != '审核通过' || (row.item_status != '初赛')){
                                                return true;
                                            }
                                            if(auth_group_id == '2'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-reBack'
                                    },
                                    {
                                        name: 'firstScore',
                                        text: __('初赛评分'),
                                        title: __('初赛评分'),
                                        hidden:function(row){
                                            if(row.item_status != '初赛' || row.audit_status == '退回修改'){
                                                return true;
                                            }
                                            if(auth_group_id == '2'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-firstScore'
                                    }
                                ],
                                events: Controller.api.events.operate,
                                formatter: Table.api.formatter.buttons
                            }                    ]
                    ]
                });
            }
            /**
             * 批量审核
             */
             $(".btn-batchAudit").on("click",function () {
                 var ids = [];
                 $.each(table.bootstrapTable('getSelections'), function (index, row) {
                     ids.push(row.item_id);
                 });
                 Fast.api.ajax({
                     url: "match/matchitemschool/batchaudit",
                     data: {ids:ids}
                 },function(data,ret){
                      Toastr.success(ret.msg);
                     table.bootstrapTable('refresh');
                     return false;
                 },function(data,ret){
                      Toastr.error(ret.msg);
                     table.bootstrapTable('refresh');
                     return false;
                 });
             })
            /**
             * 批量推荐
             */
            $(".btn-batchRecommend").on("click",function () {
                var ids = [];
                $.each(table.bootstrapTable('getSelections'), function (index, row) {
                    ids.push(row.item_id);
                });
                Fast.api.ajax({
                    url: "match/matchitemschool/batchrecommend",
                    data: {ids:ids}
                },function(data,ret){
                    Toastr.success(ret.msg);
                    table.bootstrapTable('refresh');
                    return false;
                },function(data,ret){
                    Toastr.error(ret.msg);
                    table.bootstrapTable('refresh');
                    return false;
                });
            })
            /**
             * 打包项目请求
             */
            $(".btn-package").on("click", function (e) {
                //获取选中id，装进数组传到后台
                var ids = [];
                $.each(table.bootstrapTable('getSelections'), function (index, row) {
                   ids.push(row.item_id);
                });

                //ajax无法输出文件浏览器下载，自定义post请求
                post("../match/matchitemschool/package",{"ids":ids});
            });

            /**
             * 自定义非ajax POST请求
             * @param URL
             * @param PARAMS
             * @returns {Element}
             */
            function post(URL, PARAMS) {
                var temp = document.createElement("form");
                temp.action = URL;
                temp.method = "post";
                temp.style.display = "none";
                for (var x in PARAMS) {
                    var opt = document.createElement("textarea");
                    opt.name = x;
                    opt.value = PARAMS[x];
                    temp.appendChild(opt);
                }
                document.body.appendChild(temp);
                temp.submit();
                return temp;
            }

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
            },
            events:{
                operate:{
                    'click .btn-audit': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var that = this;
                        var top = $(that).offset().top - $(window).scrollTop();
                        var left = $(that).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        var table = $(that).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        Fast.api.open("match/matchitemschool/audit_view?id="+row.item_id+"&type=audit", __('审核'),{
                            callback:function(value){
                                Toastr.success("审核成功");
                                table.bootstrapTable('refresh');
                            }
                        })
                    },
                    'click .btn-recommend': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var that = this;
                        var top = $(that).offset().top - $(window).scrollTop();
                        var left = $(that).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        var table = $(that).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        Fast.api.ajax({
                            url: "match/matchitemschool/recommend",
                            data: {id:row.item_id}
                        },function(data){
                            Toastr.success("推荐成功");
                            table.bootstrapTable('refresh');
                            return false;
                        });
                    },
                    'click .btn-viewDetail': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var that = this;
                        var top = $(that).offset().top - $(window).scrollTop();
                        var left = $(that).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        var table = $(that).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        Fast.api.open("match/matchitemschool/audit_view?id="+row.item_id+"&type=viewDetail", __('查看'))
                    },
                    'click .btn-reBack': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var that = this;
                        var top = $(that).offset().top - $(window).scrollTop();
                        var left = $(that).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        var table = $(that).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        Fast.api.ajax({
                            url: "match/matchitemschool/reBack",
                            data: {id:row.item_id}
                        },function(data){
                            Toastr.success("退回成功");
                            table.bootstrapTable('refresh');
                            return false;
                        });
                    },
                    'click .btn-firstScore': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var that = this;
                        var top = $(that).offset().top - $(window).scrollTop();
                        var left = $(that).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        var table = $(that).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        Fast.api.open("match/matchitemschool/firstView?id="+row.item_id, __('初赛评分'), {
                            callback: function (value) {
                                Toastr.success("评分成功");
                                table.bootstrapTable('refresh');
                            }
                        })
                    }
                }
            }
        }
    };
    return Controller;
});