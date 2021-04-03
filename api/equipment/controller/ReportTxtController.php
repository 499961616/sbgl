<?php


namespace api\equipment\controller;


use app\equip\model\EquipmentModel;
use app\equip\service\EquipService;
use app\preciousequip\model\PreciousEquipModel;
use cmf\controller\RestBaseController;
use think\Db;
use think\db\Query;
use think\Request;


class ReportTxtController extends RestBaseController
{


    public function typeOne(Request  $request)
    {

        $equipModel = new EquipmentModel();

        $data  =  $request->post();
        $type   = $data['type'];
        //表一
        if ($type == 1){
            //获取到上报的素有单位信息
         $dwInfo =  Db::name('report')->limit(1)->value('data');
         //转换成数组格式
         $dwInfo =   array_column(json_decode($dwInfo, true), 'id');
        //对数组进行升序排序 方便统计查看
         asort($dwInfo);
         //存放所有符合的上报设备
          $newEquipData = array();

            //循环查询符合上报的设备进行叠加
            for ($i=0;$i<count($dwInfo);$i++) {

                $id = $dwInfo[$i];
                $sql = " SELECT d.name As receive_name,`e`.* FROM `cmf_equipment` `e` INNER JOIN `cmf_dw` `d` ON `e`.`receive_id`=`d`.`id` WHERE ( `receive_id`  =  $id AND `asset_types` >= 3 AND `asset_types` >= 3 AND `price` >= 800  AND `e`.`use_direction` IN (1,2)) OR ( `asset_types` = 12 AND `asset_types` = 14 ) ";
                $equipData = $equipModel->query($sql);

                array_push($newEquipData,$equipData);
            }
            $newEquipData =   array_reduce($newEquipData, 'array_merge', []);
//            //返回json格式给前台
            return  json($newEquipData);
//
        }elseif ($type == 2){
            //表2
        }elseif ($type == 3){
            //表3


            $startTime = date("Y") ;
            $endTime   = date("Y") +1;

//        return $startTime;

            $years = $startTime."/".$endTime;

            $PreciousEquipModel = new PreciousEquipModel();
            $equipService = new EquipService();

            //查询指定 年使用表 中有没有信息
            $res = $PreciousEquipModel->where("years",$years)->select()->toArray();

            return  json($res);
        }

    }
}