<?php


namespace  api\equipment\controller;


use \app\equip\model\DwModel;
use api\equipment\model\RykModel;
use app\equip\model\EquipmentModel;
use app\equip\model\XzcDwModel;
use app\equip\model\XzcEquipmentModel;
use cmf\controller\RestBaseController;
use think\Db;
use think\Request;
use function MongoDB\BSON\toJSON;


class DanWeiController extends  RestBaseController
{

    protected  $dwModel;

    public function __construct()
    {

        $user_id  =  cmf_get_current_admin_id();

        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');

        //判断是那个管理员   就对应哪个数据库
        if ($user_role == 2 || $user_role == 4){
            $this->dwModel  =  new DwModel();

        }elseif ($user_role ==3 || $user_role == 5){

            $this->dwModel  =   new XzcDwModel();
        }else{

            $this->dwModel    =   new DwModel();
        }

    }


    public function search(Request $request)
    {
        $query =  $request->request('q');
        $res =    $this->dwModel->field('id, name')
                  ->where('name','like',"%$query%")
                  ->select();

              return json($res);
    }

    public function dwName(Request $request)
    {
        $query =  $this->dwModel->request('id');
        $res =    DwModel::field('name')
                    ->where('id','=',$query)
                    ->select();

        return json($res);
    }

    //返回单位的所有信息
    public function dwInfo()
    {
        $res =   $this->dwModel->field(['id','pId','name','create_date','fund_subject','unit_nature','use_direction','concat(id,"--",name)'=>'names'])->select();
        return json($res);
    }
    //返回指定单位的所有信息
    public function dwInfos($id)
    {
        $res =    $this->dwModel->field('*')->where('id','=',$id)->find();
        return json($res);
    }

    //更新单位信息
    public function dwUpdate(Request $request){

      $id =$request->post("id");
      $pId =$request->post("pId");
      $name =$request->post("name");
      $unit_nature =$request->post("unit_nature");
      $fund_subject =$request->post("fund_subject");
      $use_direction =$request->post("use_direction");
      $create_date =$request->post("create_date");


       $res =   $this->dwModel->where('id','=',$id)
           ->update(['name' => $name,'unit_nature'=>$unit_nature,'fund_subject'=>$fund_subject,'use_direction'=>$use_direction,'create_date'=>$create_date]);

        if ($res){
            return json(['code'=>1]);
        }else{
            return json(['code'=>0]);
        }
    }

    //返回新单位id
    public function dwNewId(Request $request)
    {

        $pId =$request->post("pId");

          $id = $this->dwModel->where('pId','=',$pId)->order('id','desc')->value('id');
//            如果没有下级则默认在父级 后面加01  有的话在下级id加1
            if (empty($id)){
                return  $pId.'01';
            }else{
                return $id+1;
            }

    }

    //添加单位
    public function dwAdd(Request $request)
    {
        $data['id'] =   $request->post("id");
        $data['pId'] =  $request->post("pId");
        $data['name'] = $request->post("name");
        $data['unit_nature'] =  $request->post("unit_nature");
        $data['fund_subject'] = $request->post("fund_subject");
        $data['use_direction'] =$request->post("use_direction");
        $data['create_date'] =  $request->post("create_date");

        $res =  $this->dwModel->allowField(true)->save($data);
        if ($res){
            return $this->success('添加成功！');
        }else{
            return $this->error('添加失败！');
        }
    }

    //删除单位
    public function dwDel(Request $request)
    {

        $id =   $request->post("id");

        $res = $this->dwModel->where('id','=',$id)->delete();
        if ($res){
            return $this->success('删除成功！');
        }else{
            return $this->error('删除失败！');
        }
    }

    //返回上报单位
    public function reportDw()
    {
        $res =   $this->dwModel->field(['id','concat(id,"    --   ",name)'=>'names'])->select();
        return json($res);
    }

    //保存上报单位
    public function saveReport(Request $request)
    {

        $info = $request->post('data');
        //前台获取的数据
        $data =  ['data'=>$info,'createtime'=>date('Y-m-d')];
//        return $data;

        //获取数据库上一次上报的单位id
        $lastId = Db::name('report')->order('id','desc')->limit(1)->value('id');


//        如果没有就新增 有就更新
        if ($lastId){
             Db::name('report')->where('id',$lastId)->update($data);
            return   json(['code'=>1,'message'=>'上报成功！']);
         }else{
              Db::name('report')->insert($data);
            return   json(['code'=>1,'message'=>'上报成功！']);
        }

    }


    //返回上一次的上报单位
    public function oldReportDw()
    {
          $data =     Db::name('report')->order('id','desc')->value('data');
        if ($data){
              return   json_decode($data);
          }
    }
}