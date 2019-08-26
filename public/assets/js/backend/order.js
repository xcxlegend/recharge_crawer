define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'order/index',
                    table: 'order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                search: true,
                commonSearch: true,
                sortName: 'id',
                searchFormVisible: true,
                queryParams: function (params) {
                    var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    return params;
                },
                columns: [
                    [
                        {field: 'id',width: "60px", title: __('Id'), sortable: true},
                        {field: 'uid', width: "90px",title: '商户ID',sortable: true},
                        {field: 'pay_uid', width: "120px",title: '上游账号ID',sortable: true},
                        {field: 'orderid', width: "150px",title: '平台订单号'},
                        {field: 'trade_id',width: "150px", title: '上游订单号'},
                        {field: 'out_trade_id',width: "150px", title: '下游订单号'},
                        {field: 'phone', width: "100px",title: '手机号', operate: false},
                        {field: 'money', title: '金额', operate: false},
                        {
                            field: 'status',
                            title: '订单状态', 
                            sortable: true,
                            formatter: Controller.api.formatter.status,
                            searchList: [{"id":"0","name":"未完成"},{"id":"1","name":"订单支付中"},{"id":"2","name":"支付成功"},{"id":"3","name":"回调完成"}],
                        },
                        {
                            field: 'create_time',
                            width: "150px",
                            title: '创建时间',
                            sortable: true, 
                            formatter: Table.api.formatter.datetime, 
                            operate: 'RANGE',
                            addclass: 'datetimerange'
                        },
                        {field: 'pay_time', width: "150px",title: '支付时间', sortable: true, formatter: Table.api.formatter.datetime,operate: false},
                        {field: 'success_time', width: "150px",title: '成功时间', sortable: true,formatter: Table.api.formatter.datetime, operate: false},
                        {field: 'finish_time', width: "150px", title: '完成时间', sortable: true, formatter: Table.api.formatter.datetime,operate: false},
                        {field: 'notify_url', width: "200px",title: '回调地址', formatter: Controller.api.formatter.url,operate: false},
                        {
                            field: 'operate',
                            width: "80px", 
                            title: __('Operate'),
                            table: table, 
                            events: Table.api.events.operate, 
                            buttons: [
                                {
                                    name: 'click',
                                    title: __('查单'),
                                    classname: 'btn btn-xs btn-info btn-click',
                                    icon: 'fa fa-arrow-circle-o-up',
                                    click: function (data,row,index) {
                                        Layer.confirm("确定手动查单吗？", {type: 5, skin: 'layui-layer-dialog layui-layer-fast'}, function (value,index) {
                                            Fast.api.ajax({
                                                url: "order/find",
                                                data: { id: row.id}
                                            }, function (data) {
                                                Layer.closeAll();
                                                
                                            });
                                        });
                                    }
                                },
                                {
                                    name: 'click',
                                    title: __('手动回调'),
                                    classname: 'btn btn-xs btn-info btn-click',
                                    icon: 'fa fa-anchor',
                                    click: function (data,row,index) {
                                        Layer.confirm("确定回调吗？", {type: 5, skin: 'layui-layer-dialog layui-layer-fast'}, function (value,index) {
                                            Fast.api.ajax({
                                                url: "order/callback",
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
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {//渲染的方法
                url: function (value, row, index) {
                    return '<div class="input-group input-group-sm" style="width:250px;"><input type="text" class="form-control input-sm" value="' + value + '"><span class="input-group-btn input-group-sm"><a href="' + value + '" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a></span></div>';
                },
                status: function (value, row, index) {
                    var statusjson = {"0":"未完成","1":"订单支付中","2":"支付成功","3":"回调完成"};
                    return statusjson[value];
                },
                
            },
        }
    };
    return Controller;
});