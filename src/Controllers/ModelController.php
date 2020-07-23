<?php

namespace Skyyouare\Gii\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Skyyouare\Gii\Models\ModelGenerate;

class ModelController extends Controller
{

    public function index(Request $request)
    {
        $response = ['files'=>[]];

        try {
            $response['table_list'] = ModelGenerate::getTableList();
            // preview
            if ($request->method() == 'POST') {

                $tableName       = trim($request->post('table_name'));
                $modelClassName  = trim($request->post('model_class_name'));
                $parentClassName = trim($request->post('parent_class_name'));
                $primary_key     = trim($request->post('primary_key'));
                $create_at       = trim($request->post('create_at'));
                $update_at       = trim($request->post('update_at'));

                $obj = new ModelGenerate($tableName, $modelClassName, $parentClassName,$primary_key,$create_at,$update_at);
                //fileList
                $fileList = $obj->preview();
                $response['files'] = $fileList;

                //fields
                $fields = $obj->attributes();
                $response['fields'] = $fields;

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
                    $response['generate_info'] = ModelGenerate::generateFile($fileList, $waitingFiles);
                }
            }
        }catch (\Exception $exception) {
            $response['alert'] = [
                'type'    => 'error',
                'message' => $exception->getMessage()
            ];
        }

        $viewPath = 'gii_views::model';
        return response()->view($viewPath, $response);
    }


    public function fields(Request $request){
        $tableName       = trim($request->post('table_name'));
        $attributes = (new ModelGenerate($tableName, '1', '1','1','1','1'))->attributes();
        return $attributes;
    }
}
