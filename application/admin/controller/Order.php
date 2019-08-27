<?php

namespace app\admin\controller;
use app\common\controller\Backend;
class Order extends Backend
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Order');
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
                
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    //查单
    public function queryOrder()
    {

        $id = $this->request->post("id");
        if(!$id){
            $this->error();
        }
        $order = $this->model->find($id);
        if($order['status']==1){
            $order = $order->toArray();
            $result = model('Common/Order')->queryOrder($order);
            $this->success($result);
        }else{
            $this->error('订单错误');
        }  
        
    }

    //回调
    public function doNotify()
    {

        $id = $this->request->post("id");
        if(!$id){
            $this->error();
        }
     
        $order = $this->model->find($id);
        if($order['status']==2){
            $result = model('Common/Order')->pubNotify($order,$order['status']);
            if ($result === false){
                $this->error(model('Common/Order')->getError());
            }
            $this->success();
        }else{
            $this->error('订单错误');
        }  
  
        
    }

}
