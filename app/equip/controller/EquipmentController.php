<?php

//主机设备
namespace app\equip\controller;


use app\equip\model\MkModel;
use app\equip\model\SbmkModel;
use app\equip\service\Csv;
use app\equip\service\EquipService;
use app\equip\service\ExportService;
use app\equiptransfer\model\TransferModel;
use cmf\controller\AdminBaseController;
use think\Db;



class EquipmentController extends AdminBaseController
{


    public function index()
    {
        $param = $this->request->param();

        //时间格式替换  Y-M-D  替换为 Y.M.D
        if (!empty($param['start_time'])){
            $param['start_time'] = str_replace('-','.',$param['start_time']);
            $param['end_time'] = str_replace('-','.',$param['end_time']);
            }
        //筛选的排序
        $orders = array(
            'receive_id' =>  "领用单位" ,
            'equip_id' =>  '仪器编号',
            'sort_id' => "分类号" );

        $equipService = new EquipService();
        //查询的数据
        $data  = $equipService->equipList($param);
        //共多少件设备；总共价值:多少元；
        $equipPrice =$equipService->equipPrice($param);

        $data->appends($param);
        $this->assign("equipPrice", $equipPrice);
        $this->assign("orders", $orders);
        $this->assign('order',        isset($param['order']) ? $param['order'] : '');
        $this->assign('receive_name', isset($param['receive_name']) ? $param['receive_name'] : '');
        $this->assign('receive_id',   isset($param['receive_id']) ? $param['receive_id'] : '');
        $this->assign('start_time',   isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('start_price',  isset($param['start_price']) ? $param['start_price'] : '');
        $this->assign('end_price',    isset($param['$end_price']) ? $param['$end_price'] : '');
        $this->assign('end_time',     isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword',      isset($param['keyword']) ? $param['keyword'] : '');
        //数据渲染
        $this->assign("data", $data->items());
        //渲染分页
        $this->assign('page', $data->render());

        return $this->fetch();

    }

    //调出 己方设备详细信息
    public function outShow(){

        $equip_id = $this->request->param('equip_id');
        //获取自己的数据库模型
        $equipModel = $this->roles();
        $mk = new MkModel();
        $instrument_source = $mk->selectStatus("仪器来源");
        $use_direction =     $mk->selectStatus("使用方向");
        $funding_subjects =  $mk->selectStatus("经费科目");
        $status = $mk->selectStatus("现状");
        $dwModel = $this->dw();

        $data =$equipModel->where('equip_id',$equip_id)->find()->toArray();

        $data['tran_id'] = $this->request->param('id');

        $data['receive_name'] = empty($data['receive_id'])  ? '':$data['receive_id'].'-'.$dwModel->dwName($data['receive_id']);
        $data['useunit_name'] = empty($data['useunit_num'])? '':$data['useunit_num'].'-'.$dwModel->dwName($data['useunit_num']);

        $this->assign("data", $data);
        $this->assign("instrument_source", $instrument_source);//仪器来源
        $this->assign("use_direction", $use_direction);//使用方向
        $this->assign("funding_subjects", $funding_subjects);//经费科目
        $this->assign("status", $status);//现状
        return $this->fetch();
    }

    //调入 对方设备详细信息
    public function inShow()
    {
        $equip_id = $this->request->param('equip_id');

        $equipModel = $this->otherRole();
        $mk = new MkModel();
        $instrument_source   = $mk->selectStatus("仪器来源");
        $use_direction       = $mk->selectStatus("使用方向");
        $funding_subjects    = $mk->selectStatus("经费科目");
        $status              = $mk->selectStatus("现状");
        $dwModel             = $this->dw();

        $data =$equipModel->where('equip_id',$equip_id)->find();
        $data['tran_id'] = $this->request->param('id');

        $data['receive_name'] = empty($data['receive_id'])  ? '':$data['receive_id'].'-'.$dwModel->dwName($data['receive_id']);
        $data['useunit_name'] = empty($data['useunit_num'])? '':$data['useunit_num'].'-'.$dwModel->dwName($data['useunit_num']);
        $this->assign("data", $data);
        $this->assign("instrument_source", $instrument_source);//仪器来源
        $this->assign("use_direction", $use_direction);//使用方向
        $this->assign("funding_subjects", $funding_subjects);//经费科目
        $this->assign("status", $status);//现状
        return $this->fetch();
    }


    //添加设备页面
    public function add()
    {
        //获取管理员对应的数据库
        $equipModel =    $this->roles();

        //获取最新的设备分类号+1
        $newId = $equipModel->getNewId();

        $this->assign("newId", $newId);
        return $this->fetch();
    }

    //添加主机设备
    public function addPost()
    {

        if ($this->request->isPost()) {

            $sbmkModel = new SbmkModel();
            //获取管理员对应的数据库
            $equipModel =    $this->roles();

            //获取前台所有填写的数据
            $data = $this->request->param();
            //成批条数
            $num =  floor($this->request->param('num'));
            //分类号
            $sort_id = $this->request->param('sort_id');

            $data['asset_types'] = $sbmkModel->searchAssetType($sort_id);

            //后台验证规则
            $result = $this->validate($data, 'Equipment');
            if ($result !== true) {
                //验证失败返回
                $this->error($result);
            }

            //判断条数 如果一条执行  大于1条则循环条数插入
            $service =  new EquipService();
            if ($num==1) {
                //根据分类号查询sbmk表 对应的 设备分类号
//                return $data;
                 $service->isInsertData($data);
                 $equipModel->insert($data);
                $this->success('添加成功!', url('Equipment/edit',array('equip_id'=>$data['equip_id'])));

            }elseif ($num>1){
             // 获取仪器编号批量的区间
                $pre_id =  $equipModel->getNewId();
                $last_id =$equipModel->getInterId($pre_id,$num);

                    for($i=0;$i<$num;$i++){
                    //插入批量的设备id区间
                        $data['eqid_Interval'] =$pre_id.'--'.$last_id;
                        $data['equip_id'] =$equipModel->getNewId();
                        $service->isInsertData($data);
                        $equipModel->insert($data);
                    }
                $this->success('添加成功!', url('Equipment/edit',array('equip_id'=>$equipModel->getLastId())));


            }
            $this->error('添加失败!请重新添加', url('Equipment/add'));
        }
    }

    //设备修改信息
    public function edit()
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

    //更新设备信息
    public function save()
    {
        $data = $this->request->param();
        //获取管理员对应的数据库

        $equipModel  =   $this->roles();

        if ($equipModel->save($data, ['equip_id' => $data['equip_id']])) {
            $this->success('保存成功!');
        }
        $this->error('更新失败!');

    }



    //数据导出Excel
    public function export()
    {
        // 设置表头
        $headAr = [
            '领用单位号', '领用单位名', '仪器编号', '使用单位号', '分类号', '资产类别', '仪器名称', '型号', '规格', '单价',
            '厂家', '现状', '出厂号', '出厂日期', '购置日期', '经手人', '领用人', '经费科目', '使用方向', '国别', '仪器来源', '入库时间', '变动日期', '单据号', '记帐人', '供货商', '存放地点', '说明', '备注', '国标分类号'
        ];
        //导出的字段
        $keyAr = [
            'receive_id', 'receive_name', 'equip_id', 'useunit_num', 'sort_id', 'asset_types', 'equip_name', 'model', 'size', 'price', 'factory', 'status', 'factory_num', 'factory_date', 'purchase_date',
            'dealer', 'employer', 'funding_subjects', 'use_direction', 'country', 'instrument_source', 'torage_time', 'change_date', 'document_num', 'bookkeeper', 'supplier', 'storage_location', 'description', 'remark', 'nation_num'];

        $export = new ExportService();
        $equipService = new EquipService();

        $param = $this->request->param();
        //如果筛选条件为空则不能打印
//        if(empty($param['receive_id']) || empty($param['start_time']) || empty($param['end_time'])|| empty($param['start_price'])  || empty($param['end_price']) || empty($param['keyword'])) {
//            $this->error("请输入筛选条件");
//        }
        //获取数据
        $data  = $equipService->equipAllList($param);
        //导出csv表格
        $csv = new Csv();
        //使用原生写法，thinkPHP查询方法会有额外数据，导致导出失败

        $titleList = [
            '领用单位号', '领用单位名', '仪器编号', '使用单位号', '分类号', '资产类别', '仪器名称', '型号', '规格', '单价',
            '厂家', '现状', '出厂号', '出厂日期', '购置日期', '经手人', '领用人', '经费科目', '使用方向', '国别', '仪器来源', '入库时间', '变动日期', '单据号', '记帐人', '供货商', '存放地点', '说明', '备注'
        ];

        $csv->put_csv($data, $titleList);
//        $export->exportExcel('设备信息', $data, $headAr, $keyAr);

    }

    //当前单位 只能看自己单位的信息
    public function unitManage()
     {
         $param = $this->request->param();

        $id =  cmf_get_current_admin_id();

       $role =  Db::table('cmf_user')->where('id','=',$id)->value('user_login');
       $param['receive_id'] = substr($role,2);
//        return  var_dump($param);


            //时间格式替换  Y-M-D  替换为 Y.M.D
            if (!empty($param['start_time'])){
                $param['start_time'] = str_replace('-','.',$param['start_time']);
                $param['end_time'] = str_replace('-','.',$param['end_time']);
            }
            //筛选的排序
            $orders = array(
                'receive_id' =>  "领用单位" ,
                'equip_id' =>  '仪器编号',
                'sort_id' => "分类号" );

            $equipService = new EquipService();
            //查询的数据
            $data  = $equipService->equipList($param);
            //共多少件设备；总共价值:多少元；
            $equipPrice =$equipService->equipPrice($param);

            $data->appends($param);
            $this->assign("equipPrice", $equipPrice);
            $this->assign("orders", $orders);
            $this->assign('order',        isset($param['order']) ? $param['order'] : '');
            $this->assign('receive_name', isset($param['receive_name']) ? $param['receive_name'] : '');
            $this->assign('receive_id',   isset($param['receive_id']) ? $param['receive_id'] : '');
            $this->assign('start_time',   isset($param['start_time']) ? $param['start_time'] : '');
            $this->assign('start_price',  isset($param['start_price']) ? $param['start_price'] : '');
            $this->assign('end_price',    isset($param['$end_price']) ? $param['$end_price'] : '');
            $this->assign('end_time',     isset($param['end_time']) ? $param['end_time'] : '');
            $this->assign('keyword',      isset($param['keyword']) ? $param['keyword'] : '');
            //数据渲染
            $this->assign("data", $data->items());
            //渲染分页
            $this->assign('page', $data->render());

            return $this->fetch();

        }

    public function unit_edit()
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

//报表打印功能
    public function prints()
    {
        $equip_id = $this->request->param('equip_id');

        $equipModel =    $this->roles();

        $mk = new MkModel();
        $dwModel = $this->dw();
        $data =$equipModel->where('equip_id',$equip_id)->find();


        $data['create_time'] = date('Y-m-d ');

        $data['sort'] = $mk->where('BJ4',intval($data['asset_types']))->where('BJ','分类号')->value('NR');

        $dwName= $dwModel->where('id',$data['useunit_num'])->value('name');

        $data['useunit_name']  = $dwName.'&nbsp;&nbsp;&nbsp;('.$data['useunit_num'].')';

        $data['instrument_source']  =$mk->where('BJ4',$data['instrument_source'])->where('BJ','经费科目')->value('NR');
        $data['instrument_source'] = substr( $data['instrument_source'],2);

        $data['country'] = $data['country'] ? '中国' : '';

        $data['total_size']  = $data['price'];

        $data['old_equip_id']= $data['equip_id'];
        if (intval($data['num']) !== 1){
           $data['equip_id']  = $data['eqid_Interval'];
           $data['total_size']  = $data['price'] * $data['num'];
       }

        $this->assign('data',$data);

        return $this->fetch();
    }

    //设备调拨
    public function transfer()
    {

        $user_id  =  cmf_get_current_admin_id();
        //获取角色id
        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');
        $transfer_to = $user_role == 2 ? 3 : 2;

        $role_id =  $this->roles();
        //获取调拨的设备号
        $equip_id =  $this->request->param('equip_id');

        $TransferModel = new TransferModel();

        $res =  $TransferModel->where('equip_id',$equip_id)->where('status',0)->find();

        if (!empty($res)){
            return $this->error('该设备已经在调转中或已经调转！');
        }

        //调转方
        $TransferModel->transfer_from   = $user_role;
        //转入方
        $TransferModel->transfer_to     =  $transfer_to;
        //设备编号
        $TransferModel->equip_id        =  $equip_id;
        //旧设备号
        $TransferModel->old_equip        = $equip_id;
        //调转时间
        $TransferModel->transfer_date   =  date("Y-m-d h:i:s");

       $res =   $TransferModel->save();
       if ($res){
           $this->success('调转成功！','equiptransfer/EquipTransfer/transferOut');
       }

        $this->error('调转失败！','equiptransfer/EquipTransfer/transferOut');
    }

}