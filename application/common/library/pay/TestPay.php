<?php


namespace app\common\library\pay;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;
use think\Log;

class TestPay extends IPay
{

    // 返回一个订单号
    public function order(array &$order): bool
    {
        $trade_id = create_orderid('T');
        $order['trade_id'] = $trade_id;
        return true;
    }

    public function notifyError()
    {
        return 'F';
    }

    public function queryOrder(array &$order): bool
    {
        // TODO: Implement queryOrder() method.
    }

    public function checkNotify($request): ?array
    {
        return [$request['orderno']];
    }

    public function notifySucess()
    {
        return 'T';
    }

    public function queryAccount()
    {


    }


    // 因为是get请求. 所以直接请求
    protected function request($api, array $query): ?array {

    }

}