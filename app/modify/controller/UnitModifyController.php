<?php

//主机设备
namespace app\modify\controller;


use api\equipment\model\RykModel;
use app\equip\model\DwModel;
use cmf\controller\AdminBaseController;

class UnitModifyController extends AdminBaseController
{
    public function index()
    {

        return $this->fetch();

    }

    public function changeEmployer()
    {
        return $this->fetch();
    }

    public function addEmployer()
    {


       $data['unitId'] = $this->request->param('unitId');
       $data['name'] = $this->request->param('unitName');

       $dwModel = new DwModel();
        $data['unitName'] =$dwModel->where('id','=', $data['unitId'])->value('name');


        $rykModel = new  RykModel();
       $res =  $rykModel->insert($data);
       if ($res){
           return $this->success('添加成功');
       }else{
           return $this->error('添加失败');

       }

    }




}