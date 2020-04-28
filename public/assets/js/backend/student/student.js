define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var authName = Config.authName;
    //审核按钮点击事件
    $("#success").click(function(){
        $auditId = $("#audit_id").val();
        Fast.api.ajax({
            url:'student/student/audit',
            data:{audit_id:$auditId,audit_status:'success'}
        },function (data,ret) {
            Fast.api.close(data);
            return false;
        })
    });

    $("#failed").click(function(){
        $auditId = $("#audit_id").val();
        Fast.api.ajax({
            url:'student/student/audit',
            data:{audit_id:$auditId,audit_status:'failed'}
        },function (data,ret) {
            Fast.api.close(data);
            return false;
        })
    });

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'student/student/index' + location.search,
                    add_url: 'student/student/add',
                    edit_url: 'student/student/edit',
                    del_url: 'student/student/del',
                    multi_url: 'student/student/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            if(authName == '学校管理员'){
                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    searchFormVisible: true,//普通搜索显示
                    pk: 'id',
                    sortName: 'id',
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'),operate:false},
                            {field: 'username', title: __('Username'),operate:'like'},
                            {field: 'nickname', title: __('Sname'),operate:'like'},
                            {field: 'email', title: __('Email'),operate:false},
                            {field: 'mobile', title: __('Mobile'),operate:'like'},
                            {field: 'city', title: __('City'),operate:false},
                            {field: 'gender', title: __('Gender'),operate:false,formatter:function(index,value){
                                if(value.gender == 0){
                                    return "男";
                                }else{
                                    return "女";
                                }
                            }},
                            {field: 'college', title: __('College'),operate:false},
                            {field: 'school_name', title: __('School'),operate:false},
                            {field: 'major', title: __('Major'),operate:false},
                            {field: 'class', title: __('Class'),operate:false},
                            {field: 'createtime', title: __('Createtime'), operate:false, addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                            {field: 'audit_status',operate:false, title: __('AuditStatus'),formatter:function(index,value){
                                if(value.audit_status == '0'){
                                    return "<span>待审核</span>";
                                }else if(value.audit_status == '1'){
                                    return "<span style='color:green'>审核通过</span>";
                                }else{
                                    return "<span style='color:red'>拒绝</span>";
                                }
                            }},
                            {field: 'operate', title: __('Operate'), table: table,
                                buttons: [
                                    {
                                        name: 'audit',
                                        text: __('审核'),
                                        title: __('审核'),
                                        hidden:function(row){
                                            if(row.audit_status != '0'){
                                                return true;
                                            }
                                            if(authName == '平台管理员'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-audit'
                                    },
                                    {
                                        name: 'resetPwd',
                                        text: __('重置密码'),
                                        title: __('重置密码'),
                                        hidden:function(row){
                                            if(authName != '平台管理员' && authName != 'Admin group'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-resetPwd'
                                    }
                                ],
                                events: Controller.api.events.operate,
                                formatter: Table.api.formatter.buttons
                            }
                        ]
                    ]
                });
            }else if (authName == '平台管理员'){
                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    searchFormVisible: true,//普通搜索显示
                    pk: 'id',
                    sortName: 'id',
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'),operate:false},
                            {field: 'username', title: __('Username'),operate:'like'},
                            {field: 'nickname', title: __('Sname'),operate:'like'},
                            {field: 'email', title: __('Email'),operate:false},
                            {field: 'mobile', title: __('Mobile'),operate:'like'},
                            {field: 'city', title: __('City'),operate:false},
                            {field: 'gender', title: __('Gender'),operate:false,formatter:function(index,value){
                                if(value.gender == 0){
                                    return "男";
                                }else{
                                    return "女";
                                }
                            }},
                            //{field: 'college', title: __('College'),operate:false},
                            {field: 'school_name', title: __('School'),operate:false},
                            {field: 'school_id', title: __('School'),visible:false, searchList: $.getJSON("ajax/schoolselect")},
                          //  {field: 'major', title: __('Major'),operate:false},
                            //{field: 'class', title: __('Class'),operate:false},
                            {field: 'createtime', title: __('Createtime'), operate:false, addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                            {field: 'audit_status',operate:false, title: __('AuditStatus'),formatter:function(index,value){
                                if(value.audit_status == '0'){
                                    return "<span>待审核</span>";
                                }else if(value.audit_status == '1'){
                                    return "<span style='color:green'>审核通过</span>";
                                }else{
                                    return "<span style='color:red'>拒绝</span>";
                                }
                            }},
                            {field: 'operate', title: __('Operate'), table: table,
                                buttons: [
                                    {
                                        name: 'audit',
                                        text: __('审核'),
                                        title: __('审核'),
                                        hidden:function(row){
                                            if(row.audit_status != '0'){
                                                return true;
                                            }
                                            if(authName == '平台管理员'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-audit'
                                    },
                                    {
                                        name: 'resetPwd',
                                        text: __('重置密码'),
                                        title: __('重置密码'),
                                        hidden:function(row){
                                            if(authName != '平台管理员' && authName != 'Admin group'){
                                                return true;
                                            }
                                        },
                                        classname: 'btn btn-xs btn-primary btn-resetPwd'
                                    }
                                ],
                                events: Controller.api.events.operate,
                                formatter: Table.api.formatter.buttons
                            }
                        ]
                    ]
                });
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
                Form.api.bindevent($("form[role=form]"))
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
                        Fast.api.open("student/student/audit_view?id="+row.id, __('审核'),{
                            callback:function(value){
                                Toastr.success("审核成功");
                                table.bootstrapTable('refresh');
                            }
                        })
                    },
                    'click .btn-resetPwd': function (e, value, row, index) {
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
                            url: "student/student/resetpwd",
                            type: "post",
                            data:{id:row.id}
                        });
                    }
                }
            }
        }
    };
    return Controller;
});