<?php
namespace app\equipchange\controller;

use app\equip\model\BdkModel;
use app\equip\model\DwModel;
use app\equip\model\EquipmentModel;
use app\equipchange\service\EquipChangeService;
use cmf\controller\AdminBaseController;

class EquipChangeController extends AdminBaseController
{

//数据变动  单条变动首页
    public function index()
    {
        $dwModel = new DwModel();
        $equipModel = new EquipmentModel();
        $equipChangeService = new EquipChangeService();
        //页码id
        $id = $this->request->param('id');
        //搜索的设备编号id
        $equip_id = $this->request->param('equipId');

        //首次点进去的页面
        if (is_null($id) && is_null($equip_id)){

            $data= $equipModel->limit(1)->order('id','DESC' )->find();
            $data = $equipChangeService->dwChangeName($data);
            //上一页
            $pre = $equipChangeService->equip_pre($data['id']);
            //下一页
            $next = $equipChangeService->equip_next($data['id']);

            $this->assign("data",$data);
            $this->assign("pre" ,$pre);
            $this->assign("next",$next );
            //如果是搜索功能
        }elseif (is_null($id) && !is_null($equip_id)){

            $data= $equipModel->where('equip_id','=',$equip_id)->limit(1)->find();
            $data = $equipChangeService->dwChangeName($data);
            if ($data==null){
                    $this->error('没有此仪器编号记录',url('EquipChange/index'));
                }
            //上一页
            $pre = $equipChangeService->equip_pre($data['id']);
            //下一页
            $next = $equipChangeService->equip_next($data['id']);


            $this->assign("data", $data);
            $this->assign("equip_id", $equip_id);
            $this->assign("pre", $pre);
            $this->assign("next",$next );

        }else{
            //当前设备数据
            $data= $equipModel->where('id','=',$id)->limit(1)->order('id','DESC' )->find();
            $data = $equipChangeService->dwChangeName($data);
            //上一页//
            $pre = $equipChangeService->equip_pre($data['id']);
            //下一页
            $next = $equipChangeService->equip_next($data['id']);

            $this->assign("data", $data);
            $this->assign("pre", $pre);
            $this->assign("next",$next);

        }
        $mk = $equipChangeService->equipMk();

        $this->assign("instrument_source",  $mk['instrument_source']);//仪器来源
        $this->assign("use_direction",      $mk['use_direction'] );  //使用方向
        $this->assign("funding_subjects",   $mk['funding_subjects']);//经费科目
        $this->assign("status",             $mk['status']);          //现状

        return $this->fetch();
    }

//数据变动  单条变动更新
    public function update()
    {

        $BdkModel = new BdkModel();
        $equipModel = new EquipmentModel();
        $dwModel = new DwModel();

        $status = $this->request->param('status');

        $data = $this->request->param();
//        return var_dump($data);
        //判断是否有价格变动  如果价格 有变动 则更新价格 并且插入变动库
        if (!empty($data['change_price'])){
            $price =  $data['price'] + $data['change_price']; //主机库 变动后的价格
            //更新设备库 价格
            $result =  $equipModel->where('equip_id',$data['equip_id'])->update(['price'=>$price]);
            if ($result == 1){
                //生成变动记录
                unset($data['id']);
                //变动前的状态
                $data['status'] = $data['old_status'];
                $data['change_date'] = date('Y-m-d');
                //是价格变动 则转入单位要为空  转入单位要单独形成一条变动记录
                $data['transfer_unit'] = '';
                $data['receive_name'] = $dwModel->dwName($data['receive_id']);
                $BdkModel->create($data);
            }
        }
        //现状如果是 567d 则数据存到变动表中 并且在设备表中删除 如果是C 转入 则设备表中的状态改为B 变动表为C
        if ($status == 5 ||$status == 6 ||$status == 7 ||$status == "C" ||$status == "D"){
            $data = $this->request->param();
            $data['change_date'] = date('Y-m-d');
            $data['receive_name'] = $dwModel->dwName($data['receive_id']);
        //如果现状为C(转出)  则设备表信息的状态 需要改成B(转入) 并且  领用单位 改为 转入单位
            if ($status == "C"){
                $data['change_price'] ='';
                $BdkModel->save($data);
                $equipModel->where('equip_id','=',$data['equip_id'])->update(['status'=>'B','receive_id' =>$data['transfer_unit']]);
                $this->success('更新成功!');
            }
            //否则 如果是其他的状态则插入变动库 并且删除设备表中的信息
            $BdkModel->save($data);
            $equipModel->where('equip_id','=',$data['equip_id'])->delete();
            $this->success('更新成功!');
        }
        //如果 价格和现状 567d 没有变动  则单单更新数据
        $data['change_date'] = date('Y-m-d');
        unset($data['price']);
        $equipModel->save($data, ['equip_id' => $data['equip_id']]);
        $this->success('更新成功!', url('EquipChange/index',array('equipId'=>$data['equip_id'])));

    }

