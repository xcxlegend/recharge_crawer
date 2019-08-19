<?php


namespace app\common\library\pay;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;
use think\Log;

class CSTPay extends IPay
{
    const API_QUERYACCOUNT = '/MainServiceBusiness/GetAgentInfo';
    const XML_KEY_PREFIX = '{http://schemas.datacontract.org/2004/07/KR.NetDistribute.Models}';


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


    public function order()
    {
        // TODO: Implement order() method.
    }

    public function queryOrder()
    {
        // TODO: Implement queryOrder() method.
    }

    public function checkNotify()
    {
        // TODO: Implement checkNotify() method.
    }

    public function notifySucess()
    {
        // TODO: Implement notifySucess() method.
    }

    public function queryAccount()
    {

        // agentid=%s&merchantKey=%s
        // verifystring

        // agentid=%s&merchantKey=%s
        $verifystring = md5("agentid={$this->params['agentid']}&merchantKey={$this->params['merchantKey']}");

        $data = $this->request(self::API_QUERYACCOUNT, [
            'agentid' => $this->params['agentid'],
            'verifystring' => $verifystring,
        ]);

        if ($data['resultno'] ?? '0' == 1000){
            return $data;
        } else {
            return [];
        }

    }


    // 因为是get请求. 所以直接请求
    protected function request($api, array $query): ?array {
        $client = New Client();
        $url = $this->params['gateway'] . $api . '?' .  http_build_query($query);
        $res = $client->get($url);
        if ($res->getStatusCode() == 200){
            $xml = $res->getBody()->getContents();
            $reader = new Service();
            $reader->elementMap = [
                '{http://schemas.datacontract.org/2004/07/KR.NetDistribute.Models}AgentInfoReturn' => 'Sabre\Xml\Deserializer\keyValue',
            ];
            $result = $reader->parse($xml);
            foreach ($result as $key => $item) {
                $newKey = substr($key, strlen(self::XML_KEY_PREFIX));
                $result[$newKey] = $item;
                unset($result[$key]);
            }
            dump($result);
            Log::record(json_encode([
                'url'       => $url,
                'response'  => $xml,
            ], JSON_UNESCAPED_UNICODE));
            return (array)$result;
        } else {
            Log::record(json_encode([
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