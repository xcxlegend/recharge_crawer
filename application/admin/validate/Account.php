<?php

namespace app\admin\validate;

use think\Validate;

class Account extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'username' => 'require|max:50|unique:account,username',
        'name' => 'require',
        'password' => 'require',
        'number'    => 'require|max:50|unique:account,number',
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['username', 'name', 'number', 'password'],
        'edit' => ['username', 'name', 'number'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'username' => '用户名',
            'name' => '商户名称',
            'password' => '登录密码',
            'pay_password' => '支付密码',
            'number'    => '商户编号',
        ];
        parent::__construct($rules, $message, $field);
    }
    
}
