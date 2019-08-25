<?php


namespace app\common\job;


use app\common\library\pay\Factory;
use app\common\library\pay\InitParam;
use app\common\model\Order;
use think\Queue;
use think\queue\Job;

/**
 * 处理订单保存后, 去请求平台支付的异步处理
 * Class PayOrder
 * @package app\common\job
 */
class PayOrder
{

    /**
     * @param Job $job
     * @param $data
     */
    public function fire(Job $job, $data) {
        ['order' => $order, 'pay_code' => $payCode] = $data;
        // $data -> order
        // 如果支付失败
        if (!$this->pay($data)) {
            if ($job->attempts() >= 1) {
                $job->delete();
                $this->notify($order, Order::STATUS_TIMEOUT);
                return;
            } else {
                $job->release(60);
            }
        } else { // 支付成功
            return; // 等待支付回调
        }
    }


    /**
     * 调用支付接口
     * 支付成功返回true 并且修改订单信息
     * @param $order
     * @return bool
     */
    protected function pay($data): bool{
        ['order' => $order, 'pay_code' => $payCode] = $data;
        // 从库中选择一个账号
        $account = model('Account', 'common\model')->findValidAccount($order['money']);
        if ($account == null) { return false;}

        $Pay = Factory::create($payCode, new InitParam($account));
        if (!$Pay) {
            return false;
        }

        if ($Pay->order($order)){
            $order['pay_uid'] = $account['id'];
            model('Order', 'common\model')->setOrderPaying($order);
            return true;
        }
        return false;
    }

    protected function notify($order, $status) {
        Order::pubNotify($order, $status);
    }

}