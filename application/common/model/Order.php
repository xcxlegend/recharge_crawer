<?php


namespace app\common\model;
use think\Model;

class Order Extends Model
{

    const STATUS_PEDDING = 0;
    const STATUS_PAYING  = 1;
    const STATUS_PAYED   = 2;
    const STATUS_FINISH  = 3;


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



}