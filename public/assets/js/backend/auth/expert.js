define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    //专家ids
    var projectIds = Config.projectIds;
    //比赛阶段
    var stage = Config.stage;

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'auth/expert/index',
                    add_url: 'auth/expert/add',
                    edit_url: 'auth/expert/edit',
                    del_url: 'auth/expert/del',
                    multi_url: 'auth/expert/multi',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                showExport: false,
                operate: false,
                showToggle: false,
                showColumns: false,
                commonSearch: false,
                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID'},
                        {field: 'nickname', title: __('Nickname')},
                        {field: 'email', title: __('Email')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'technical_title', title: __('Technical_title')}
                    ]
                ]
            });

            /**
             * 分配专家
             */
            $(".btn-distribute").on("click", function (e) {
                //获取选中专家id
                var expertIds = Table.api.selectedids(table);

                var data = [];
                //判断是否勾选重置已评分按钮
                if ($("#reset_score").prop("checked")) {
                    var reset_score = $("#reset_score").val();
                    data = {"expertIds":expertIds,"projectIds":projectIds,"stage":stage,"resetScore":reset_score};
                }else{
                    data = {"expertIds":expertIds,"projectIds":projectIds,"stage":stage};
                }

                Fast.api.ajax({
                    url:'match/matchitem/distribute',
                    type:'post',
                    data:data
                },function (data,ret) {
                    parent.Toastr.success("分配成功");
                    Fast.api.close(data);
                });
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});