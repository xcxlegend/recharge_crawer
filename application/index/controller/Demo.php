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
        ];
        $data['sign'] = sign("", $data);
        $client = new Client();
        $res = $client->post('http://crawer.in:8003/api/order', ['query' => $data])
            ->getBody()->getContents();
        dump($res);
    }





}