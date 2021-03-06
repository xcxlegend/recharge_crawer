<?php


namespace app\common\library\pay;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;
use think\Log;

/**
 * Class CSTPay
 * @package app\common\library\pay
 */
class CSTPay extends IPay
{
    /**
     *
     */
    const API_QUERYACCOUNT = '/MainServiceBusiness/GetAgentInfo';
    /**
     *
     */
    const API_PHONE_ORDER = '/MainServiceBusiness/SendPhoneChargeInfo';

    const API_QUERY_ORDER = '/MainServiceBusiness/GetOrderInfo';

    /**
     *
     */
    const XML_KEY_PREFIX = '{http://schemas.datacontract.org/2004/07/KR.NetDistribute.Models}';

    /**
     *
     */
    const GATEWAY = 'http://www.18381789999.com:8107';

    static protected $api_xml_ns = [
        self::API_PHONE_ORDER => 'SendChargeInfoReturn',
        self::API_QUERYACCOUNT => 'AgentInfoReturn',
        self::API_QUERY_ORDER => 'GetOrderInfoReturn',
    ];


    /**
     *
    0000	下单成功
    0001	支付失败；下单失败；未扣款；重新提交订单；支付失败；冲正提交失败
    0002	传入参数不完整
    0003	验证摘要串验证失败
    0004	充值号码格式错误
    0005	代理商验证失败
    0006	代理商未激活
    0007	没有对应充值产品
    0008	系统异常，请稍后重试
    0009	账户余额不足
    0010	没有对应订单(不能作为充值失败的依据)
    0011	定单号不允许重复
    0012	IP地址不符合要求
    0013	运营商系统升级，暂不能充值
    0014	充值成功
    0015	充值失败
    0016	正在处理
    1000	查询用户信息成功
    1001	查询用户信息失败
    0119	对应订单已无法冲正
    0111	号码面值对应的订单不存在
    0024	冲正成功
    0025	冲正失败
    0026	正在冲正中

     */


    public function order(array &$order): bool
    {
        /**
         * chargenumbertype	号码类型	3	非空	手机号码为1
        固话号码为2
        agentid	代理商id	200	非空	代理商在代理平台的商家号(平台安全管理，基础资料修改里）
        returntype	返回类型	1	非空	1表示get返回 2表示返回XML信息。目前只支持xml方式返回。
        orderid	代理商订单号	30	非空	该订单号由代理商系统生成。orderid唯一确定一条订单。
        chargenumber	充值号码	20	非空	充值号码
        amountmoney	充值面值		非空	充值面值
        num	数量	4	可空	充值数量（目前支持1，支持倍数待通知）
        ispname	运营商	20	可空	固话时需要输入
        移动、联通、电信
        传汉字，用utf-8编码，进行MD5加密时不用编码，手机号可空。
        source	订单来源	10	非空	代理商请填写2，此处务必填写2，否则可能造成提交订单号重复。
        verifystring	验证摘要串	100	非空	详见接后描述

         */
        $params = [
            'chargenumbertype' => 1,
            'agentid'          => $this->params->agentid,
            'returntype'       => 2,
            'orderid'          => $order['orderid'],
            'chargenumber'     => $order['phone'],
            'amountmoney'      => $order['money'],
            'num'              => 1,
            'ispname'          => '',
            'source'           => 2,
        ];

        $sign_params = [
            'chargenumbertype',
            'agentid',
            'returntype',
            'orderid',
            'chargenumber',
            'amountmoney',
            'ispname',
            'source',
        ];
        $md5str = '';
        foreach ($sign_params as $sign_param) {
            $md5str .= "{$sign_param}={$params[$sign_param]}&";
        }

        $md5str .= 'merchantKey='.$this->params->merchantKey;
        $params['verifystring'] = md5($md5str);

        $response = $this->request(self::API_PHONE_ORDER, $params);
        if (($response['resultno'] ?? '-1') != '0000') {
            return false;
        }

        $order['trade_id'] = $response['orderno'];
        return true;
    }

    /**
     * @return string
     */
    public function notifyError()
    {
        return 'F';
    }

