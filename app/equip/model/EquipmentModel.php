<?php
namespace app\equip\model;


use think\Db;
use think\Model;

class EquipmentModel extends Model
{

    protected $type = [
        'more' => 'array',
    ];


    //获取最新的一条记录的仪器编号
    public function getNewId()
    {
        //取出最大的id值
        $id= $this->max('id');
        //根据最大id 找出最新的一条设备记录equip_id
        $lastId=  $this->where('id',$id)->value("equip_id");
        //例：仪器编号为 1900492y 则要分割两个部分 数字1900492 和 字母y  数字加1  字母不变
        $newLast = substr($lastId, 7, 1);
        $newFirst = substr($lastId, 0, 7);
         return $newId = $newFirst + 1 . $newLast;
    }




}