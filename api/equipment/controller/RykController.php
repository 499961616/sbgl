<?php


namespace api\equipment\controller;


use api\equipment\model\RykModel;
use api\equipment\model\XzcRykModel;
use app\equip\model\DwModel;
use app\equip\model\XzcDwModel;
use cmf\controller\RestBaseController;
use think\Db;
use think\Request;

class RykController extends  RestBaseController
{

    protected  $RykModel;

    public function __construct()
    {

        $user_id  =  cmf_get_current_admin_id();

        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');

        //判断是那个管理员   就对应哪个数据库
        if ($user_role == 2 || $user_role == 4){

            $this->RykModel  =  new RykModel();
        }elseif ($user_role ==3 || $user_role == 5){

            $this->RykModel  =   new XzcRykModel();
        }else{
            $this->RykModel    =   new RykModel();
        }

    }

    //根据单位id  返回本单位的所有管理老师
    public function employer(Request $request)
    {
        $id =  $request->request('id');

        $res=   $this->RykModel->field('name')
            ->where('unitId','=',$id)
            ->select();;

        return json($res);
    }

    //根据领用单位id返回所有信息
    public function dwEmployer($id)
    {

         $res =  $this->RykModel->where('unitId','=',$id)->select();
        return json($res);
    }


    //删除单位领用人
    public function delEmployer($id)
    {

        $res =  $this->RykModel->where('id','=',$id)->delete();
        return json($res);
    }


    //更新领用人
    public function updateEmployer(Request $request)
    {
        $id=  $request->post('id');

        $name=  $request->post('name');

        $res = $this->RykModel->where('id','=',$id)->update(['name'=>$name]);
        return json($res);

    }

}