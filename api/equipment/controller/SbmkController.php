<?php


namespace api\equipment\controller;


use api\equipment\model\SbmkModel;
use cmf\controller\RestBaseController;
use think\Request;

class SbmkController extends  RestBaseController
{

//查询设备名库 模糊查询的分类号所对应的 id和名字 返回json格式
    public function  search(Request  $request)
    {
        $query =  $request->request('q');
        $res=   SbmkModel::field('id, name')
            ->where('name','like',"%$query%")
            ->select();

        return json($res);

    }

}