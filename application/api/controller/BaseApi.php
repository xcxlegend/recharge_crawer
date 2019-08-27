<?php


namespace app\api\controller;

use app\common\controller\Api;

class BaseApi extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    const ERROR_PARAMS = "-1";
    const ERROR_SIGN   = "-2";
    const ERROR_NOUSER = "-3";
    const ERROR_BANUSER = "-4";
    const ERROR_GROUP   = "-5";
    const ERROR_ORDER_EXIST   = "-6";
    const ERROR_MONEY   = "-7";
    const ERROR_ORDER_SAVE   = "-8";
    const ERROR_NO_ORDER    = "-9";
    const ERROR_ORDER_NOPAY = "-10";



    protected $timestamp;

    protected function _initialize() {
        parent::_initialize();
        $this->timestamp = time();
    }

    protected function response($data) {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }


}