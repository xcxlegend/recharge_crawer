<?php


namespace app\common\model;
use app\common\library\pay\Factory;
use app\common\library\pay\InitParam;
use think\Db;
use think\Model;

class Account extends Model
{


    /**
     * @param $money
     * @return array|null
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function findValidAccount($money): ?array {
        $query = ['money' => ['>=', $money]];
        $count = $this->where($query)->count();
        if ($count == 0) {
            return null;
        }

        $start = $this->where($query)->order('id asc')->limit(0, 1)->find()->toArray();
        $rand = random_int(0, $count);
        $this->startTrans();
        $account = $this->where(
            [
                'money' => ['>=', $money],
                'id'    => ['>=', $start['id']]
            ]
        )->order('id asc')->limit(0, $rand)->find();
        if (!$account){
            $this->rollback();
            return null;
        }

        $this->where(['id' => $account['id']])->update(
            [
                'money'      => Db::raw("money - {$money}"),
                'lock_money' => Db::raw("lock_money + {$money}"),
            ]
        );
        $this->commit();

        return $account->toArray();
    }


    /**
     * 刷新余额
     * @param $id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refresh($id): string {
        $account = $this->find($id);
        if (!$account) {
            return "平台账号错误";
        }
        $Pay = Factory::create('CST', new InitParam($account));
        $data = $Pay->queryAccount();
        $agentbalance = $data['agentbalance'] ?? -1;
        if ($agentbalance >= 0) {
            $this->where(['id' => $id])->setField('money', $agentbalance);
        }
        return true;
    }


}