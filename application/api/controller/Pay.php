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

    protected $account;
    protected $Pay;

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
        $method = $this->request->param('method', 'CST');
        $AccountModel = model('Account');
        if (!$AccountModel){
            echo 'error';
            $this->notifyFinish();
        }
        $account = $AccountModel->where(['number' => $this->request->request('agentid')])->find();
        if (!$account) {
            echo 'no agent';
            $this->notifyFinish();
        }
        $this->account = $account;
        $Pay = Factory::create($method, new InitParam($account));
        if (!$Pay) {
            echo 'error';
            $this->notifyFinish();
        }

        $this->Pay = $Pay;

        $ret = $Pay->checkNotify($this->request->request());

        if (!$ret) {
            echo $Pay->notifyError();
            $this->notifyFinish();
        }
        [$orderid] = $ret;
        $model = model('Order');
        $order = $model->where(['orderid' => $orderid])->find();
        if (!$order) {
            echo $Pay->notifyError();
            $this->notifyFinish();
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
        $this->notifyFinish();
    }


    protected function notifyFinish() {
        if ($this->account && $this->Pay) {
            $account = $this->Pay->queryAccount();
            $agentbalance = $account['agentbalance'] ?? -1;
            if ($agentbalance >= 0) {
                model('Account')->where(['id' => $this->account['id']])->setField('money', $agentbalance);
            }
        }
        exit();
    }

}