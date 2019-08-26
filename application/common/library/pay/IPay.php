<?php

namespace app\common\library\pay;

abstract class IPay
{
    protected $params;

    public function __construct(InitParam $params)
    {
        $this->params = $params;
    }

    /**
     * 发起支付  如果成功则返回true 并且给order里面的部分数据赋值
     * @param array &$order
     * @return bool
     */
    abstract public function order(array &$order): bool;       // 订单
    abstract public function queryOrder(array &$order): bool;  // 订单查询
    abstract public function checkNotify($request): ?array; // 接受回调参数检查
    abstract public function notifySucess(); // 回调成功的回复
    abstract public function notifyError(); // 回调成功的回复
    abstract public function queryAccount(); // 回调成功的回复
}