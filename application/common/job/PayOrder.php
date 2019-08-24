<?php


namespace app\common\job;


use app\common\library\pay\Factory;
use app\common\library\pay\InitParam;
use app\common\model\Order;
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
        // 如果支付失败
        if (!$this->pay($data)) {
            if ($job->attempts() == 1) {
                $job->delete();
                // DELETE ORDER
                // NOTIFY TIMEOUT
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
    protected function pay($order): bool{

        // 从库中选择一个账号
        $account = model('Account', 'common\model')->findValidAccount($order['money']);
        if ($account == null) { return false;}
        if (Factory::create('Test', new InitParam('', $account['number'], $account['appkey']))->order($order)){
            $order['pay_time'] = time();
            $order['status'] = Order::STATUS_PAYING;
            model('Order', 'common\model')->where(['id' => $order['id']])->update($order);
            return true;
        }
        return false;
    }

}