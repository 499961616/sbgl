<?php


namespace app\equip\model;
use think\Model;

class DwModel extends Model
{

    //类型转换
    protected $type = [
        'more' => 'array',
    ];

    //根据单位ID返回单位名称
    public function dwName($id)
    {

       return  $this->where('id','=',$id)
            ->value('name');


    }

}