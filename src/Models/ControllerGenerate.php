<?php
/**
 * Created by PhpStorm.
 * User: jaysun
 * Date: 2019-11-21
 * Time: 17:24
 */

namespace Skyyouare\Gii\Models;

use Illuminate\Support\Facades\DB;
/**
 * Generate CRUD
 */
class ControllerGenerate extends BaseGenerate
{


    /**
     * @var mixed
     */
    protected $controllerClass;
    /**
     * @var string
     */
    protected $controllerNamespace;
    /**
     * @var
     */
    protected $controllerClassName;

    /**
     * @var mixed
     */
    protected $controllerClassMini;

    /**
     * @var mixed
     */
    protected $modelClass;
    /**
     * @var
     */
    protected $modelClassName;
    /**
     * @var string
     */
    protected $modelNamespace;

    /**
     * @var
     */
    protected $model;
    /**
     * @var
     */
    protected $modelKeyName;

     /**
     * @var
     */
    protected $validateClass;

     /**
     * @var
     */
    protected $validateClassName;


    /**
     * Route mapping controller method
     *
     * @var array
     */
    protected $actions = [
        'list'         => 'getList',
        'detail'       => 'getDetail',
        'save'         => 'save',
        'delete'       => 'delete',
        'batch_delete' => 'batchDelete',
    ];


    /**
     * @var bool|string
     */
    protected $project;

    protected $m2cPath;

    protected $m2Path;

    protected $m2;


    /**
     * ControllerBusiness constructor.
     * @param $controllerClassName
     * @param $modelClassName
     */
    public function __construct($controllerClassName, $modelClassName)
    {
        foreach (func_get_args() as $k => $v) {
            if (!$v) {
                throw new \Exception('params is empty!');
            }
        }
        $this->controllerClassName = $controllerClassName;
        $this->modelClassName      = $modelClassName;

        $this->model = new $modelClassName;
        // keyname
        $this->modelKeyName = $this->model->getKeyName();

        // controller
        $controllerClassNameArr    = explode('\\', $controllerClassName);
        $this->controllerClass     = end($controllerClassNameArr);
        $this->controllerNamespace = trim(substr($controllerClassName, 0, strrpos($controllerClassName, '\\')), '\\');

        // model
        $modelClassNameArr    = explode('\\', $modelClassName);
        $this->modelNamespace = trim(substr($modelClassName, 0, strrpos($modelClassName, '\\')), '\\');
        $this->modelClass     = end($modelClassNameArr);

        // validate
        $this->validateClass     = $this->modelClass . 'Requests';
        $this->validateClassName = str_replace('Models', 'Http\\Requests', $this->modelNamespace) . '\\' . $this->validateClass;
        $this->controllerClassMini = str_replace('controller', '', strtolower($this->controllerClass));

        // /account-book/api/manage/user/list
        $urlPath = parse_url(config('app.url'))['path'] ?? '';
        // project
        $this->project = trim(substr($urlPath, strpos($urlPath, '/')),'/');

        $this->m2cPath = $this->getM2cPath();

        $this->m2     = $this->getM2();
        $this->m2Path = $this->getM2Path();
    }

    /**
     * @param $tableName
     * @param $modelClassName
     * @param $parentClassName
     * @return array
     * @throws \ReflectionException
     */
    public function preview()
    {

        $ret   = [];
        $ret[] = $this->handleApiRoute();
        $ret[] = $this->handleWebRoute();
        $ret[] = $this->handleController();
        $ret[] = $this->handleViewsList();
        $ret[] = $this->handleViewsEdit();
        $ret[] = $this->handleViewsCreate();
        $ret[] = $this->handleViewsDetail();
        // $ret[] = $this->handleViewsLayoutDefault();


        return $ret;
    }