    /**
     *
     */
    public function queryOrder(array &$order): bool
    {
        /**
         *
        agentid	代理商id
        returntype	返回类型
        orderid	代理商订单号
        verifystring	验证摘要串
        // agentid=%s&returntype=%s&orderid=%s&merchantKey=%s
         */
        $orderid = $order['orderid'];

        $data = [
            'agentid'       => $this->params->agentid,
            'returntype'    => 2,
            'orderid'       => $orderid,
        ];
        $data['verifystring'] = md5("agentid={$this->params->agentid}&returntype=2&orderid={$orderid}&merchantKey={$this->params->merchantKey}");
        $response = $this->request(self::API_QUERY_ORDER, $data);
        if (($response['resultno'] ?? '-1') != '0014') {
            return false;
        }
        return true;
    }

    /**
     * @param $request
     * @return array|null
     */
    public function checkNotify($request): ?array
    {
        /**
         *  agentid
            orderno
            orderstatus
            verifystring
         * agentid=%s&orderno=%s&orderstatus=%s&merchantKey=%s
         */

        $orderno = $request['orderno'] ?? '';
        $orderstatus = $request['orderstatus'] ?? '';
        $verifystring = $request['verifystring'] ?? '';

        $sign = md5("agentid={$this->params->agentid}&orderno={$orderno}&orderstatus={$orderstatus}&merchantKey={$this->params->merchantKey}");
        if ($sign !== $verifystring){
            return null;
        }
        return [$request['orderno'] ?? ''];
    }

    /**
     * @return string
     */
    public function notifySucess()
    {
        return 'T';
    }

    /**
     * @return array|null
     */
    public function queryAccount()
    {

        // agentid=%s&merchantKey=%s
        // verifystring

        // agentid=%s&merchantKey=%s
        $verifystring = md5("agentid={$this->params->agentid}&merchantKey={$this->params->merchantKey}");

        $data = $this->request(self::API_QUERYACCOUNT, [
            'agentid' => $this->params->agentid,
            'verifystring' => $verifystring,
        ]);

        if ($data['resultno'] ?? '0' == 1000){
            return $data;
        } else {
            return [];
        }

    }


    /**
     * @param $api
     * @param array $query
     * @return array|null
     * @throws \Sabre\Xml\ParseException
     */
    protected function request($api, array $query): ?array {
        $client = New Client();
//        $url = $this->params->gateway . $api . '?' .  http_build_query($query);
        $url = self::GATEWAY . $api . '?' .  http_build_query($query);
//        echo $url;
        try{
            $res = $client->get($url);
        } catch (\Exception $e){
            Log::error(json_encode([
                'url'       => $url,
                'response'  => '',
                'exception' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return null;
        }
        if ($res->getStatusCode() == 200){
            $xml = $res->getBody()->getContents();
            $reader = new Service();
            $reader->elementMap = [
                '{http://schemas.datacontract.org/2004/07/KR.NetDistribute.Models}' . self::$api_xml_ns[$api] => 'Sabre\Xml\Deserializer\keyValue',
            ];
            $result = $reader->parse($xml);
            foreach ($result as $key => $item) {
                $newKey = substr($key, strlen(self::XML_KEY_PREFIX));
                $result[$newKey] = $item;
                unset($result[$key]);
            }
            Log::alert(json_encode([
                'url'       => $url,
                'response'  => $xml,
            ], JSON_UNESCAPED_UNICODE));
            return (array)$result;
        } else {
            Log::error(json_encode([
                'url'       => $url,
                'code'      => $res->getStatusCode(),
            ], JSON_UNESCAPED_UNICODE));
            return null;
        }

/*
        $promise = $client->getAsync($url);
        $promise->then(
            function (ResponseInterface $res) use ($url){
                $xml = $res->getBody()->getContents();
                $reader = new Service();
                $result = $reader->parse($xml);
                Log::record(json_encode([
                    'url'       => $url,
                    'response'  => $xml,
                ], JSON_UNESCAPED_UNICODE));
                return (array)$result;
            },
            function (RequestException $e)  use ($url) {
                Log::record(json_encode([
                    'url'       => $url,
                    'code'      => $e->getCode(),
                    'error'     => $e->getMessage(),
                ], JSON_UNESCAPED_UNICODE));
                return null;
            }
        );
        $promise->wait();*/
    }

}