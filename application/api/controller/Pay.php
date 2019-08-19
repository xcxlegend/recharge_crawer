<?php


namespace app\api\controller;
use app\common\controller\Api;

class Pay extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    public function notify() {
        echo 'T';
    }

}