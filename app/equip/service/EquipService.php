<?php

namespace app\equip\service;


use app\equip\model\EquipmentModel;
use app\preciousequip\model\PreciousEquipModel;
use think\db\Query;

/**
 * Class EquipChangeService
 * @package app\equip\service
 */
class EquipService
{
    protected  $equipModel;
    public function __construct()
    {
        $this->equipModel  = new EquipmentModel();
    }

    /**
     * 设备筛选查询 返回自定义查询内容 带分页
     * @param $filter
     */

    public function equipList($filter)
    {
        $order   = !empty($filter['order']) ? $filter['order'] : 'receive_id';
        $equip  = new  EquipmentModel();
         return $equip
             //给Equipment起一个别名
             ->alias('e')
             //关联查询单位表 取名d  将设备表的 领用单位id 等于 单位表的ID
             ->join('Dw d','e.receive_id = d.id')
             //条件筛选
            ->where(function (Query $query) use ($filter) {
                // 1。领用单位id
                $receive_id = empty($filter['receive_id']) ? 0 : intval($filter['receive_id']);
                if (!empty($receive_id)) {
                    $query->where('receive_id','like', $receive_id. '%');
                }
                //2。 时间范围
                $startTime = empty($filter['start_time']) ? 0 : $filter['start_time'];
                $endTime   = empty($filter['end_time'])   ? 0 : $filter['end_time'];
                if (!empty($startTime)) {
                    $query->where('torage_time', '>=', $startTime);
                }
                if (!empty($endTime)) {
                    $query->where('torage_time', '<=', $endTime);
                }
                 //3。 价格范围
                 $startPrice = empty($filter['start_price']) ? 0 : $filter['start_price'];
                 $endPrice   = empty($filter['end_price'])   ? 0 : $filter['end_price'];
                 if (!empty($startPrice)) {
                     $query->where('price', '>=', $startPrice);
                 }
                 if (!empty($endPrice)) {
                     $query->where('price', '<=', $endPrice);
                 }
                //4。关键字
                $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
                if (!empty($keyword)) {
                    $query->where('equip_name', 'like', "%$keyword%");
                }

            })
            //查询的字段  单位表的name字段 和设备的所有字段
            ->field('d.name As receive_name ,e.*')

            ->order($order, 'asc')

             ->paginate(30);


    }

    /**
     * 设备筛选查询 返回自定义查询内容
     * @param $filter
     */

    public function equipAllList($filter)
    {
        $order   = !empty($filter['order']) ? $filter['order'] : 'receive_id';
        $equip  = new  EquipmentModel();
        return $equip
            //给Equipment起一个别名
            ->alias('e')
            ->join('Dw d','e.receive_id = d.id')
            //条件筛选  分为三个条件
            ->where(function (Query $query) use ($filter) {
                // 1。领用单位id
                $receive_id = empty($filter['receive_id']) ? 0 : intval($filter['receive_id']);
                if (!empty($receive_id)) {
                    $query->where('receive_id','like', $receive_id. '%');
                }
                //2。 时间范围
                $startTime = empty($filter['start_time']) ? 0 : $filter['start_time'];
                $endTime   = empty($filter['end_time'])   ? 0 : $filter['end_time'];
                if (!empty($startTime)) {
                    $query->where('torage_time', '>=', $startTime);
                }
                if (!empty($endTime)) {
                    $query->where('torage_time', '<=', $endTime);
                }
                //2。 时间范围
                $startPrice = empty($filter['start_price']) ? 0 : $filter['start_price'];
                $endPrice   = empty($filter['end_price'])   ? 0 : $filter['end_price'];
                if (!empty($startPrice)) {
                    $query->where('price', '>=', $startPrice);
                }
                if (!empty($endPrice)) {
                    $query->where('price', '<=', $endPrice);
                }
                //3。关键字
                $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
                if (!empty($keyword)) {
                    $query->where('equip_name', 'like', "%$keyword%");
                }

            })
            //查询的字段  单位表的name字段 和设备的所有字段
            ->field('e.*,d.name As receive_name')

            ->order($order, 'asc')

            ->select()->toArray();


    }


    /**
     * 精密设备列表
     * @param $years
     */
    //
    public function preciousEquipList($years)
    {


        $equip  = new  PreciousEquipModel();
        $res =  $equip
            //给Equipment起一个别名
            ->alias('p')
            //关联查询单位表 取名d  将设备表的 领用单位id 等于 单位表的ID
            ->join('Dw d','p.receive_id = d.id')
            ->where(function (Query $query) use ($years) {
                $query->where('years',$years);
            })
            //查询的字段  单位表的name字段 和设备的所有字段
            ->field('p.*,d.name As receive_name')
            ->order('id', 'DESC')
            ->paginate(14);

        return $res;
    }

    /**
     *  新增设备时 如果大于等于40万价格并且分类号为03开头的设备   则自动插入精密仪器年使用表
     * @param $data
     */

    public function isInsertData($data)
    {
        $time = date("Y");
            if ($data['price']>=400000 &&substr($data['sort_id'],0,2) == 03 ){
                    $pre = new PreciousEquipModel();
                    $data['years'] = $time. '/' .($time+1) ;
                    $pre->save($data);
            }

    }

    /**
     * 首次获取精密设备的年使用信息 如果没有数据记录 则创建该年的设备记录
     * @param $years
     */

    public function insertPreciousData($years)
    {
        //从主机表筛选 获取所有精密设备
        $where['LEFT(sort_id,2)'] = array('=','03');

        $datas = $this->equipModel
            ->where('price','>=',400000)
            ->where($where)
            ->select()->toArray();

        //根据精密设备数据条数 循环插入到 年使用表中
            for ($i=0;$i<count($datas);$i++){
                unset($datas[$i]['id']);
                $datas[$i]['years'] = $years;
                $preciousModel = new PreciousEquipModel();
                $preciousModel->save($datas[$i]);

            }
    }


    /**
     * 获取筛选的批量修改的设备数量和价格
     * @param $filter
     */
    public function equipPrice($filter)
    {
        return $this->equipModel
            //条件筛选  分为4个条件
            ->where(function (Query $query) use ($filter) {
                // 1。领用单位id
                $receive_id= empty($filter['receive_id']) ? 0 : intval($filter['receive_id']);
                if (!empty($receive_id)) {
                    $query->where('receive_id','like', $receive_id. '%');
                }
                //2。 时间范围
                $startTime = empty($filter['start_time']) ? 0 : $filter['start_time'];
                $endTime   = empty($filter['end_time'])   ? 0 : $filter['end_time'];
                if (!empty($startTime)) {
                    $query->where('torage_time', '>=', $startTime);
                }
                if (!empty($endTime)) {
                    $query->where('torage_time', '<=', $endTime);
                }
                //3。关键字
                $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
                if (!empty($keyword)) {
                    $query->where('equip_name', 'like', "%$keyword%");
                }
                //4仪器编号范围
                $equipIdFrom = empty($filter['equip_id_from']) ? 0 : $filter['equip_id_from'];
                $equipIdTo   = empty($filter['equip_id_to'])   ? 0 : $filter['equip_id_to'];
                if (!empty($equipIdFrom)) {
                    $query->where('equip_id', '>=', $equipIdFrom);
                }
                if (!empty($equipIdTo)) {
                    $query->where('equip_id', '<=', $equipIdTo);
                }
            })
            ->field('count(equip_id) as num, sum(price) as price' )
            ->select()
            ->toArray();
    }


}