    //批量单价增减页面
    public function BatchPriceEdit()
    {
        $param = $this->request->param();
        $equipChangeService = new EquipChangeService();
        $orders = $equipChangeService->sort();

        if(!empty($param['receive_id']) || !empty($param['start_time'])|| !empty($param['start_price'])|| !empty($param['keyword'])){
            $data = $equipChangeService->equipList($param);
            $equipId = $equipChangeService->equipId($param);
            $equipPrice =$equipChangeService->equipPrice($param);

            $data->appends($param);
            //数据渲染
            $this->assign("data", $data->items());
            $this->assign("equipId", $equipId);
            $this->assign("equipPrice", $equipPrice);
            //渲染分页
            $this->assign('page', $data->render());
        }

        $this->assign("orders", $orders);
        $this->assign('order',        isset($param['order']) ? $param['order'] : '');
        $this->assign('receive_id', isset($param['receive_id']) ? $param['receive_id'] : '');
        $this->assign('receive_name', isset($param['receive_name']) ? $param['receive_name'] : '');
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time',   isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('start_price', isset($param['start_price']) ? $param['start_price'] : '');
        $this->assign('end_price', isset($param['end_price']) ? $param['end_price'] : '');
//        $this->assign('equip_id_from', isset($param['equip_id_from']) ? $param['equip_id_from'] : '');
//        $this->assign('equip_id_to', isset($param['equip_id_to']) ? $param['equip_id_to'] : '');
        $this->assign('keyword',   isset($param['keyword']) ? $param['keyword'] : '');

        return $this->fetch();

    }

    //批量单价增减修改
    public function edit()
    {
        $param =  $this->request->param();
//    return var_dump($param);
        $equipChangeService = new EquipChangeService();
        $equipModel = new EquipmentModel();
        $bdkModel   =  new BdkModel();
        $dwModel   =  new DwModel();
        //需要修改的所有设备信息
        $data = $equipChangeService->equipId($param);


        $change_date = $param['change_date'];
        $where        = $param['where'];
        $change_price = $param['change_price'];

        //修改的条数
        $len = count($data);
        //循环  添加变动库信息 和更新主机设备库设备价格信息
        for ($i=0;$i< $len;$i++){

        //更新主机设备库设备价格信息
          $price =  $data[$i]['price'] + $change_price;
          $equipModel->where('equip_id',$data[$i]['equip_id'])->update(['price'=>$price]);
        //添加变动库信息
          unset($data[$i]['id']);
          $data[$i]['where'] = $where;
          $data[$i]['change_date'] = $change_date ;
          $data[$i]['change_price'] = $change_price;
          $data[$i]['receive_name'] = $dwModel->dwName($data[$i]['receive_id']);
          $bdkModel->create($data[$i]);
        }
        $this->success('更新成功!', url('EquipChange/BatchPriceEdit',
            array('receive_name'=>$param['receive_name'],'receive_id'=>$param['receive_id'],
                'start_time'=>$param['start_time'],'end_time'=>$param['end_time'],
                'start_price'=>$param['start_price'],'end_price'=>$param['end_price'],
                'keyword'=>$param['keyword'],)));}

