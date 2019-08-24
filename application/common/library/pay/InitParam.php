<?php


namespace app\common\library\pay;


/**
 * Class InitParam
 * @package app\common\library\pay
 */
class InitParam
{
    /**
     * 网关地址
     * @var
     */
    public $gateway;
    /**
     * 授权ID
     * @var
     */
    public $agentid;
    /**
     * 授权的KEY
     * @var
     */
    public $merchantKey;

    /**
     * InitParam constructor.
     * @param $gateway
     * @param $agentid
     * @param $merchantKey
     */
    public function __construct($gateway, $agentid, $merchantKey)
    {
        $this->gateway = $gateway;
        $this->agentid = $agentid;
        $this->merchantKey = $merchantKey;
    }
}