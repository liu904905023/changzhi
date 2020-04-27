define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var loginName = Config.loginName;
    var scoreTotal = Config.totalSocre;

    /**
     * 计算总分
     */
    $("input[name='score']").keyup(function(){
        //获取单项总分
        var totalSocreOne = $(this).attr("data-totalScore");
        //获取单项评分
        var scoreOne = $(this).val();

        //限定只允许输入小数或数字
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
        var score = [];
        $("input[name='score']").each(function(k,v){
            score.push($(this).val());
        });
        var totalScore = 0;
        for(var i = 0;i < score.length;i++){
            var s = 0;
            //判断 int 还是float
            if(score[i] % 1 === 0){
                //int
                s = parseFloat(score[i]);
            } else{
                // float 则保留两位小数
                s = parseFloat(score[i].substring(0, score[i].indexOf(".") + 3));
            }
            totalScore += isNaN(s) ? 0 : s;
        }
        //判断评分总分是否超过100 若评分项大于单项总分 提示错误
        if(eval(totalScore) > 100 || eval(scoreOne) > eval(totalSocreOne)){
            $("#total_score").html("总分：" + totalScore + "分");
            $("#total_score").css("color","red");
            $(this).css("border","1px solid red");
            $("#scoringSuccess").attr("disabled",true);
        }else{
            $("#total_score").html("总分：" + totalScore + "分");
            $("#total_score").css("color","#000");
            $(this).css("border","1px solid #ccc");
            $("#scoringSuccess").attr("disabled",false);
        }

    });

    /**
     * 评分按钮点击
     */
    $("#scoringSuccess").click(function(){
        //验证是否全部填写评分
        var scoreIsNull = false;

        //实际得分
        var score = [];
        $("input[name='score']").each(function(){
            if($(this).val() == '' || $(this).val() == null){
                scoreIsNull = true;
            }
            score.push($(this).val());
        });
        //本项满分
        var total_score = [];
        $("input[name='total_score']").each(function(){
            total_score.push($(this).val());
        });
        //评分项id
        var rule_item_id = [];
        $("input[name='rule_item_id']").each(function(){
            rule_item_id.push($(this).val());
        });
        //评分项名称
        var rule_item_name = [];
        $("input[name='rule_item_name']").each(function(){
            rule_item_name.push($(this).val());
        });
        //评分规则id
        var ruleId = $("input[name='rule_id']").val();
        //项目id
        var itemId = $("#item_id").val();
        //评语
        var comment = $("#comment").val();
        //状态（新增还是修改）
        var isType = '';

        if(scoreTotal == 0){
            isType = 'insert';
        }else{
            isType = 'update';
        }

        //验证评分是否全部填写
        if(!scoreIsNull){
            Fast.api.ajax({
                url:'match/matchitemexpert/scoring',
                data:{score:score,ruleId:ruleId,itemId:itemId,rule_item_id:rule_item_id,rule_item_name:rule_item_name,totalScore:total_score,comment:comment,isType:isType}
            },function (data,ret) {
                Fast.api.close(data);
                return false;
            });
        }else{
            alert("评分不能为空");
            return false;
        }

    });

    //关闭查看页面
    $("#closeButton").click(function (data) {
        var index = parent.Layer.getFrameIndex(window.name);
        parent.Layer.close(index);
    });

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'match/matchitemexpert/index' + location.search,
                    add_url: 'match/matchitemexpert/add',
                    edit_url: 'match/matchitemexpert/edit',
                    del_url: 'match/matchitemexpert/del',
                    multi_url: 'match/matchitemexpert/multi',
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
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'item_id', title: __('Id'),operate:false},
                            {field: 'group_name', title: __('Group_id'),operate:false},
                            {field: 'track_name', title: __('Track_id'),operate:false},
                            {field: 'item_name', title: __('Item_name'),operate:'like'},
                            {field: 'school_name', title: __('School_name'),operate:false},
                            {field: 'ms.school_id', title: __('School_name'), visible:false, searchList: $.getJSON("ajax/schoolselect")},
                            {field: 'audit_status', title: __('Audit_status'), searchList: {"草稿":__('草稿'),"待审核":__('待审核'),"审核通过":__('审核通过'),"审核未通过":__('审核未通过'),"归档":__('归档')}},
                            {field: 's.is_score', title: __('Score_count'),searchList:{"N":'未评分',"Y":'已评分'},formatter:function(index,value){
                                if(value.is_score == 'Y'){
                                    return "<span style='color:green;'>已评分</span>";
                                }else if(value.is_score == 'N'){
                                    return "<span style='color:red;'>未评分</span>";
                                }
                            }},
                            {field: 'score', title: __('TotalScore'),operate:false},
                            {field: 'nickname', title: __('User_id'),operate:false},
                            {field: 'operate', title: __('Operate'), table: table,
                                buttons: [
                                    {
                                        name: 'viewDetail',
                                        text: __('查看'),
                                        title: __('查看'),
                                        classname: 'btn btn-xs btn-primary btn-viewDetail'
                                    },
                                    {
                                        name: 'scoring',
                                        text: __('评分'),
                                        title: __('评分'),
                                        hidden:function(row){
                                            if(loginName != '专家评委'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-scoring'
                                    }
                                ],
                                events: Controller.api.events.operate,
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
                        Fast.api.open("match/matchitemexpert/audit_view?id="+row.item_id+"&type=viewDetail", __('查看'))
                    },
                    'click .btn-scoring': function (e, value, row, index) {
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
                        Fast.api.open("match/matchitemexpert/scoring_view?id="+row.item_id, __('评分'),{
                            callback:function(value){
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