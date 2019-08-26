<?php


namespace app\index\controller;


use app\common\controller\Frontend;
use GuzzleHttp\Client;

class Demo extends Frontend
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $layout = 'default';

    public function order() {
        if (!$this->request->isAjax()){
            return $this->view->fetch();
        }
        $phone = $this->request->request('phone');
        if (!$phone) {
            return $this->error('请输入号码');
        }
        $data = [
            "username"      => "admin",
            "out_trade_id"  => create_orderid('P'),
            "phone"         => $phone,
            "money"         => 1,
            'notify_url'    => config("site.domain") . '/index/demo/notify',
        ];

        $user = model('user')->where(['username' => 'admin'])->find();

        $extend = json_decode($user['extend'] ?? '', true);
        $key = $extend['appkey'] ?? '';

//        return $this->success('ok', '', $data);
        $data['sign'] = sign($key, $data);
        $client = new Client();
        $res = $client->post('http://crawer.in:8003/api/order', ['query' => $data])
            ->getBody()->getContents();
//        dump($res);
        $this->success('成功','', $res);
    }

    public function notify() {
        exit('ok');
    }





}