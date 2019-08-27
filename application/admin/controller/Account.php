<?php

namespace app\admin\controller;
use app\common\controller\Backend;
use fast\Random;
class Account extends Backend
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Account');
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
            //print_r($where);
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
                $v->hidden(['password', 'pay_password','appkey']);
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                if (!$params['password']){
                    unset($params['password']);
                }
                if (!$params['pay_password']){
                    unset($params['pay_password']);
                }

                $userValidate = \think\Loader::validate('Account');
                $userValidate->rule([
                    'username' => 'require|max:50|unique:account,username,' . $row->id,
                    'number' => 'require|max:50|unique:account,number,' . $row->id,
                ]);
                $result = $row->validate('Account.edit')->save($params);
                if ($result === false)
                {
                    $this->error($row->getError());
                }

                $this->success();
            }
            $this->error();
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();

    }

    public function add()
    {
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                $result = $this->model->validate('Account.add')->save($params);
                if ($result === false)
                {
                    $this->error($this->model->getError());
                }
                $this->success();
            }
            $this->error();
        }
        return $this->view->fetch();
    }
    //刷新余额
    public function refresh()
    {

        $id = $this->request->post("id");
        $ids = explode( ',',$id);
        if ($ids)
        {
            $commonModel = model('Common/Account');
            $commonModel->startTrans();
            foreach ($ids as $value)
            {
                $result = $commonModel->refresh($value);
                if ($result === false)
                {
                    $this->rollback();
                    $this->error($commonModel->getError());
                    break; 
                }
            }
            $commonModel->commit();
            $this->success();
        }
        $this->error();
    }

}
