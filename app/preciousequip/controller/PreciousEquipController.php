<?php

//精密仪器
namespace app\preciousequip\controller;


use app\equip\model\EquipmentModel;
use app\equip\model\MkModel;
use app\equip\service\EquipService;
use app\preciousequip\model\PreciousEquipModel;
use app\equip\service\EquipChangeService;
use cmf\controller\AdminBaseController;

class PreciousEquipController extends AdminBaseController
{

    public function index()
    {
        $date = date("Y");
        //数据渲染
        $this->assign("date", $date);
        return $this->fetch();

    }

    public function show()
    {
        $param = $this->request->param();

        $startTime = empty($param['start_time']) ? date("Y") : $param['start_time'];
        $endTime   = empty($param['end_time'])   ? date("Y")+1  : $param['end_time'];

         $years = $startTime."/".$endTime;

        $PreciousEquipModel = new PreciousEquipModel();
        $equipService = new EquipService();

        //查询指定 年使用表 中有没有信息
        $res = $PreciousEquipModel->where("years",$years)->select();

        //如果没有查询到 则插入精密设备指定的年使用记录
        if (count($res) == 0){
          $res= $equipService->insertPreciousData($years);
          if ($res){
              //查询的数据
           return   $datas  = $equipService->preciousEquipList($years);
          }

        }
        //查询的数据
        $datas  = $equipService->preciousEquipList($years);

        $datas->appends($param);
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        //数据渲染
        $this->assign("datas", $datas);
        $this->assign("years", $years);
        $this->assign("startTime", $startTime);
        $this->assign("endTime", $endTime);

        //渲染分页
        $this->assign('pages', $datas->render());

        return $this->fetch();
    }

//精密设备年使用更新
    public function update()
    {
        $data = $this->request->param();
        $startTime = substr($data['years'],0,4);
        $endTime = substr($data['years'],5,7);

        $equip_id =  $data['equip_id'];

       $preModel = new PreciousEquipModel();
       $res = $preModel->where('equip_id',$equip_id)->Update($data);
       if ($res){
           $this->success('添加成功!',url('PreciousEquip/show',
               array('start_time'=>$startTime, 'end_time'=>$endTime)));
       }
        $this->success('添加失败!',url('PreciousEquip/show',url('PreciousEquip/show',
            array('start_time'=>$startTime, 'end_time'=>$endTime))));

}






}