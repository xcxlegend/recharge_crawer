<?php

namespace app\common\library\pay;

abstract class IPay
{
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    abstract public function order();       // 订单
    abstract public function queryOrder();  // 订单查询
    abstract public function checkNotify(); // 接受回调参数检查
    abstract public function notifySucess(); // 回调成功的回复
    abstract public function queryAccount(); // 回调成功的回复
}