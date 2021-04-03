<?php

//主机设备调拨
namespace app\equiptransfer\controller;
use api\equipment\model\XzcRykModel;
use app\equip\model\MkModel;
use app\equip\model\XzcDwModel;
use app\equip\service\EquipService;
use app\equiptransfer\model\TransferModel;
use cmf\controller\AdminBaseController;
use think\Db;

class EquipTransferController extends  AdminBaseController
{

    //转出
    public function transferOut()
    {
        $user_id  =  cmf_get_current_admin_id();
        //获取角色id
        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');

//        var_dump($user_role);
       $TransferModel =   new  TransferModel();
       $data =   $TransferModel->where('transfer_from',$user_role)->order('transfer_date','desc')->select();
       $this->assign('data',$data);
        return $this->fetch();

    }


    //调拨设备
    public function transferIn()
    {
        $user_id  =  cmf_get_current_admin_id();
        //获取角色id
        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');

//        var_dump($user_role);
        $TransferModel =   new  TransferModel();
        $data =   $TransferModel->where('transfer_to',$user_role)->order('transfer_date','desc')->select();
        $this->assign('data',$data);
        return $this->fetch();

        return $this->fetch();

    }
    
    //撤回调拨
    public function transferDel()
    {
        $id  = $this->request->param('id');

        $TransferModel =   new  TransferModel();
        $res  =   $TransferModel->where('id',$id)->delete();

        if ($res){
            return $this->success('撤回成功','EquipTransfer/transferOut');
        }
        return $this->error('撤回失败','EquipTransfer/transferOut');
    }

    //接收设备调拨
    public function transferAcc()
    {
      //需要接收的设备号
      $equip_id =  $this->request->param('equip_id');
     //调拨表的信息id
      $id       =  $this->request->param('id');
      //获取对方的数据库
      $otherEquipModel =  $this->otherRole();
        //获取自己的数据库
      $ownsModel =  $this->roles();
        //获取对方的设备信息
        $other_equip_data =$otherEquipModel->where('equip_id',$equip_id)->find()->toArray();
        //获取对方的变动库
        $otherBdk = $this->otherBdk();
        //获取对方的单位库
        $otherDw = $this->otherDw();

        $other_equip_data['change_date'] = date('Y-m-d');
        $other_equip_data['receive_name'] = $otherDw->dwName($other_equip_data['receive_id']);
        $other_equip_data['status'] = 'C';
        $other_equip_data['where'] = '调拨出去';

        unset($other_equip_data['id']);

        //插入变动库 并且删除设备表中的信息
        $otherBdk->save($other_equip_data);


        $other_equip_data['equip_id'] = $ownsModel->getNewId();
        $other_equip_data['remark'] = '调拨过来的设备！';
//        return  var_dump(is_object($other_equip_data)) ;
        //把对方的设备添加到自己的数据库
        $ownsModel->allowField(true)->save($other_equip_data); ;

        $otherEquipModel->where('equip_id',$equip_id)->delete();

       $trasferModel = new TransferModel();
       //调拨数据库状态进行变动
        $result =    $trasferModel->where('id',$id)->update(['status'=>1,'equip_id'=>$other_equip_data['equip_id']]);
        if ($result){
            return $this->success('接收成功','EquipTransfer/transferIn');
        }
        return $this->error('接收失败','EquipTransfer/transferIn');

    }
        //拒绝调拨
    public function transferRefuse(){
        //需要接收的设备号
        $equip_id =   $this->request->param('equip_id');
        //调拨表的信息id
        $id     =   $this->request->param('id');

        $trasferModel = new TransferModel();

        $res =  $trasferModel->where('id',$id)->update(['status'=>2]);

        if ($res){
            return $this->success('拒收成功','EquipTransfer/transferIn');

        }
        return $this->error('拒收失败','EquipTransfer/transferIn');

    }


        //设备修改信息
        public function show()
    {
        $equip_id = $this->request->param('equip_id');

        $equipModel =    $this->roles();
        $mk = new MkModel();
        $dwModel = $this->dw();
        $instrument_source = $mk->selectStatus("仪器来源");
        $use_direction = $mk->selectStatus("使用方向");
        $funding_subjects = $mk->selectStatus("经费科目");
        $status = $mk->selectStatus("现状");

        //修改和新增时排除这几个选项， 这几个选项只能在变动的时候选择
        $status = array_diff_key($status,[4=>'',5=>'',6=>'',11=>'',12=>'']);

        $data =$equipModel->where('equip_id',$equip_id)->find();
//        return var_dump($data);
        $data['receive_name'] = empty($data['receive_id'])  ? '':$data['receive_id'].'-'.$dwModel->dwName($data['receive_id']);
        $data['useunit_name'] = empty($data['useunit_num'])? '':$data['useunit_num'].'-'.$dwModel->dwName($data['useunit_num']);
        $this->assign("data", $data);
        $this->assign("instrument_source", $instrument_source);//仪器来源
        $this->assign("use_direction", $use_direction);//使用方向
        $this->assign("funding_subjects", $funding_subjects);//经费科目
        $this->assign("status", $status);//现状


        return $this->fetch();
    }


}