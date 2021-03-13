<?php
namespace app\equip\validate;

use think\Validate;

class EquipmentValidate extends Validate
{
    protected $rule = [
        'equip_id' => 'require',
        'sort_id' => 'require',
        'bookkeeper' => 'require',
        'receive_id' => 'require',
    ];
    protected $message = [
        'equip_id.require' => '仪器编号不能为空！',
        'sort_id.require' => '分类号不能为空！',
        'bookkeeper.require' => '记账人不能为空！',
        'receive_id.require' => '领用单位不能为空！',
    ];
}