<?php


namespace app\index\controller;


use GuzzleHttp\Client;

class Demo
{

    public function order() {
        $data = [
            "username"      => "admin",
            "out_trade_id"  => create_orderid('P'),
            "phone"         => "15520620207",
            "money"         => 1,
            'notify_url'    => 'http://crawer.in:8003/index/demo/notify',
        ];
        $data['sign'] = sign("dBsyaGphkbAMwnmjEVc23qeiv49z1NOt", $data);
        $client = new Client();
        $res = $client->post('http://crawer.in:8003/api/order', ['query' => $data])
            ->getBody()->getContents();
        dump($res);
    }

    public function notify() {
        exit('ok');
    }





}