    /**
     * @return array
     */
    private function handleController()
    {
        $stubFile = $this->pathJoin([__DIR__, '../../resources', 'stubs', 'controller.stub']);

        // model
        $modelClass     = $this->modelClass . 'Model';
        $modelClassName = $this->modelNamespace . '\\' . $modelClass;

        $fields = [
            '{{model_class_name}}'     => $modelClassName,
            '{{controller_namespace}}' => $this->controllerNamespace,
            '{{controller_class}}'     => $this->controllerClass,
            '{{model_keyname}}'        => $this->modelKeyName,
            '{{model_class}}'          => $modelClass,
            '{{validate_class}}'        => $this->validateClass,
            '{{validate_class_name}}'    => $this->validateClassName,
        ];

        return self::handleFile($this->controllerNamespace, $this->controllerClass, $fields, $stubFile);
    }


    /**
     * @return array
     */
    private function handleViewsList()
    {
        $stubFile = $this->pathJoin([__DIR__, '../../resources', 'stubs','pages', 'list.vue.stub']);

        // Get attributes + key
        $attributes = $this->model->getAttributes();

        // table
        $cols = array_merge([$this->modelKeyName], array_keys($attributes));
        // comments
        $comments = $this->getTableComments();
        // type
        $types = $this->getTableType();
        $tableCols    = '';
        $searchFields = '';
        $hsearchFields = '';
        $table_header = '';
        $table_body = '';
        $table_base_offset = 20;
        $table_in_offset = $table_base_offset + 4;
        foreach ($cols as $k=> $col) {
            $tableCols    .= "                    {title:'{$col}', key:'{$col}'},\n";
            $searchFields .= "                    '{$col}',\n";
            // comment
            $comment = $this->getComment($comments, $col);
            if($k % 2 == 0){
                $hsearchFields .= "                    [\n";
            }
            //根据类型
            $type = $this->getType($types, $col);

            $hsearchFields .= $this->getHsearchStrByType($col,$comment,$type);

            if($k%2 == 1 || $k ==count($cols)-1){
                $hsearchFields .= "                    ],\n";
            }
            //table_header 超过7列注释代码
            if($k <= 7){
                $style_header = '';
                $style_body = '';
                if($k==0){
                    $style_header = " style=\"flex:1;\"";
                    $style_body = " style=\"flex:1; text-align: center;\"";
                }

                $table_header .= "\n".str_repeat(" ",$table_base_offset)."<div class=\"td\"".$style_header.">".$comment."</div>";
                $table_body   .= "\n".str_repeat(" ",$table_base_offset)."<div class=\"td\"".$style_body.">";
                $table_body   .= "\n".str_repeat(" ",$table_in_offset)."<p>{{items.".$col."}}</p>";
                $table_body   .= "\n".str_repeat(" ",$table_base_offset)."</div>";
            }else{
                $table_header .= "\n".str_repeat(" ",$table_base_offset)."<!-- <div class=\"td\">".$comment."</div> -->";
                $table_body   .= "\n".str_repeat(" ",$table_base_offset)."<!-- <div class=\"td\">";
                $table_body   .= "\n".str_repeat(" ",$table_in_offset)."<p>{{items.".$col."}}</p>";
                $table_body   .= "\n".str_repeat(" ",$table_base_offset)."</div> -->";
            }


        }
        $fields = [
            '{{table_name}}'             => $this->getTableName(),
            '{{model_key_name}}'         => $this->modelKeyName,
            '{{table_cols}}'             => $tableCols,
            '{{table_header}}'           => $table_header,
            '{{table_body}}'             => $table_body,
            '{{controller_class}}'       => $this->controllerClass,
            '{{controller_class_lower}}' => strtolower($this->controllerClass),
            '{{controller_class_mini}}'  => $this->controllerClassMini,
            '{{search_fields}}'          => $searchFields,
            '{{hsearchFields}}'          => $hsearchFields,
            '{{base_route_path}}'        => $this->getBaseRoutePath(),
            '{{rest_base_api}}'          => $this->getRestApiUrl(),
            '{{list_api}}'               => $this->getApiUrl('list'),
            '{{delete_api}}'             => $this->getApiUrl('delete'),
            '{{batch_delete_api}}'       => $this->getApiUrl('batch_delete'),
        ];
        return self::handleViewFile($this->controllerNamespace, $this->controllerClass, $fields, $stubFile, 'list');
    }

