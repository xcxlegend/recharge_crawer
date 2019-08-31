<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $userModel = model('User');
        $orderModel = model('Order');

        $this->view->assign([
            'totaluser'        => $userModel->where(['group_id'=>1])->count(),
            'totalorder'       => $orderModel->count(),
            'totalusermoney'   => $userModel->sum('money'),
            'totalorderamount' => $orderModel->sum('money'),
            'totalorder0'   => $orderModel->where(['status'=>0])->count(),
            'totalorder1'  => $orderModel->where(['status'=>1])->count(),
            'totalorder2'       => $orderModel->where(['status'=>2])->count(),
            'totalorder3'    => $orderModel->where(['status'=>3])->count(),
        ]);

        return $this->view->fetch();
    }

}
