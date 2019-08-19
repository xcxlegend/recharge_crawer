<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\pay\CSTPay;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        $this->redirect("index/user/index");
        return;
        return $this->view->fetch();
    }

    public function demo()
    {
        $gateway = 'http://www.18381789999.com:8107';
        $agentid = '11608';
        $merchantKey = '36fd22e8ffce69d5750dc8b8288c6a3a';
        $pay = new CSTPay(compact('agentid','merchantKey', 'gateway'));
        $data = $pay->queryAccount();
        dump($data);
    }

}