    /**
     * @return array
     */
    private function handleViewsEdit()
    {
        $stubFile = $this->pathJoin([__DIR__, '../../resources', 'stubs', 'pages', 'edit.vue.stub']);

        $attributes = $this->model->getAttributes();

        // table
        $cols = array_keys($attributes);
        //comments
        $comments = $this->getTableComments();
        // type
        $types = $this->getTableType();
        $fields = '';
        $form_fields = '';
        foreach ($cols as $k_col => $col) {
            if(in_array($col,[$this->model::CREATED_AT,$this->model::UPDATED_AT])){
                continue;
            }
            $fields .= "                    {$col}:'',\n";
            //comment
            $comment = $this->getComment($comments, $col);
            //根据类型
            $type = $this->getType($types, $col);
            $form_fields .= $this->getFormItemByType($k_col, $col, $comment, $type);
        }

        $fields = [
            '{{controller_class_mini}}' => $this->controllerClassMini,
            '{{table_name}}'             => $this->getTableName(),
            '{{model_key_name}}'        => $this->modelKeyName,
            '{{fields_info}}'           => $fields,
            '{{form_fields}}'           => $form_fields,
            '{{base_route_path}}'       => $this->getBaseRoutePath(),
            '{{rest_base_api}}'         => $this->getRestApiUrl(),
            '{{detail_api}}'            => $this->getApiUrl('detail'),
            '{{save_api}}'              => $this->getApiUrl('save'),
        ];


        return self::handleViewFile($this->controllerNamespace, $this->controllerClass, $fields, $stubFile, 'edit');
    }

    /**
     * @return array
     */
    private function handleViewsDetail()
    {
        $stubFile = $this->pathJoin([__DIR__, '../../resources', 'stubs','pages', 'detail.vue.stub']);

        $attributes = $this->model->getAttributes();

        // table
        $cols = array_merge([$this->modelKeyName], array_keys($attributes));
        //comments
        $comments = $this->getTableComments();
        // type
        $types = $this->getTableType();
        $fields = '';
        $detail_fields = '';
        $counts = count($cols) - 1;
        foreach ($cols as $k_col => $col) {
            $fields .= "                    {$col}:'',\n";
            //comment
            $comment = $this->getComment($comments, $col);
            //根据类型
            $type = $this->getType($types, $col);
            $detail_fields .= $this->getDetailItemByType($k_col, $counts,$col, $comment, $type);
        }

        $fields = [
            '{{controller_class_mini}}' => $this->controllerClassMini,
            '{{table_name}}'             => $this->getTableName(),
            '{{model_key_name}}'        => $this->modelKeyName,
            '{{fields_info}}'           => $fields,
            '{{detail_fields}}'           => $detail_fields,
            '{{rest_base_api}}'          => $this->getRestApiUrl(),
            '{{detail_api}}'            => $this->getApiUrl('detail'),
        ];

        return self::handleViewFile($this->controllerNamespace, $this->controllerClass, $fields, $stubFile, 'detail');
    }

    /**
     * @return array
     */
    private function handleViewsCreate()
    {
        $stubFile = $this->pathJoin([__DIR__, '../../resources', 'stubs','pages', 'create.vue.stub']);

        $attributes = $this->model->getAttributes();

        // table
        $cols = array_keys($attributes);
        //comments
        $comments = $this->getTableComments();
        // type
        $types = $this->getTableType();
        $fields = '';
        $form_fields = '';
        foreach ($cols as $k_col => $col) {
            if(in_array($col,[$this->model::CREATED_AT,$this->model::UPDATED_AT])){
                continue;
            }
            $fields .= "                    {$col}:'',\n";
            //comment
            $comment = $this->getComment($comments, $col);
            //根据类型
            $type = $this->getType($types, $col);
            $form_fields .= $this->getFormItemByType($k_col, $col, $comment, $type);
        }
        $fields = [
            '{{controller_class_mini}}' => $this->controllerClassMini,
            '{{table_name}}'             => $this->getTableName(),
            '{{model_key_name}}'        => $this->modelKeyName,
            '{{fields_info}}'           => $fields,
            '{{form_fields}}'           => $form_fields,
            '{{base_route_path}}'       => $this->getBaseRoutePath(),
            '{{rest_base_api}}'         => $this->getRestApiUrl(),
            '{{save_api}}'              => $this->getApiUrl('save'),
        ];


        return self::handleViewFile($this->controllerNamespace, $this->controllerClass, $fields, $stubFile, 'create');
    }


