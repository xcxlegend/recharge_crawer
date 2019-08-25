<?php


namespace app\common\job;
use app\common\model\Order;
use GuzzleHttp\Client;
use think\queue\Job;

/**
 * 回调的
 * Class Notify
 * @package app\common\job
 */
class Notify
{
    /**
     * @param Job $job
     * @param $data
     */
    public function fire(Job $job, $data) {
        ['url' => $url, 'query' => $query, 'callback' => $callback, 'order' => $order] = $data;
        if (!$this->notify($data)) {
            if ($job->attempts() > 3) {
                // 如果一直超时 没有回调成功 则不回调了
                $job->delete();
                if ($callback) {
                    call_user_func_array([$this, $callback], [$order]);
                }
                return;
            }
            $job->release(10);
            return;
        }
        $job->delete();
        if ($callback) {
            call_user_func_array([$this, $callback], [$order]);
        }
    }


    protected function notify($data): bool {
        ['url' => $url, 'query' => $query, 'callback' => $callback] = $data;
        $client = new Client();
        $response = $client->post($url, ['query' => $query])->getBody()->getContents();
        if (strtolower(substr($response, 0, 2)) == 'ok'){
            return true;
        } else {
            return false;
        }
    }

    protected function finish($order) {
        model('Order', 'common\model')->setOrderFinish($order);
    }

    protected function timeout($order) {
        model('Order', 'common\model')->setOrderTimeout($order);
    }

}