<?php
use think\facade\Route;

//查找单位
Route::get('equipment/dw', 'equipment/DanWei/search');

//更新单位信息
Route::post('equipment/dwUpdate', 'equipment/DanWei/dwUpdate');

//添加单位
Route::post('equipment/dwAdd', 'equipment/DanWei/dwAdd');

//新单位id
Route::post('equipment/dwNewId', 'equipment/DanWei/dwNewId');

//单位删除
Route::post('equipment/dwDel', 'equipment/DanWei/dwDel');

//返回单位
Route::get('equipment/dwInfo', 'equipment/DanWei/dwInfo');

//根据单位id 返回单位全部信息
Route::get('equipment/equipId/:id', 'equipment/DanWei/dwInfos');

//根据单位id 返回单位名
Route::get('equipment/equipId', 'equipment/DanWei/dwName');

//查找单位领用人
Route::get('equipment/employer', 'equipment/Ryk/employer');

//删除单位领用人
Route::post('equipment/delEmployer/:id', 'equipment/Ryk/delEmployer');

//更新单位领用人
Route::post('equipment/updateEmployer', 'equipment/Ryk/updateEmployer');

//返回指定单位的领用人
Route::post('equipment/dwEmployer/:id', 'equipment/Ryk/dwEmployer');

//查找设备名称分类
Route::get('equipment/equipName', 'equipment/Sbmk/search');

//返回所选的精密设备详细信息
Route::get('equipment/detail/:id', 'equipment/PreciousEquip/search');

