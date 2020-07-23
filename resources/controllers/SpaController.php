<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
class SpaController extends Controller
{
    public function __invoke() {

        return view('index');
    }

    /**
     * 左侧菜单
     *
     * @description
     * @example
     * @author doujinya 401298791@email.com
     * @date 2020-07-06
     *
     * @return void
     */
    public function getNav(){
        $navList = [
            [
                'name'=>'首页',
                'icon'=>"/images/icon/nav_icon_home_normal@2x.png",
                'tagIcon'=> "/images/icon/nav_icon_home_prsessed@2x.png",
                'children'=> [],
                'isShow'=> false
            ],
            [
                'name'=> '个人中心',
                'icon'=> "/images/icon/index_top_icon_xiaoxi.png",
                'tagIcon'=> "/images/icon/nav_icon_geren_pressed@2x.png",
                'children'=> [
                    '考勤中心',
                    '我的费用',
                    '消息提醒',
                    '我的人事'
                ],
                'isShow'=> false
            ],
            [
                'name'=> '流程中心',
                'icon'=> "/images/icon/nav_icon_liucheng_normal@2x.png",
                'tagIcon'=> "/images/icon/nav_icon_liucheng_pressed@2x.png",
                'children'=> [
                    '考勤中心1',
                    '我的费用2',
                    '消息提醒3',
                    '我的人事4'
                ],
                'isShow'=> false
            ],
            [
                'name'=> '商机管理',
                'icon'=> "/images/icon/nav_icon_shangji_normal@2x.png",
                'tagIcon'=> "/images/icon/nav_icon_shangji__pressed@2x.png",
                'children'=> [],
                'isShow'=> false
            ],
            [
                'name'=> '项目管理',
                'icon'=> "/images/icon/nav_icon_xiangmu_normal@2x.png",
                'tagIcon'=> "/images/icon/nav_icon_xiangmu_pressed@2x.png",
                'children'=> [],
                'isShow'=> false
            ],
            [
                'name'=> '合同管理',
                'icon'=> "/images/icon/nav_icon_hetong_normal@2x.png",
                'tagIcon'=> "/images/icon/nav_icon_hetong_pressed@2x.png",
                'children'=> [],
                'isShow'=> false
            ],
            [
                'name'=> '工时管理',
                'icon'=>  "/images/icon/nav_icon_gongshi_normal@2x.png",
                'tagIcon'=>  "/images/icon/nav_icon_gongshi_pressed@2x.png",
                'children'=>  [],
                'isShow'=>  false
            ],
            [
                'name'=>  '资料库',
                'icon'=>  "/images/icon/nav_icon_ziliao_normal@2x.png",
                'tagIcon'=>  "/images/icon/nav_icon_ziliao_pressed@2x.png",
                'children'=>  [],
                'isShow'=>  false
            ],
            [
                'name'=> '我的同事',
                'icon'=>  "/images/icon/nav_icon_tongshi_normal@2x.png",
                'tagIcon'=>  "/images/icon/nav_icon_tongshi_pressed@2x.png')}",
                'children'=>  [],
                'isShow'=> false
            ],
        ];
        return $navList;
    }

    public function selectUser(Request $request){

        $query = $request->query();
        $arr = User::where('name','like','%'.$query['name'].'%')->get();
        foreach($arr as $k=>&$v){
            $v['value'] = $v['name'];
        }
        return $this->ok($arr,true);
    }
}
