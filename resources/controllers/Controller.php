<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

     /**
     * 返回成功
     * 返回成功
     *
     * @param array $data
     * @return array
     */
    public function ok($data = array(),$count = false) {
        $return         = [];
        $return['code'] = 1000;
        if($count){
            $return['count'] = count($data);
        }
        $return['data'] = $data;

        return response()->json($return)->withHeaders([
            'Access-Control-Allow-Origin' => '*'
        ]);
    }

    /**
     * 返回失败
     * 返回失败
     *
     * @param string $msg
     * @param int    $code
     * @return array
     */
    public function no($msg, $code = 1001) {
        $return         = [];
        //1000成功;1001失败;1002缺少参数; 1003参数错误;
        $return['code'] = $code;
        $return['msg']  = $msg;

        return response()->json($return)->withHeaders([
            'Access-Control-Allow-Origin' => '*'
        ]);
    }
}
