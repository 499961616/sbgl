<?php

namespace app\equipchange\service;


use app\equip\model\BdkModel;
use app\equip\model\DwModel;
use app\equip\model\EquipmentModel;
use app\equip\model\MkModel;
use app\preciousequip\model\PreciousEquipModel;
use think\db\Query;

/**
 * Class EquipChangeService
 * @package app\equip\service
 */
class EquipChangeService
{
    protected  $equipModel;
    protected  $equipMkModel;
    protected  $bdkMkModel;
    protected  $dwModel;
    public function __construct()
    {
        $this->equipModel  = new EquipmentModel();
        $this->equipMkModel  = new MkModel();
        $this->bdkMkModel  = new BdkModel();
        $this->dwModel = new DwModel();
    }

    /**
     * 设备筛选查询 返回自定义查询内容
     * @param $filter
     */

    public function equipList($filter)
    {
        return $this->equipModel
            //给Equipment起一 个别名
            ->alias('e')
            //关联查询单位表 取名d  将设备表的 领用单位id 等于 单位表的ID
            ->join('Dw d','e.receive_id = d.id')
            //条件筛选  分为三个条件
            ->where(function (Query $query) use ($filter) {
                // 1。领用单位id
                $receive_id = empty($filter['receive_id']) ? 0 : intval($filter['receive_id']);
                if (!empty($receive_id)) {
                    $query->where('receive_id', $receive_id);
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
                //3。 价格范围
                $startPrice = empty($filter['start_price']) ? 0 : $filter['start_price'];
                $endPrice   = empty($filter['end_price'])   ? 0 : $filter['end_price'];
                if (!empty($startPrice)) {
                    $query->where('price', '>=', $startPrice);
                }
                if (!empty($endPrice)) {
                    $query->where('price', '<=', $endPrice);
                }
                //仪器编号范围
                $equipIdFrom = empty($filter['equip_id_from']) ? 0 : $filter['equip_id_from'];
                $equipIdTo   = empty($filter['equip_id_to'])   ? 0 : $filter['equip_id_to'];
                if (!empty($equipIdFrom)) {
                    $query->where('equip_id', '>=', $equipIdFrom);
                }
                if (!empty($equipIdTo)) {
                    $query->where('equip_id', '<=', $equipIdTo);
                }
            })
            //查询的字段  单位表的name字段 和设备的所有字段
            ->field('d.name As receive_name ,e.*')
            ->order('e.id')
            ->paginate(30);


    }


    /**
     * 获取筛选的批量修改的设备编号
     * @param $filter
     */

    public function equipId($filter)
    {
        return $this->equipModel
            //条件筛选  分为4个条件
            ->where(function (Query $query) use ($filter) {
                // 1。领用单位id
                $receive_id = empty($filter['receive_id']) ? 0 : intval($filter['receive_id']);
                if (!empty($receive_id)) {
                    $query->where('receive_id', $receive_id);
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
                //5。 价格范围
                $startPrice = empty($filter['start_price']) ? 0 : $filter['start_price'];
                $endPrice   = empty($filter['end_price'])   ? 0 : $filter['end_price'];
                if (!empty($startPrice)) {
                    $query->where('price', '>=', $startPrice);
                }
                if (!empty($endPrice)) {
                    $query->where('price', '<=', $endPrice);
                }
            })

            ->select()->toArray();


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
                $receive_id = empty($filter['receive_id']) ? 0 : intval($filter['receive_id']);
                if (!empty($receive_id)) {
                    $query->where('receive_id', $receive_id);
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
                //3。 价格范围
                $startPrice = empty($filter['start_price']) ? 0 : $filter['start_price'];
                $endPrice   = empty($filter['end_price'])   ? 0 : $filter['end_price'];
                if (!empty($startPrice)) {
                    $query->where('price', '>=', $startPrice);
                }
                if (!empty($endPrice)) {
                    $query->where('price', '<=', $endPrice);
                }
            })
            ->field('count(equip_id) as num, sum(price) as price' )
            ->select()
            ->toArray();
    }


    /**
     * 设备库的上一页
     * @param $filter
     */
    public function equip_pre($filter)
    {
        $pre_info = $this->equipModel->field('id')->where('id','<',$filter)->order('id','DESC')->limit(1)->select();
        return $pre  = isset( $pre_info[0]['id']) ?  $pre_info[0]['id']:'';
    }

    /**
     * 设备库的下一页
     * @param $filter
     */
    
    public function equip_next($filter)
    {
        $next_info = $this->equipModel->field('id')->where('id','>',$filter)->limit(1)->select();
        return $next = isset($next_info[0]['id']) ? $next_info[0]['id'] :'';
    }

    /**
     * 变动库上一页
     * @param $data
     */
    public function bdk_pre($data)
    {
        return $pre_info = $this->bdkMkModel
            ->where('equip_id','<=',$data['equip_id'])
            ->where('id','<',$data['id'])
            ->order('equip_id','desc')->find();
    }

    /**
     * 变动库的下一页
     * @param $data
     */

    public function bdk_next($data)
    {
        return $next_info = $this->bdkMkModel
            ->where('equip_id','>=',$data['equip_id'])
            ->where('id','>',$data['id'])
            ->order('equip_id')->find();
    }

    /**
     * 变动次数
     * @param $id
     *
     */
    public function bdCount($id)
    {

        return $total =  count($this->bdkMkModel->where('equip_id','=',$id)->select());
    }


    /**
     * 设备名库整合
     *
     */

    public function equipMk()
    {
        $mk['instrument_source'] = $this->equipMkModel->selectStatus("仪器来源");
        $mk['use_direction']     = $this->equipMkModel->selectStatus("使用方向");
        $mk['funding_subjects']  = $this->equipMkModel->selectStatus("经费科目");
        $mk['status']            = $this->equipMkModel->selectStatus("现状");

        return $mk;
    }

    /**
     * 判断设备 是否在主机设备表中
     * @param $equip_id
     */

    public function isInEquip($equip_id)
    {
        $result =  $this->equipModel->where('equip_id', '=', $equip_id)->find();
        return $result ? '1' : '0';
    }

    //排序条件
    public function sort()
    {
        //筛选的排序
       return $orders=  array(
            'receive_id' =>  "领用单位" ,
            'equip_id' =>  '仪器编号',
            'sort_id' => "分类号"     );
    }


    /**
     * 返回变动恢复的 领用单位和使用单位名 拼接名字  如23-阳光学院
     * @param  $data
     */
    public function dwName($data)
    {
        $data['transfer_unit_name'] = empty($data['transfer_unit']) ? '':$data['transfer_unit'].'-'.$this->dwModel->dwName($data['transfer_unit']);
        $data['receive_name']         = empty($data['receive_id'])  ? ''  :$data['receive_id'].'-'.   $this->dwModel->dwName($data['receive_id']);
        $data['useunit_name']        = empty($data['useunit_num']) ? ''  :$data['useunit_num'].'-'   .$this->dwModel->dwName($data['useunit_num']);

        return $data;
    }

    /**
     * 返回单条恢复的 领用单位和使用单位名 拼接名字  如23-阳光学院
     * @param  $data
     */
    public function dwChangeName($data)
    {
        $data['receive_name']         = empty($data['receive_id'])  ? ''  :$data['receive_id'].'-'.   $this->dwModel->dwName($data['receive_id']);
        $data['useunit_name']        = empty($data['useunit_num']) ? ''  :$data['useunit_num'].'-'   .$this->dwModel->dwName($data['useunit_num']);

        return $data;
    }
}