    //批量现状修改
    public function BatchStatusEdit()
    {

        $param = $this->request->param();

        $equipChangeService = new EquipChangeService();
        $orders = $equipChangeService->sort();

        if(!empty($param['receive_id']) || !empty($param['start_time'])|| !empty($param['start_price'])|| !empty($param['keyword'])){
//            return  var_dump($param);
            $data = $equipChangeService->equipList($param);
            $equipId = $equipChangeService->equipId($param);
            $equipPrice =$equipChangeService->equipPrice($param);

            $data->appends($param);
            //数据渲染

            $this->assign("data", $data->items());
            $this->assign("equipId", $equipId);
            $this->assign("equipPrice", $equipPrice);
            //渲染分页
            $this->assign('page', $data->render());
        }
        $this->assign("orders", $orders);
        $this->assign('order',        isset($param['order']) ? $param['order'] : '');
        $this->assign('receive_name', isset($param['receive_name']) ? $param['receive_name'] : '');
        $this->assign('receive_id', isset($param['receive_id']) ? $param['receive_id'] : '');
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('start_price', isset($param['start_price']) ? $param['start_price'] : '');
        $this->assign('end_price', isset($param['end_price']) ? $param['end_price'] : '');
//        $this->assign('equip_id_from', isset($param['equip_id_from']) ? $param['equip_id_from'] : '');
//        $this->assign('equip_id_to', isset($param['equip_id_to']) ? $param['equip_id_to'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');

        return $this->fetch();

    }

    //批量现状修改
    public function statusEdit()
    {
        $param =  $this->request->param();

        $equipChangeService = new EquipChangeService();
        $equipModel = new EquipmentModel();
        $bdkModel   =  new BdkModel();
        $dwModel   =  new DwModel();
        //需要修改的所有设备信息
        $data = $equipChangeService->equipId($param);
        //变动时间
        $change_date = $param['change_date'];
        //变动去向
        $where        = $param['where'];
        //变动状态
        $status = $param['status'];

        //修改的条数
        $len = count($data);
        //判断变动的状态是多少  如果是 567D 则执行
        if ($status =='5' ||$status =='6' ||$status =='7' ||$status =='D' ){
            //循环  添加变动库信息 和删除主机设备库信息
            for ($i=0;$i< $len;$i++){
                //添加变动库信息
                unset($data[$i]['id']);
                $data[$i]['where'] = $where;
                $data[$i]['change_date'] = $change_date ;
                $data[$i]['status'] = $status;
                $data[$i]['receive_name'] = $dwModel->dwName($data[$i]['receive_id']);
                $bdkModel->create($data[$i]);

                $equipModel->where('equip_id',$data[$i]['equip_id'])->delete();
            }
            $this->success('变动成功!', url('EquipChange/BatchStatusEdit',
                array('receive_name'=>$param['receive_name'],'receive_id'=>$param['receive_id'],
                    'start_time'=>$param['start_time'],'end_time'=>$param['end_time'],
                    'start_price'=>$param['start_price'],'end_price'=>$param['end_price'],
                    'keyword'=>$param['keyword'],)));
        }
        //如果是其他状态 则只更新设备表的信息
            for ($i=0;$i< $len;$i++){
                //添加变动库信息
                unset($data[$i]['id']);
                $data[$i]['change_date'] = $change_date ;
                $equipModel->where('equip_id',$data[$i]['equip_id'])->update(['status'=>$status]);
            }
        $this->success('变动成功!', url('EquipChange/BatchStatusEdit',
            array('receive_name'=>$param['receive_name'],'receive_id'=>$param['receive_id'],
                'start_time'=>$param['start_time'],'end_time'=>$param['end_time'],
                'start_price'=>$param['start_price'],'end_price'=>$param['end_price'],
                'keyword'=>$param['keyword'],)));
    }

