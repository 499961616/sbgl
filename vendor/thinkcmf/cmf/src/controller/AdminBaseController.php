<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use app\equip\model\BdkModel;
use app\equip\model\DwModel;
use app\equip\model\EquipmentModel;
use app\equip\model\XzcBdkModel;
use app\equip\model\XzcDwModel;
use app\equip\model\XzcEquipmentModel;
use think\Db;

class AdminBaseController extends BaseController
{

    protected function initialize()
    {
        // 监听admin_init
        hook('admin_init');
        parent::initialize();
        $sessionAdminId = session('ADMIN_ID');
        if (!empty($sessionAdminId)) {
            $user = Db::name('user')->where('id', $sessionAdminId)->find();

            if (!$this->checkAccess($sessionAdminId)) {
                $this->error("您没有访问权限！");
            }
            $this->assign("admin", $user);
        } else {
            if ($this->request->isPost()) {
                $this->error("您还没有登录！", url("admin/public/login"));
            } else {
                return $this->redirect(url("admin/Public/login"));
            }
        }
    }

    public function _initializeView()
    {
        $cmfAdminThemePath    = config('template.cmf_admin_theme_path');
        $cmfAdminDefaultTheme = cmf_get_current_admin_theme();

        $themePath = "{$cmfAdminThemePath}{$cmfAdminDefaultTheme}";

        $root = cmf_get_root();

        //使cdn设置生效
        $cdnSettings = cmf_get_option('cdn_settings');
        if (empty($cdnSettings['cdn_static_root'])) {
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$root}/{$themePath}",
                '__STATIC__'   => "{$root}/static",
                '__WEB_ROOT__' => $root
            ];
        } else {
            $cdnStaticRoot  = rtrim($cdnSettings['cdn_static_root'], '/');
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$cdnStaticRoot}/{$themePath}",
                '__STATIC__'   => "{$cdnStaticRoot}/static",
                '__WEB_ROOT__' => $cdnStaticRoot
            ];
        }

        config('template.view_base', WEB_ROOT . "$themePath/");
        config('template.tpl_replace_string', $viewReplaceStr);
    }

    /**
     * 初始化后台菜单
     */
    public function initMenu()
    {
    }

    /**
     *  检查后台用户访问权限
     * @param int $userId 后台用户id
     * @return boolean 检查通过返回true
     */
    private function checkAccess($userId)
    {
        // 如果用户id是1，则无需判断
        if ($userId == 1) {
            return true;
        }

        $module     = $this->request->module();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        $rule       = $module . $controller . $action;

        $notRequire = ["adminIndexindex", "adminMainindex"];
        if (!in_array($rule, $notRequire)) {
            return cmf_auth_check($userId);
        } else {
            return true;
        }
    }


    /**
     *  判断用户权限
     */
    public function roles()
    {

        $user_id  =  cmf_get_current_admin_id();

        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');
        //判断是那个管理员   就对应哪个数据库
        if ($user_role == 2 || $user_role == 4){
            //是实验室管理员 就对应这实验室的数据库模型
          return  $this->equipModel  = new EquipmentModel();

        }elseif ($user_role ==3 || $user_role == 5){
            //是行政处管理员 就对应行政处的数据库模型

            return  $this->equipModel  = new XzcEquipmentModel();
        }else{
            //  默认是实验室的模型
            return  $this->equipModel  = new EquipmentModel();
        }
     }

    /**
     *  对方的  数据库模型
     */
    public function otherRole()
    {

        $user_id  =  cmf_get_current_admin_id();

        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');
        //判断是那个管理员   就对应哪个数据库
        if ($user_role == 2 || $user_role == 4){
            //是实验室管理员 就对应这实验室的数据库模型
            return  $this->equipModel  = new XzcEquipmentModel();


        }elseif ($user_role ==3 || $user_role == 5){
            //是行政处管理员 就对应行政处的数据库模型
            return  $this->equipModel  = new EquipmentModel();

        }else{
            //  默认是实验室的模型
            return  $this->equipModel  = new EquipmentModel();
        }
    }





    /**
     *  判断用户权限 来实现单位选择
     */

    public function dw()
    {
        $user_id  =  cmf_get_current_admin_id();

        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');
        //判断是那个管理员   就对应哪个数据库
        if ($user_role == 2 || $user_role == 4){

            //是实验室管理员 就对应这实验室的数据库模型
            return  $this->dwModel  =  new DwModel();

        }elseif ($user_role ==3 || $user_role == 5){

            //是行政处管理员 就对应行政处的数据库模型
            return  $this->dwModel  =  new XzcDwModel();
        }
        //  默认是实验室的模型
        return $this->dwModel  =  new DwModel();

    }


    /**
     *  判断用户权限 来实现对方的单位库
     */

    public function otherDw()
    {
        $user_id  =  cmf_get_current_admin_id();

        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');
        //判断是那个管理员   就对应哪个数据库
        if ($user_role == 2 || $user_role == 4){


            return  $this->dwModel  =  new XzcDwModel();

        }elseif ($user_role ==3 || $user_role == 5){


            return  $this->dwModel  =  new DwModel();

        }
        //  默认是实验室的模型
        return $this->dwModel  =  new DwModel();

    }



    /**
     *  判断用户权限 来实现变动库数据库选择
     */

    public function bdk()
    {
        $user_id  =  cmf_get_current_admin_id();

        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');
        //判断是那个管理员   就对应哪个变动库数据库
        if ($user_role == 2 || $user_role == 4){
            //是实验室管理员 就对应这实验室的变动库数据库模型
            return    $this->BdkModel  =  new BdkModel();

        }elseif ($user_role ==3 || $user_role == 5){
            //是行政处管理员 就对应行政处的变动库数据库模型
            return  $this->BdkModel  =  new XzcBdkModel();

        }else{
            //  默认是实验室的变动库模型
            return  $this->BdkModel  = new BdkModel();
        }
    }

    /**
     *  判断用户权限 对方的变动数据模型
     */

    public function otherBdk()
    {
        $user_id  =  cmf_get_current_admin_id();

        $user_role = Db::table('cmf_role_user')->where('user_id',$user_id)->value('role_id');
        if ($user_role == 2 || $user_role == 4){
            return  $this->BdkModel  =  new XzcBdkModel();

        }elseif ($user_role ==3 || $user_role == 5){
            return    $this->BdkModel  =  new BdkModel();

        }else{
            return  $this->BdkModel  = new BdkModel();
        }
    }


}