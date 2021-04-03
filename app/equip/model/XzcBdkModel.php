<?php


namespace app\equip\model;


use think\Model;

class XzcBdkModel extends Model
{

    //类型转换
    protected $type = [
        'more' => 'array',
    ];

    //变动恢复首次点击进去  返回数据信息
    public function returnData()
    {
      return  $this->alias('b')
          ->join('xzc_dw d','b.transfer_unit =d.id')
          ->field('d.name as transfer_unit_name,b.*')
          ->order('b.change_date','desc' )
          ->find();
    }
}