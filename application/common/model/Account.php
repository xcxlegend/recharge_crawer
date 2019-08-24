<?php


namespace app\common\model;
use think\Db;
use think\Model;

class Account extends Model
{


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


}