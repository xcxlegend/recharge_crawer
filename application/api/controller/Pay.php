<?php


namespace app\api\controller;
use app\common\controller\Api;
use app\common\library\pay\Factory;
use app\common\library\pay\InitParam;
use app\common\model\Order;

/**
 * 上游接口
 * Class Pay
 * @package app\api\controller
 */
class Pay extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 商户回调接口.
     *
     */
    public function notify() {
        /*dump($this->request->request());
        dump($this->request->param());*/

        /**
         *  agentid
            orderno
            orderstatus
            verifystring
         */
        $method = $this->request->param('method');
        $AccountModel = model('Account');
        if (!$AccountModel){
            exit('error');
        }
        $account = $AccountModel->where(['number' => $this->request->request('agentid')])->find();
        if (!$account) {
            exit('no agent');
        }
        $Pay = Factory::create($method, new InitParam($account));
        if (!$Pay) {
            exit('error');
        }

        $ret = $Pay->checkNotify($this->request->request());

        if (!$ret) {
            echo $Pay->notifyError();
            exit();
        }
        [$orderid] = $ret;
        $model = model('Order');
        $order = $model->where(['orderid' => $orderid])->find();
        if (!$order) {
            echo $Pay->notifyError();
            exit;
        }
        $order = $order->toArray();
        switch ($order['status']) {
            case Order::STATUS_PAYING:
                $model->setOrderPayed($order);
                Order::pubNotify($order, 1);
            case Order::STATUS_PAYED:
            case Order::STATUS_FINISH:
                echo $Pay->notifySucess();
                break;
            default:
                echo $Pay->notifyError();
        }
        exit;
    }

}