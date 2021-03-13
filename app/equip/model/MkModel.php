<?php


namespace app\equip\model;


use think\Model;
use function MongoDB\BSON\toJSON;

class MkModel extends Model
{

    protected $type = [
        'more' => 'array',
    ];

    //查询下拉框数据
    public function selectStatus($name)
    {
         return $this->where('BJ',$name)->column('NR');

    }
}