    //单位调整（转入/转出）
    public function BatchUnitEdit()
    {
        $param = $this->request->param();

        $equipChangeService = new EquipChangeService();
        $orders = $equipChangeService->sort();
        if(!empty($param['receive_id']) || !empty($param['start_time']) || !empty($param['start_price'])|| !empty($param['keyword'])){

            $data = $equipChangeService->equipList($param);
            $equipId = $equipChangeService->equipId($param);
            $equipPrice =$equipChangeService->equipPrice($param);

            $data->appends($param);
            //数据渲染
            $this->assign("data", $data->items());
            $this->assign("equipId", $equipId);
            $this->assign("equipPrice", $equipPrice);
            //渲染分页
            $this->assign('page', $data->render());
        }

        $this->assign("orders", $orders);
        $this->assign('order',        isset($param['order']) ? $param['order'] : '');
        $this->assign('receive_name', isset($param['receive_name']) ? $param['receive_name'] : '');
        $this->assign('receive_id', isset($param['receive_id']) ? $param['receive_id'] : '');
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('start_price', isset($param['start_price']) ? $param['start_price'] : '');
        $this->assign('end_price', isset($param['end_price']) ? $param['end_price'] : '');
        $this->assign('transfer_unitname', isset($param['transfer_unitname']) ? $param['transfer_unitname'] : '');
//        $this->assign('equip_id_from', isset($param['equip_id_from']) ? $param['equip_id_from'] : '');
//        $this->assign('equip_id_to', isset($param['equip_id_to']) ? $param['equip_id_to'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');

        return  $this->fetch();
    }

    //单位调整（转入/转出）修改
    public function UnitEdit()
    {
        $param =  $this->request->param();

        $equipChangeService = new EquipChangeService();
        $equipModel = new EquipmentModel();
        $bdkModel   =  new BdkModel();
        $dwModel   =  new DwModel();

        //需要修改的所有设备信息
        $data = $equipChangeService->equipId($param);

        $change_date   = $param['change_date'];
        //变动转入单位
        $transfer_unit = $param['transfer_unit'];
        $where         = $param['where'];
        //修改的条数
        $len = count($data);
        //循环  添加变动库信息 和更新主机设备库设备价格信息
        for ($i=0;$i< $len;$i++){
            //添加变动库信息
            unset($data[$i]['id']);
            $data[$i]['where'] = $where;
            $data[$i]['change_date']  = $change_date;
            $data[$i]['receive_name'] = $dwModel->dwName($data[$i]['receive_id']);
            $data[$i]['receive_id']   =$transfer_unit;
            $data[$i]['status']   ='C';
            $bdkModel->create($data[$i]);
            $equipModel->where('equip_id','=',$data[$i]['equip_id'])->update(['status'=>'B','receive_id'=> $transfer_unit ]);

        }
        $this->success('变动成功!', url('EquipChange/BatchUnitEdit',
            array('receive_name'=>$param['receive_name'],'receive_id'=>$param['receive_id'],'start_time'=>$param['start_time'],'end_time'=>$param['end_time'],
               'transfer_unit'=>$param['transfer_unit'],'keyword'=>$param['keyword'],)));
    }

