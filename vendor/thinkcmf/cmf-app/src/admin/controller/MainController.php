<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\equiptransfer\model\TransferModel;
use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\Menu;

class MainController extends AdminBaseController
{

    /**
     *  后台欢迎页
     */
    public function index()
    {
        $user_id  =  cmf_get_current_admin_id();

        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');

        $transferModel = New TransferModel();


        //判断是否有待转入设备
        $notify =  $transferModel->where('status','0')->where('transfer_to',$user_role)->select()->toArray();

        $this->assign('notifys', $notify);

        return $this->fetch();
    }


    public function dashboardWidget()
    {
        $dashboardWidgets = [];
        $widgets          = $this->request->param('widgets/a');
        if (!empty($widgets)) {
            foreach ($widgets as $widget) {
                if ($widget['is_system']) {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 1]);
                } else {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 0]);
                }
            }
        }

        cmf_set_option('admin_dashboard_widgets', $dashboardWidgets, true);

        $this->success('更新成功!');

    }

}
