<?php


namespace  api\equipment\controller;


use api\equipment\model\DwModel;
use api\equipment\model\RykModel;
use cmf\controller\RestBaseController;
use think\Request;


class DanWeiController extends  RestBaseController
{
    public function search(Request $request)
    {

        $query =  $request->request('q');
        $res =    DwModel::field('id, name')
                ->where('name','like',"%$query%")
              ->select();

              return json($res);
    }

    public function dwName(Request $request)
    {
        $query =  $request->request('id');
        $res =    DwModel::field('name')
            ->where('id','=',$query)
            ->select();

        return json($res);
    }

    //返回单位的所有信息
    public function dwInfo()
    {
        $res =    DwModel::field(['id','pId','name','create_date','fund_subject','unit_nature','use_direction','concat(id,"--",name)'=>'names'])->select();
        return json($res);
    }
    //返回指定单位的所有信息
    public function dwInfos($id)
    {
        $res =    DwModel::field('*')->where('id','=',$id)->find();
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

        $dwModel = new DwModel();
       $res =   $dwModel->where('id','=',$id)
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
        $dwModel = new DwModel();
        $pId =$request->post("pId");
          $id = $dwModel->where('pId','=',$pId)->order('id','desc')->value('id');
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

        $dwModel = new DwModel($data);
        $res =  $dwModel->allowField(true)->save();
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
        $dwModel = new DwModel();
        $res =  $dwModel->where('id','=',$id)->delete();
        if ($res){
            return $this->success('删除成功！');
        }else{
            return $this->error('删除失败！');
        }
    }


}