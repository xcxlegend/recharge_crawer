<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use fast\Random;
use think\Config;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;


    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            //print_r($where);
            $total = $this->model
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $groupList = \app\admin\model\UserGroup::column('id,name');
        $this->view->assign('groupList',$groupList);
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
                if ($params['password'])
                {
                    $params['salt'] = Random::alnum();
                    $params['password'] = md5(md5($params['password']) . $params['salt']);
                } else {
                    unset($params['password'], $params['salt']);
                }

                $extend = json_decode($row['extend'],true);
                $extend['pay_code'] =$params['extend']['pay_code'];
                $params['extend'] = json_encode($extend);
                $userValidate = \think\Loader::validate('User');
                $userValidate->rule([
                    'username' => 'require|max:50|unique:user,username,' . $row->id,
                    'email'    => 'require|email|unique:user,email,' . $row->id
                ]);
                $result = $row->validate('User.edit')->save($params);
                if ($result === false)
                {
                    $this->error($row->getError());
                }

                $this->success();
            }
            $this->error();
        }
        $row['extend'] = json_decode($row['extend'],true);
        $this->view->assign("row", $row);
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        $this->view->assign('paycodeList', build_select('row[extend][pay_code]', Config::get('pt_pay_codes'), $row['extend']['pay_code'], ['class' => 'form-control selectpicker']));
        return $this->view->fetch();

    }

    public function resetkey($id)
    {
        $row = $this->model->get(['id' => $id]);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            $extend = json_decode($row['extend'],true);
            $extend['appkey'] =$extend['appkey'] = Random::alnum(32);
            $params['extend'] = json_encode($extend);
            
            $result = $row->save($params);
            if ($result === false)
            {
                $this->error($row->getError());
            }

            $this->success();
        }
    }

    public function add()
    {
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                $params['salt'] = Random::alnum();
                $params['password'] = md5(md5($params['password']) . $params['salt']);
                $extend['appkey'] = Random::alnum(32);
                $extend['pay_code'] =$params['extend']['pay_code'];
                $params['extend'] = json_encode($extend);
                $result = $this->model->validate('User.add')->save($params);
                if ($result === false)
                {
                    $this->error($this->model->getError());
                }
                $this->success();
            }
            $this->error();
        }
        

        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), 0, ['class' => 'form-control selectpicker']));
        $this->view->assign('paycodeList', build_select('row[extend][pay_code]', Config::get('pt_pay_codes'), 0, ['class' => 'form-control selectpicker']));
        return $this->view->fetch();
    }

}
