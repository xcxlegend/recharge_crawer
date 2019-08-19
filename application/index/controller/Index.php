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
        $agentid = '11610';
        $merchantKey = 'cf0328584acf1ee2fe4f2e20547bd48a';
        $pay = new CSTPay(compact('agentid','merchantKey', 'gateway'));
        $data = $pay->queryAccount();
        dump($data);
    }

}
