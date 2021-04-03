<?php


namespace app\datareport\controller;


use app\equip\model\EquipmentModel;
use app\equip\service\EquipService;
use app\preciousequip\controller\PreciousEquipController;
use app\preciousequip\model\PreciousEquipModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\db\Query;

class DataReportController extends AdminBaseController
{
    public function index()
    {
        return $this->fetch();
        }

    public function preciousSure()
    {

        $startTime = date("Y") ;
        $endTime   = date("Y") +1;

//        return $startTime;

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

        $datas->appends($startTime);
        $datas->appends($endTime);
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

    public function reportTxt()
    {
        return $this->fetch();
    }

    public function txt()
    {
        $equipModel = new EquipmentModel();

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
                $sql = "SELECT d.name As receive_name,`e`.* FROM `cmf_equipment` `e` INNER JOIN `cmf_dw` `d` ON `e`.`receive_id`=`d`.`id` WHERE ( `receive_id`  =  $id AND `asset_types` >= 3 AND `asset_types` >= 3 AND `price` >= 800 ) OR ( `asset_types` = 12 AND `asset_types` = 14 ) ";


                $equipData = $equipModel->query($sql);

                array_push($newEquipData,$equipData);
            }
        $newEquipData =   array_reduce($newEquipData, 'array_merge', []);
//            //返回json格式给前台
            return  json($newEquipData);
//

    }



}