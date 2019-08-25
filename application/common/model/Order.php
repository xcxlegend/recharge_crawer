<?php


namespace app\common\model;
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


}