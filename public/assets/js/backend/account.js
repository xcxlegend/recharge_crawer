define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'account/index',
                    add_url: 'account/add',
                    edit_url: 'account/edit',
                    del_url: 'account/del',
                    multi_url: 'account/multi',
                    table: 'account',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                //searchFormVisible: true,
                //searchFormTemplate: 'searchtpl',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true},
                        {field: 'number', title: '商户编号'},
                        {field: 'username', title: '用户名'},
                        {field: 'name', title: '商户名称'},
                        {field: 'money', title: '余额'},
                        {field: 'lock_money', title: '冻结金额', operate: false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, 
                        buttons: [
                            {
                                name: 'click',
                                title: __('刷新余额'),
                                classname: 'btn btn-xs btn-info btn-click',
                                icon: 'fa fa-refresh',
                                click: function (data,row,index) {
                                    Layer.confirm("确定刷新余额吗？", {type: 5, skin: 'layui-layer-dialog layui-layer-fast'}, function (value,index) {
                                        Fast.api.ajax({
                                            url: "account/resetmoney",
                                            data: { id: row.id}
                                        }, function (data) {
                                            Layer.closeAll();
                                            
                                        });
                                    });
                                }
                            },
                        ],
                        formatter: Table.api.formatter.operate}
                    ]
                ],
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            $(document).on("click","#btn-resetmoney", function (e) {
                Layer.confirm("确定刷新余额吗？", {type: 5, skin: 'layui-layer-dialog layui-layer-fast'}, function (value,index) {
                    Fast.api.ajax({
                        url: "account/resetmoney"
                    }, function (data) {
                        Layer.closeAll();
                    });
                });
            });
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