    //变动恢复
    public function changeRecovery()
    {

        $bdModel = new BdkModel();
        $dwModel = new DwModel();


        $equipChangeService = new EquipChangeService();

        $id = $this->request->param('id');
        $equipid = $this->request->param('equip_id');
        //搜索的设备编号id
        $equip_id = $this->request->param('equipId');
        //首次点进去的页面
        if (is_null($id) && is_null($equip_id)){

            $data= $bdModel->alias('b')->join('dw d','b.transfer_unit =d.id')
                ->field('d.name as transfer_unit_name,b.*')->order('b.change_date','desc' )->find();

            $data =$equipChangeService->dwName($data);
            //上一页
            $pre = $equipChangeService->bdk_pre($data);
            //下一页
            $next = $equipChangeService->bdk_next($data);

            //变动总次数
            $count = $equipChangeService->bdCount(is_null($data['equip_id']) ? '0' :$data['equip_id']);
            $this->assign("count", $count);
            $this->assign("data",$data);
            $this->assign("pre" ,$pre);
            $this->assign("next",$next);

        //如果是搜索功能
        }elseif (is_null($id) && !is_null($equip_id)){

            $data= $bdModel->where('equip_id','=',$equip_id)->order('change_date','DESC' )->find();
            if ($data==null){
                $this->error('没有此仪器编号记录',url('EquipChange/changeRecovery'));
            }
            $data =$equipChangeService->dwName($data);

            $count = $equipChangeService->bdCount(is_null($data['equip_id']) ? '0' :$data['equip_id']);

                //上一页
                $pre = $equipChangeService->bdk_pre($data);
                //下一页
                $next = $equipChangeService->bdk_next($data);

                $this->assign("data", $data);
                $this->assign("equip_id", $equip_id);
                $this->assign("count", $count);
                $this->assign("pre", $pre);
                $this->assign("next",$next );

        }else{
            //当前设备数据
            $data= $bdModel->where('equip_id','=',$equipid)->where('id','=',$id)->order('change_date','DESC' )->find();
            $data =$equipChangeService->dwName($data);

           //上一页
            $pre = $equipChangeService->bdk_pre($data);
            //下一页
            $next = $equipChangeService->bdk_next($data);

            $count = $equipChangeService->bdCount(is_null($data['equip_id']) ? '0' :$data['equip_id']);
            $this->assign("count", $count);
            $this->assign("data", $data);
            $this->assign("pre", $pre);
            $this->assign("next",$next);

        }
        $mk = $equipChangeService->equipMk();

        $this->assign("instrument_source",  $mk['instrument_source']);//仪器来源
        $this->assign("use_direction",      $mk['use_direction'] );  //使用方向
        $this->assign("funding_subjects",   $mk['funding_subjects']);//经费科目
        $this->assign("status",             $mk['status']);          //现状

        return $this->fetch();
    }

    //设备信息变动恢复
    public function Recovery()
    {
        $bdModel = new BdkModel();
        $equipModel = new EquipmentModel();

        $param = $this->request->param();
        //单价变动 恢复
        if (!empty($param['change_price'])) {
            //判断设备表中有没有这个设备信息
           $data =  $equipModel->where('equip_id', '=', $param['equip_id'])->find();
           //如果设备表中有这条记录 则可以进行价格变动恢复 恢复流程->主机设备库单价还原 -> 变动库删除此次的变动记录
           if ($data){
               $price = $data['price'] - $param['change_price'];
                $equipModel->where('equip_id', '=', $param['equip_id'])->update(['price'=>$price]);
                $bdModel->where("id",'=',$param['id'])->delete();
               $this->success('变动成功！',url('EquipChange/changeRecovery'));
           }else{
               $this->error('仪器在主机库中不存在！可能已经报废、丢失、调出、或退库，无法恢复');
           }
              $this->error('变动失败！请重新尝试！');
        }

        //如果变动恢复状态为567 D   则直接恢复到设备库中 并且变动库中记录删除
       if ($param['status']  == 5 || $param['status']  == 6 || $param['status']  == 7 || $param['status']  == "D"){

           $data =$bdModel->where('equip_id','=',$param['equip_id'])->order('id','DESC' )->find()->toArray();
           $data['status'] ='1';
           //设备信息在变动库的id
           $bdkId = $data['id'];
           unset($data['id']);
           $equipModel->allowField(true)->save($data);
            $bdModel->where('id' ,'=',$bdkId)->delete();
           $this->success('变动成功！',url('EquipChange/changeRecovery'));
       }

        //单位转出操作恢复
       if($param['status'] == 'C'){
           //恢复的单位id
           $uint_id = $bdModel->where('id','=',$param['id'])->value('receive_id');
           $equipModel->where('equip_id', '=', $param['equip_id'])->update(['receive_id'=>$uint_id,'status'=>1]);
           $bdModel->where('id','=',$param['id'])->delete();
           $this->success('变动成功！',url('EquipChange/changeRecovery'));
       }
        $this->error('变动失败！请重新尝试！');

    }
}