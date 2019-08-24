<?php


namespace app\api\controller;
use app\common\controller\Api;

/**
 * 上游接口
 * Class Pay
 * @package app\api\controller
 */
class Pay extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 商户回调接口.
     *
     */
    public function notify() {
        echo 'T';
    }

}