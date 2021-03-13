<?php


namespace api\equipment\controller;


use api\equipment\model\RykModel;
use cmf\controller\RestBaseController;
use think\Request;

class RykController extends  RestBaseController
{
    //根据单位id  返回本单位的所有管理老师
    public function employer(Request $request)
    {
        $id =  $request->request('id');
//        return $query;
        $res=   RykModel::field('name')
            ->where('unitId','=',$id)
            ->select();;

        return json($res);
    }
//
    public function dwEmployer($id)
    {

        $rykModel = new RykModel();
         $res =  $rykModel->where('unitId','=',$id)->select();
        return json($res);
    }


    //删除单位领用人
    public function delEmployer($id)
    {
        $rykModel = new RykModel();
        $res =  $rykModel->where('id','=',$id)->delete();
        return json($res);
    }

    public function updateEmployer(Request $request)
    {
        $id=  $request->post('id');
        $name=  $request->post('name');
        $rykModel = new RykModel();
        $res =  $rykModel->where('id','=',$id)->update(['name'=>$name]);
        return json($res);

    }

}