<?php


namespace api\equipment\controller;


use api\equipment\model\PreciousEquipModel;
use cmf\controller\RestBaseController;

class PreciousEquipController  extends  RestBaseController
{
//根据仪器id 返回所有信息
    public function search($id)
    {
         $data = new PreciousEquipModel();

        $res = $data->where('equip_id',$id)->find();
        return json($res);
    }
}