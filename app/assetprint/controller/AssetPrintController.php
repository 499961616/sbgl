<?php

namespace  app\assetprint\controller;


use app\equip\service\EquipService;
use cmf\controller\AdminBaseController;

class AssetPrintController extends AdminBaseController
{
    public function index()
    {
        $param = $this->request->param();

        //时间格式替换  Y-M-D  替换为 Y.M.D
        if (!empty($param['start_time'])){
            $param['start_time'] = str_replace('-','.',$param['start_time']);
            $param['end_time'] = str_replace('-','.',$param['end_time']);
        }


        $equipService = new EquipService();
        //查询的数据
        $data  = $equipService->equipList($param);
        //共多少件设备；总共价值:多少元；
        $equipPrice =$equipService->equipPrice($param);

//        return  var_dump($param);

        $data->appends($param);
        $this->assign("equipPrice", $equipPrice);
        $this->assign('order',        isset($param['order']) ? $param['order'] : '');
        $this->assign('receive_name', isset($param['receive_name']) ? $param['receive_name'] : '');
        $this->assign('receive_id',   isset($param['receive_id']) ? $param['receive_id'] : '');
        $this->assign('start_time',   isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time',   isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('start_equip_id',   isset($param['start_equip_id']) ? $param['start_equip_id'] : '');
        $this->assign('end_equip_id',   isset($param['end_equip_id']) ? $param['end_equip_id'] : '');
        //数据渲染
        $this->assign("data", $data->items());
        //渲染分页
        $this->assign('page', $data->render());

        return $this->fetch();
}


    //资产条形码打印
    public function prints()
    {
        $param = $this->request->param();

        //时间格式替换  Y-M-D  替换为 Y.M.D
        if (!empty($param['start_time'])){
            $param['start_time'] = str_replace('-','.',$param['start_time']);
            $param['end_time'] = str_replace('-','.',$param['end_time']);
        }

        $equipService = new EquipService();
        //查询打印的数据
        $data  = $equipService->equipPrintList($param);


        //数据渲染
        $this->assign("param", $param);
        $this->assign("data", $data);

        //渲染分页

        return $this->fetch();
    }
}