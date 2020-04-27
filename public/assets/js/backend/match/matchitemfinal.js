define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var loginName = Config.loginName;

    //关闭查看页面
    $("#closeButton").click(function (data) {
        Fast.api.close(data);
    });

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchitemfinal/index' + location.search,
                    add_url: 'match/matchitemfinal/add',
                    edit_url: 'match/matchitemfinal/edit',
                    del_url: 'match/matchitemfinal/del',
                    multi_url: 'match/matchitemfinal/multi',
                    table: 'match_item',
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
                columns: [
                    [
                        {checkbox: true},
                        {field: 'item_id', title: __('Id'),operate:false},
                        // {field: 'group_name', title: __('Group_id'),operate:false},
                        {field: 'track_name', title: __('Track_id'),operate:false},
                        {field: 'item_name', title: __('Item_name'),operate:'like'},
                        {field: 'school_name', title: __('School_name'),operate:false},
                        {field: 'ms.school_id', title: __('School_name'), visible:false, searchList: $.getJSON("ajax/schoolselect")},
                        {field: 'trackgroup', visible:false, title: __('Track_group'), searchList: function (column) {
                            return Template('sourcetpl', {});
                        }},
                        {field: 'audit_status', title: __('Audit_status'), searchList: {"草稿":__('草稿'),"待审核":__('待审核'),"审核通过":__('审核通过'),"审核未通过":__('审核未通过'),"归档":__('归档')}},
                        {field: 'expertCount', title: __('Is_distribute'),operate:false,formatter:function(index,value){
                            if(value.expertCount != 0){
                                return "<span style='color: green'>已分配</span>";
                            }else{
                                return "<span style='color: red'>未分配</span>";
                            }
                        }},
                        {field: 's.is_score', title: __('Score_count'),searchList:{'N':'未评分','Y':'已评分'},formatter:function(index,value){
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
                        {field: 'final_score', title: __('Final_match_score'),operate:false,formatter:function(index,value){
                            if(value.expertCount != 0 && value.scoreCount == value.expertCount){
                                return value.final_score;
                            }
                        }},

                        {field: 'final_score', title: __('评分进度'),operate:false,formatter:function(index,value){
                                var html = '';
                                var proBar = (value.scoreCount/value.expertCount)*100;
                                html = '<div class="progress">\n' +
                                    '    <div class="progress-bar progress-bar-info" role="progressbar"' +
                                    '        aria-valuemin="0" aria-valuemax="100" style="width: '+proBar+'%;">\n' +
                                    //'        <span class="sr-only">40% 完成</span>\n' +
                                    '<span style="color: black">'+value.scoreCount + '/'+value.expertCount+'</span>'+
                                    '    </div>\n' +
                                    '</div>';
                                return html;
                            }},
                        {field: 'nickname', title: __('User_id'),operate:false},
                        {field: 'mobile', title: __('Mobile'),operate:false},
                        {field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'viewDetail',
                                    text: __('查看'),
                                    title: __('查看'),
                                    classname: 'btn btn-xs btn-primary btn-viewDetail'
                                },
                                {
                                    name: 'viewExpert',
                                    text: __('已分配的专家'),
                                    title: __('已分配的专家'),
                                    hidden:function(row){
                                        if(row.expertCount == 0 || loginName == '专家评委'){
                                            return true;
                                        }
                                    },
                                    classname: 'btn btn-xs btn-primary btn-viewExpert'
                                },
                                {
                                    name: 'scoreRecord',
                                    text: __('评分记录'),
                                    title: __('评分记录'),
                                    hidden:function(row){
                                        if(row.scoreCount != row.expertCount){
                                            return true;
                                        }
                                    },
                                    classname: 'btn btn-xs btn-primary btn-scoreRecord'
                                }
                            ],
                            events: Controller.api.events.operate,
                            formatter: Table.api.formatter.buttons
                        }                    ]
                ]
            });

            /**
             * 总决赛分配专家列表
             */
            $(".btn-distribute_list").on("click", function (e) {
                //获取选中id
                var ids = Table.api.selectedids(table);
                Fast.api.open("auth/expert/index?projectIds=" + ids + "&stage=总决赛", __('分配专家'),{
                    callback:function(value){
                        table.bootstrapTable('refresh');
                    }
                });
            });

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
                post("../match/matchitem/package",{"ids":ids});
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
                        Fast.api.open("match/matchitemfinal/audit_view?id="+row.item_id+"&type=viewDetail", __('查看'))
                    },
                    'click .btn-viewExpert': function (e, value, row, index) {
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
                        Fast.api.open("match/matchitemfinal/viewDistributeExpert?id="+row.item_id, __('已分配的专家'))
                    },
                    'click .btn-scoreRecord': function (e, value, row, index) {
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
                        Fast.api.open("match/matchitemfinal/scoreRecord?id="+row.item_id, __('评分记录'))
                    }

                }
            }
        }
    };
    return Controller;
});