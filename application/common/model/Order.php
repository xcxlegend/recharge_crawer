<?php

namespace app\common\model;
use app\common\library\pay\Factory;
use app\common\library\pay\InitParam;
use think\Model;
use think\Queue;

class Order Extends Model
{

    const STATUS_PEDDING = 0;
    const STATUS_PAYING  = 1;
    const STATUS_PAYED   = 2;
    const STATUS_FINISH  = 3;
    const STATUS_TIMEOUT  = -1;


    /**
     * 检查订单号是否存在 false表示不存在
     * @param $uid
     * @param $out_trade_id
     * @return bool
     * @throws \think\Exception
     */
    public function checkOutTradeId($uid, $out_trade_id): bool{
        return $this->where([
            'uid' => $uid,
            'out_trade_id' => $out_trade_id,
            ])->count() > 0;
    }


    public function setOrderPaying($order) {
        $update['status']   = self::STATUS_PAYING;
        $update['pay_time'] = time();
        $update['trade_id'] = $order['trade_id'];
        $update['pay_uid']  = $order['pay_uid'];
        $this->where(['id' => $order['id']])->setField($update);
    }

    public function setOrderPayed($order) {
        $update['status'] = self::STATUS_PAYED;
        $update['success_time'] = time();
        $this->where(['id' => $order['id']])->setField($update);
    }

    public function setOrderFinish($order) {
        $update['status'] = self::STATUS_FINISH;
        $update['finish_time'] = time();
        $this->where(['id' => $order['id']])->setField($update);
    }

    public function setOrderTimeout($order) {
        $update['status'] = self::STATUS_TIMEOUT;
        $this->where(['id' => $order['id']])->setField($update);
    }

    /**
     * 补回调
     * @param $order
     * @param $status
     */
    static public function pubNotify($order, $status) {
        $query = [
            'out_trade_id' => $order['out_trade_id'],
            'phone'        => $order['phone'],
            'money'        => $order['money'],
            'status'       => $status
        ];

        $callback = $status == -1 ? 'timeout' : 'finish';

        $notify = [
            'order'     => $order,
            'url'       => $order['notify_url'],
            'query'     => $query,
            'callback'  => $callback,
        ];
        Queue::push('app\common\job\Notify', $notify, 'notify');
    }


    /**
     * 查单
     * @param $order
     * @return string  包含错误信息 如果成功则为空
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function queryOrder(&$order): string {

        $pay_uid = $order['pay_uid'];
        $account = model('Account')->find($pay_uid);
        if (!$account) {
            return '平台账号错误';
        }
        $user = model('user')->find($order['uid']);
        if (!$user) {
            return '下游账号错误';
        }
        $userExtend = json_decode($user['extend'] ?? '', true);
        $code = $userExtend['pay_code'] ?? 'CST';

        $Pay = Factory::create($code, new InitParam($account));
        if ($Pay->queryOrder($order)) {
            model('order')->setOrderPayed($order);
            self::pubNotify($order, 1);
            return "";
        }else {
            return '订单未支付';
        }
    }


}