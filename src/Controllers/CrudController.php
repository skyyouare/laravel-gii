<?php

namespace Skyyouare\Gii\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Continue_;
use ReflectionClass;
use Skyyouare\Gii\Models\ControllerGenerate;

class CrudController extends Controller
{
    public function index(Request $request)
    {
        $response = [
            'files' => [],
            'model_lists'=> $this->getModelLists()
        ];
        try {
            // preview
            if ($request->method() == 'POST') {

                $modelClassName      = trim($request->post('model_class_name'));
                $controller_namespace = trim($request->post('controller_namespace'));
                $controller_name = trim($request->post('controller_name'));
                $controllerClassName = $controller_namespace.'\\'.$controller_name;

                $fileList = (new ControllerGenerate($controllerClassName, $modelClassName))->preview();

                $response['files'] = $fileList;

                // generate file
                if (!is_null($request->post('generate'))) {

                    $waitingFiles = $request->post('waitingfiles');
                    // exception
                    if (!$waitingFiles) {
                        $response['alert'] = [
                            'type'    => 'error',
                            'message' => 'Please select items first!'
                        ];
                    }
                    // generate
                    $response['generate_info'] = ControllerGenerate::generateFile($fileList, $waitingFiles);
                }
            }
        } catch (\Exception $exception) {
            $response['alert'] = [
                'type'    => 'error',
                'message' => $exception->getMessage()
            ];
        }

        $viewPath = 'gii_views::crud';
        return response()->view($viewPath, $response);
    }

    private function getModelLists(){
        $model_base_path_arr = config('gii.model_base_path');
        $arr = [];
        foreach($model_base_path_arr as $path => $namespace){
            $base_dir = base_path($path);
            if(file_exists($base_dir)){
                // 扫描目录下的所有文件
                $filename = scandir($base_dir);
                foreach($filename as $k=>$v){
                    // 跳过两个特殊目录   continue跳出循环
                    if($v=="." || $v==".."){continue;}
                    $class_name = substr($v,0,strpos($v,"."));
                    //忽略的控制器
                    if(empty($class_name)) continue;
                    if(strrchr($class_name,'Model')=='Model') continue;
                    $arr[] = $namespace.'\\'.$class_name;
                }
            }
        }
        return $arr;
    }
}