    /**
     * @return array
     */
    private function handleViewsLayoutDefault()
    {
        $stubFile = $this->pathJoin([__DIR__, '../../resources', 'stubs', 'views', 'layout_default.stub']);


        $paths = ['list', 'create', 'detail', 'edit'];

        $projectPath = $this->project ? '/' . $this->project : '';

        $m2Path = $this->m2Path ? '/' . $this->m2Path : '';

        $routes = '';
        foreach ($paths as $p) {
            $routes .= "{
                    name: '{$this->controllerClassMini}_{$p}',
                    path: '/{$this->controllerClassMini}/{$p}',
                    url: '" . str_replace(DIRECTORY_SEPARATOR, '/', "{$projectPath}{$m2Path}/layout/render?path=/{$this->controllerClassMini}/{$p}") . "'
                },\n";
        }

        $routes .= '//-----routes append-----' . "\n";

        $menus = "{
                        icon: 'ios-people',
                        title: '{$this->controllerClassMini} list',
                        name:'{$this->controllerClassMini}_list'
                    },\n";

        $menus .= '//-----menus append-----' . "\n";

        $fields = [
            '{{routes}}'        => $routes,
            '{{menus}}'         => $menus,
            '{{default_route}}' => $this->controllerClassMini . '_list',
        ];


        return self::handleLayoutdefaultFile($this->controllerNamespace, 'layouts', $fields, $stubFile, 'default.blade');

    }


    private function handleApiRoute()
    {
        // api routes
        $apiRoutes = [];

        $m2cPath = $this->m2cPath ? DIRECTORY_SEPARATOR . $this->m2cPath : '';

        $controller = str_replace('App\\Http\\Controllers\\', '', $this->controllerClassName);
        // foreach ($this->actions as $name => $action) {
            // $apiRoutes[] = "Route::any('" . str_replace(DIRECTORY_SEPARATOR, '/', $m2cPath) . "/{$name}', '{$controller}@{$action}');";
        // }
        //使用resource路由
        $apiRoutes[] = "Route::resource('" . str_replace(DIRECTORY_SEPARATOR, '/', $m2cPath) . "', '{$controller}' );";
        $apiRoutesStr = join("\n", $apiRoutes) . "\n";
        return self::handleRouteFile($apiRoutesStr, 'api');
    }

    // private function handleWebRoute_bak()
    // {

    //     $m2Path = $this->m2Path ? DIRECTORY_SEPARATOR . $this->m2Path : '';
    //     $m2     = $this->m2 ? $this->m2 . '\\' : '';

    //     $routes   = [];
    //     // $routes[] = "Route::get('" . str_replace(DIRECTORY_SEPARATOR, '/', $m2Path) . "/layout', '{$m2}RenderController@index');";
    //     // $routes[] = "Route::get('" . str_replace(DIRECTORY_SEPARATOR, '/', $m2Path) . "/layout/render', '{$m2}RenderController@render');";
    //     $routes[] = "Route::get('{path}', 'SpaController')->where(['path'=>'.*']);";

    //     $routesStr = join("\n", $routes) . "\n";
    //     return self::handleRouteFile($routesStr, 'web');
    // }

    private function handleWebRoute()
    {

        $m2Path = $this->m2Path ? DIRECTORY_SEPARATOR . $this->m2Path : '';
        $m2     = $this->m2 ? $this->m2 . '\\' : '';
        $controller = str_replace('App\\Http\\Controllers\\', '', $this->controllerClassName);
        $controller_name = str_replace('Controller', '', $controller);
        $controller_name = strtolower($controller_name);
        $controller_name = str_replace('\\', '/', $controller_name);
        $routes   = [];
        $arr = [
            'list',
            'create',
            'edit',
            'detail'
        ];
        foreach($arr as $ac){
            $routes[] = '    {
      name: \''.$controller_name.'\',
      path:\'/'.$controller_name.'/'.$ac.'\',
      component: resolve =>void(require([\'./pages/'.$controller_name.'/'.$ac.'.vue\'], resolve))
    },';
        }
        $routesStr = join("\n", $routes) . "\n";
        return self::handleJsRouteFile($routesStr);
    }

    /**
     * base route path
     *
     * @param $api
     * @description
     * @example
     * @author doujinya 401298791@email.com
     * @date 2020-07-17
     *
     * @return void
     */
    private function getBaseRoutePath(){
        $projectPath = $this->project ? '/' . $this->project : '';
        $m2cPath = $this->m2cPath ? $this->m2cPath  : '';
        return "{$projectPath}/" . str_replace(DIRECTORY_SEPARATOR, '/', $m2cPath);
    }
    /**
     * rest api url
     *
     * @param $api
     * @description
     * @example
     * @author doujinya 401298791@email.com
     * @date 2020-07-17
     *
     * @return void
     */
    private function getRestApiUrl(){
        $projectPath = $this->project ? '/' . $this->project : '';
        $m2cPath = $this->m2cPath ? $this->m2cPath  : '';
        return "{$projectPath}/api/" . str_replace(DIRECTORY_SEPARATOR, '/', $m2cPath);
    }
    /**
     * @param $api
     * @return string
     */
    private function getApiUrl($api)
    {
        $projectPath = $this->project ? '/' . $this->project : '';
        $m2cPath = $this->m2cPath ? $this->m2cPath . DIRECTORY_SEPARATOR : '';
        return "{$projectPath}/api/" . str_replace(DIRECTORY_SEPARATOR, '/', $m2cPath) . $api;
    }

    private function getM2cPath()
    {
        $m2Path = $this->getM2Path();
        $m2Path = $m2Path ? $m2Path . DIRECTORY_SEPARATOR : '';

        return $m2Path . $this->controllerClassMini;
    }

    private function getM2Path()
    {
        return strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $this->getM2()));
    }

    private function getM2()
    {
        return trim(str_replace('App\\Http\\Controllers\\', '', $this->controllerNamespace . '\\'), '\\');
    }

    /**
     * Fetch table comments
     *
     * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/types.html#reference
     * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/schema-manager.html#schema-manager
     */
    private function getTableComments()
    {
        /**
         * @var \Illuminate\Database\Connection $connection
         */
        $connection = DB::connection('mysql');
        $schema     = $connection->getDoctrineSchemaManager();

        $cols = $schema->listTableColumns($this->model->getTable());
        $columns = [];
        foreach ($cols as $col) {
            $columns[$col->getName()] =$col->getComment() ?? '';
        }
        return $columns;
    }
    /**
     * Fetch table columns
     *
     * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/types.html#reference
     * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/schema-manager.html#schema-manager
     */
    private function getTableType()
    {
        /**
         * @var \Illuminate\Database\Connection $connection
         */
        $connection = DB::connection('mysql');
        $schema     = $connection->getDoctrineSchemaManager();

        $cols = $schema->listTableColumns($this->model->getTable());
        $columns = [];
        foreach ($cols as $col) {
            $columns[$col->getName()] =$col->getType()->getName() ?? '';
        }
        return $columns;
    }
    /**
     * Fetch table name
     *
     * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/types.html#reference
     * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/schema-manager.html#schema-manager
     */
    private function getTableName()
    {
        /**
         * @var \Illuminate\Database\Connection $connection
         */
        $connection = DB::connection('mysql');
        $schema     = $connection->getDoctrineSchemaManager();

        $cols = $schema->listTableDetails($this->model->getTable());
        return $cols->getOption('comment')?$cols->getOption('comment'):$cols->getName();

    }


    /**
     * 获取comment
     *
     * @description
     * @example
     * @author doujinya 401298791@email.com
     * @date 2020-07-17
     * @param [type] $comments
     * @param [type] $col
     *
     * @return void
     */
    private function getComment($comments,$col){
        $comment = isset($comments[$col])&&!empty($comments[$col])?$comments[$col]:$col;
        $comment = explode(':',$comment)[0];
        $comment = explode('：',$comment)[0];
        return $comment;
    }
   /**
     * 获取type
     *
     * @description
     * @example
     * @author doujinya 401298791@email.com
     * @date 2020-07-17
     * @param [type] $comments
     * @param [type] $col
     *
     * @return void
     */
    private function getType($types,$col){
        $type = isset($types[$col])?$types[$col]:'';
        return $type;
    }

    /**
     * 获取hesarch string
     *
     * @description
     * @example
     * @author doujinya 401298791@email.com
     * @date 2020-07-17
     * @param [type] $comments
     * @param [type] $col
     *
     * @return void
     */
    private function getHsearchStrByType($col,$comment,$type){
        switch($type){
            case 'integer':
            case 'string':
            case 'text':
            default:
                //text
                $return =  "                       { id: '{$col}', name: '{$comment}', type: 'text', default: '', span: 12 },\n";
            break;
            case 'datetime':
                $return =  "                       { id: '{$col}', name: '{$comment}', type: 'daterange', default: '', span: 12 },\n";
            break;
        }

        return $return;
    }
    /**
     * 获取form string
     *
     * @description
     * @example
     * @author doujinya 401298791@email.com
     * @date 2020-07-17
     * @param [type] $comments
     * @param [type] $col
     *
     * @return void
     */
    private function getFormItemByType($k_col, $col,$comment,$type){
        switch($type){
            case 'integer':
            case 'string':
            default:
                //text
                $form_fields = '
            <!--'.$comment.'-->
            <el-form-item label="'.$comment.'" prop="'.$col.'" style="width: 480px;">
                <el-input v-model="data.'.$col.'"></el-input>
            </el-form-item>
            ';
            break;
            case 'text':
                $form_fields = '
            <!--'.$comment.'-->
            <el-form-item label="'.$comment.'" prop="'.$col.'" style="width: 480px;">
                <el-input type="textarea" v-model="data.'.$col.'"></el-input>
            </el-form-item>
            ';
            break;
            case 'datetime':
                $form_fields = '
            <!--'.$comment.'-->
            <el-form-item label="'.$comment.'" style="width: 360px;">
                <el-col>
                    <el-form-item prop="'.$col.'">
                        <el-date-picker
                                type="datetime"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                format="yyyy-MM-dd HH:mm:ss"
                                placeholder="选择日期"
                                v-model="data.'.$col.'"
                                style="width: 100%;" >
                        </el-date-picker>
                    </el-form-item>
                </el-col>
            </el-form-item>
            ';
            break;
            break;
        }

        return $form_fields;
    }

    /**
     * detail fileds
     *
     * @description
     * @example
     * @author doujinya 401298791@email.com
     * @date 2020-07-20
     * @param [type] $col
     * @param [type] $comment
     * @param [type] $type
     *
     * @return void
     */
    private function getDetailItemByType($k_col,$counts, $col,$comment,$type){
        $base_offset = 36;
        $add_offset = $base_offset + 4;
        if($col!=0){
            $detail_fields = "\n";
        }else{
            $detail_fields = "\n";
        }
        if($k_col%2==0){
            $detail_fields .= str_repeat(" ",$base_offset).'<el-row  :gutter="10" class="c_elrow">'."\n";
        }
        //text
        $detail_fields .= str_repeat(" ",$add_offset).'<el-col :span="elcolspan" class="c_eltab lh30">'.$comment.'：</el-col>'."\n";
        $detail_fields .= str_repeat(" ",$add_offset).'<el-col :span="8" class="lh30">{{data.'.$col.'}}</el-col>';
        if($k_col%2==1 || $k_col==$counts){
            $detail_fields .="\n".str_repeat(" ",$base_offset)."</el-row>";
        }
        return $detail_fields;
    }
}
