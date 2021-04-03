<?php

namespace app\equip\model;


use think\Model;

class SbmkModel extends Model
{

    //类型转换
    protected $type = [
        'more' => 'array',
    ];

    //返回设备类型
    public function searchAssetType($sort_id)
    {

        return $this->where('id',$sort_id)->value('BZF');

    }

}