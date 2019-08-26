<?php

namespace app\api\controller;

use app\common\controller\Api;

use app\common\model\UserGroup;
use think\Queue;

/**
 * 下游接口 请使用 order表
 * Class Order
 * @package app\api\controller
 */
class Order extends BaseApi
{
    /**
     * 下游请求订单接口
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index() {
        // TODO 下游请求订单接口

        // 下游参数可以参考原话充平台的接口参数.  只是将加密方式改成账号密码 . 因为后台的user管理使用账号密码
        // 相当于是 appkey和appsecret .
        // 签名不用那么麻烦的将所有参数签名.  因为是服务器对服务器的请求.  只加上一个随机值或者时间和账号. 密码 做md5就可以了

        // 1. 参数验证
        // 2. 用户验证/签名验证/状态验证. 后台是否开放等
        // 3. 从上游账号库里查询一个金额足够的账号 获取相关的 参数. 将这些参数传递到4中.
        // 4. 使用 app\common\library\pay\Factory::create('Test', $params) 测试类进行模拟请求订单. 后续code切换到正式即可
        // $params 必带参数  agentid (来源查询出来的账号), merchantKey(来源查询出来的账号), gateway(配置的 便于更改)
        // 5. $pay = Factory::create('Test', $params); 调用 $pay->order();
        // 6. 如果调用成功 将返回order信息 统一的payOrderData 里面有上游的订单号 使用类 便于后期扩展

        // orderid
//        username
//        out_trade_id
//        phone
//        money
//        sign
        if(
            !$this->request->request("username") ||
            !$this->request->request("out_trade_id") ||
            !$this->request->request("phone") ||
            !$this->request->request("money") ||
            !$this->request->request("notify_url") ||
            !$this->request->request("sign")
        )
            {
            return $this->error("参数错误", null, self::ERROR_PARAMS);
        }

        $username = $this->request->request('username');
        $user = model('User')->where(['username' => $username])->find();

        if (!$user) {
            return $this->error("用户不存在", null, self::ERROR_NOUSER);
        }

        // 签名
        $signs = [
            'username'      => $this->request->request('username'),
            'out_trade_id'  => $this->request->request('out_trade_id'),
            'phone'         => $this->request->request('phone'),
            'money'         => $this->request->request('money'),
            'notify_url'    => $this->request->request('notify_url'),
        ];
        $key = json_decode($user['extend'], true)['appkey'] ?? '';
        if (sign($key, $signs) !== $this->request->request('sign')){
            return $this->error("签名错误", null, self::ERROR_SIGN);
        }

        $money = $this->request->request('money');
        if ($money <= 0) {
            return $this->error("金额无效", null, self::ERROR_MONEY);
        }

        // 检查状态
        if ($user['status'] != "normal") {
            return $this->error("用户被禁用", null, self::ERROR_BANUSER);
        }

        // 检查用户组
        $group = model('UserGroup')->find($user['group_id']);
        if (!$group || $group['code'] != UserGroup::CODE_MERCHANT){
            return $this->error('用户组错误', null, self::ERROR_GROUP);
        }

        // 检查订单号存在
        $orderModel = model('Order');
        $out_trade_id = $this->request->request("out_trade_id");
        if ($orderModel->checkOutTradeId($user['id'], $out_trade_id)){
            return $this->error("商户订单号已存在", null, self::ERROR_ORDER_EXIST);
        }

        // 生成订单
        $order = [
            'uid'            => $user['id'],
            'pay_uid'        => 0,
            'orderid'        => create_orderid('MP'),
            'out_trade_id'   => $out_trade_id,
            'trade_id'       => '',
            'phone'          => $this->request->request('phone'),
            'money'          => $money,
            'create_time'    => $this->timestamp,
            'success_time'   => 0,
            'finish_time'    => 0,
            'status'         => 0,
            'notify_url'     => $this->request->request('notify_url'),
        ];
        if (!$orderModel->insert($order)) {
            return $this->error("订单保存失败", null, self::ERROR_ORDER_SAVE);
        }
        $order['id'] = $orderModel->getLastInsID();

        // 将订单推到queue里面等待处理

        $userExtend = json_decode($user['extend'] ?? '', true);
        $payCode = $userExtend['pay_code'] ?? 'Test';

        Queue::push('app\common\job\PayOrder', ['order' => $order, 'pay_code' => $payCode], 'PayOrder');
        return $this->success("请求成功", [
            'orderid'       => $order['orderid'],
            'out_trade_id'  => $order['out_trade_id'],
        ], 1);
    }


    /**
     * 下游查询订单接口
     */
    public function query() {

    }

}