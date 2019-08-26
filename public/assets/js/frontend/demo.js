define(['jquery', 'bootstrap', 'frontend', 'form', 'template'], function ($, undefined, Frontend, Form, Template) {
    var validatoroptions = {
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {
        order: function () {
            //本地验证未通过时提示
            $("#order-form").data("validator-options", validatoroptions);
            Form.api.bindevent($("#order-form"), function (data, ret) {
                // Layer.msg(ret.msg);
                Layer.open({
                    type: 1,
                    title: __('订单信息'),
                    area: ["450px", "355px"],
                    content: JSON.stringify(ret.data, "", " ")
                });
            });
        }
    };
    return Controller;
});