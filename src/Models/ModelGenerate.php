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
 * Create model
 *
 * Class BaseGenerate
 * @package Skyyouare\Gii\Models
 */
class ModelGenerate extends BaseGenerate
{

    /**
     * @var
     */
    protected $tableName;
    /**
     * @var
     */
    protected $baseModelClassName;
    /**
     * @var
     */
    protected $modelParentClassName;
    /**
     * @var
     */
    protected $select_fields;
    /**
     * @var
     */
    protected $primary_key;

    /**
     * @var
     */
    protected $tableColumns;

    /**
     * @var
     */
    protected $tableIndexesUnique;


    /**
     * Model constructor.
     * @param $tableName
     * @param $baseModelClassName
     * @param $modelParentClassName
     * @throws \Exception
     */
    public function __construct($tableName, $baseModelClassName, $modelParentClassName,$select_fields, $primary_key,$create_at,$update_at)
    {
        if(!$tableName || !$baseModelClassName || !$modelParentClassName || !$primary_key){
            throw new \Exception('params is empty!');
        }

        $this->tableName            = $tableName;
        $this->baseModelClassName   = $baseModelClassName;
        $this->modelParentClassName = $modelParentClassName;
        $this->select_fields        = explode(',',$select_fields);
        $this->primary_key          = $primary_key;
        $this->create_at            = $create_at;
        $this->update_at            = $update_at;
    }


    /**
     * @return array
     * @throws \ReflectionException
     */
    public function preview()
    {
        $stubFiles = [
            'validate'   => $this->pathJoin([__DIR__, '../../resources', 'stubs', 'validate.stub']),
            'observer'   => $this->pathJoin([__DIR__, '../../resources', 'stubs', 'observer.stub']),
            'base_model' => $this->pathJoin([__DIR__, '../../resources', 'stubs', 'basemodel.stub']),
            'model'      => $this->pathJoin([__DIR__, '../../resources', 'stubs', 'model.stub']),
        ];

        // basic model
        $baseModelClassNameArr = explode('\\', $this->baseModelClassName);
        $baseModelClass        = end($baseModelClassNameArr);
        $baseModelNamespace    = trim(substr($this->baseModelClassName, 0, strrpos($this->baseModelClassName, '\\')), '\\');

        // parent model class
        $modelParentClassNameArr = explode('\\', $this->modelParentClassName);
        $modelParentClass        = end($modelParentClassNameArr);

        // observer
        $observerClass     = $baseModelClass . 'Observer';
        $observerClassName = str_replace('Models', 'Observers\\Models', $baseModelNamespace) . '\\' . $observerClass;
        $observerNamespace = trim(substr($observerClassName, 0, strrpos($observerClassName, '\\')), '\\');

        // validate
        $validateClass     = $baseModelClass . 'Requests';
        $validateClassName = str_replace('Models', 'Http\\Requests', $baseModelNamespace) . '\\' . $validateClass;
        $validateNamespace = trim(substr($validateClassName, 0, strrpos($validateClassName, '\\')), '\\');

        // model
        $modelClass     = $baseModelClass . 'Model';
        $modelNamespace = $baseModelNamespace;
        $modelClassName = $modelNamespace . '\\' . $modelClass;

        // init table columns
        $this->getTableColumns();

        $create_at = $this->create_at ? "'".$this->create_at."'" : "null";
        $update_at = $this->update_at ? "'".$this->update_at."'" : "null";

        $rules = $this->createRules();
        $select_fields = $this->getSelectFields();

        $fields = [
            '{{validate_class}}'           => $validateClass,
            '{{validate_namespace}}'      => $validateNamespace,
            '{{base_model_class}}'        => $baseModelClass,
            '{{base_model_namespace}}'    => $baseModelNamespace,
            '{{base_model_class_name}}'   => $this->baseModelClassName,
            '{{model_namespace}}'         => $modelNamespace,
            '{{observer_class_name}}'     => $observerClassName,
            '{{observer_namespace}}'      => $observerNamespace,
            '{{model_parent_class_name}}' => $this->modelParentClassName,
            '{{remarks}}'                 => $this->createProperty(),
            '{{model_parent_class}}'      => $modelParentClass,
            '{{connection}}'              => '',
            '{{table_name}}'              => $this->tableName,
            '{{attributes}}'              => $this->createAttributes(),
            '{{attributes_validate}}'     => $this->createAttributesValidate(),
            '{{rules}}'                   => $rules['base'],
            '{{unique_vars}}'             => $rules['unique_vars'],
            '{{rules_create}}'            => $rules['create'],
            '{{rules_update}}'            => $rules['update'],
            '{{search_type}}'             => $this->getSearchType(),
            '{{observer_class}}'          => $observerClass,
            '{{primary_key}}'             => $this->primary_key,
            '{{dict_type}}'               => $this->getDictType(),
            "{{select_fields}}"           => $select_fields,
            '{{create_at}}'               => $create_at,
            '{{update_at}}'               => $update_at,
        ];


        $list = [];

        foreach ($stubFiles as $type => $stubFilePath) {

            switch ($type) {
                case 'validate':
                    $namespace = $validateNamespace;
                    $class     = $validateClass;
                    break;
                case 'base_model':
                    $namespace = $baseModelNamespace;
                    $class     = $baseModelClass;
                    break;
                case 'observer':
                    $namespace = $observerNamespace;
                    $class     = $observerClass;
                    break;
                case 'model':
                    $namespace = $modelNamespace;
                    $class     = $modelClass;
                    break;
            }

            $list[] = self::handleFile($namespace, $class, $fields, $stubFilePath);
        }

        return $list;
    }


    /**
     * Fetch table columns
     *
     * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/types.html#reference
     * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/schema-manager.html#schema-manager
     */
    private function getTableColumns()
    {
        /**
         * @var \Illuminate\Database\Connection $connection
         */
        $connection = DB::connection('mysql');
        $schema     = $connection->getDoctrineSchemaManager();

        //table columns
        $cols = $schema->listTableColumns($this->tableName);
        $columns = [];
        foreach ($cols as $col) {
            $columns[] = [
                'name'    => $col->getName(),
                'type'    => $col->getType()->getName() ?? '', // Use Doctrine convert type
                'length'  => $col->getLength()??'',
                'notnull' => $col->getNotnull()??'',
                'default' => $col->getDefault() ?? '',
                'comment' => $col->getComment() ?? '',
            ];
        }
        // var_dump($columns);exit;
        $this->tableColumns = $columns;

        //table indexes
        $indexUniques = [];
        $indexes = $schema->listTableIndexes($this->tableName);
        foreach ($indexes as $index) {
            if($index->isUnique()){
                $indexUniques[] = $index->getColumns();
            }
        }
        $this->tableIndexesUnique = $indexUniques;
    }

    /**
     * @return string
     */
    private function createAttributes()
    {
        $str = "\n";
        foreach ($this->tableColumns as $col) {
            // The primary key is filtered when generating attributes. The primary key does not need a default value, otherwise the write will be empty or null
            if (in_array($col['name'], ['id', '_id'])) {
                continue;
            }
            //主键判断
            if (!empty($this->primary_key) && $col['name']== $this->primary_key) {
                continue;
            }
            $default = isset($col['default']) ? "'" . $col['default'] . "'" : "''";
            $str     .= "        '" . $col['name'] . "' => " . $default . ",\n";
        }

        return $str;
    }

    /**
     * @return string
     */
    private function createAttributesValidate()
    {
        $str = "\n";
        foreach ($this->tableColumns as $col) {
            // The primary key is filtered when generating attributes. The primary key does not need a default value, otherwise the write will be empty or null
            if (in_array($col['name'], ['id', '_id'])) {
                continue;
            }
            //主键判断
            if (!empty($this->primary_key) && $col['name']== $this->primary_key) {
                continue;
            }
            $comment =  $this->getComment($col);
            $str     .= "            '" . $col['name'] . "' => '" . $comment . "',\n";
        }

        return $str;
    }

    /**
     * @return string
     */
    private function getSelectFields(){
        $str = "\n";
        foreach($this->select_fields as $fields){
            $str     .= str_repeat(" ",12)."'" . $fields ."',\n";
        }
        return $str;
    }
    /**
     * @return string
     */
    private function getDictType(){
        $str = "";
        foreach($this->select_fields as $k=> $fields){
            $str     .= str_repeat(" ",10)." case '" . $fields ."':\n";
            $str     .= str_repeat(" ",14)." //break;";
            if($k!=count($this->select_fields)-1){
                $str.="\n";
            }
        }
        return $str;
    }

    /**
     * @return string
     */
    private function createRules()
    {
        $base = "\n";
        $create = "";
        $update = "";
        $unique_vars = "";
        $arr_base = [];
        $arr_create = [];
        $arr_update = [];
        $arr_unique = [];
        foreach ($this->tableColumns as $col) {
            // The primary key is filtered when generating attributes. The primary key does not need a default value, otherwise the write will be empty or null
            if (in_array($col['name'], ['id', '_id'])) {
                continue;
            }
            //主键判断
            if (!empty($this->primary_key) && $col['name']== $this->primary_key) {
                continue;
            }
            //create at判断
            if (!empty($this->create_at) && $col['name'] == $this->create_at){
                continue;
            }
            //update at判断
            if (!empty($this->update_at) && $col['name'] == $this->update_at){
                continue;
            }
            $fule_arr = [];
             //nullable
             if($col['notnull']===false){
                $fule_arr[] = 'nullable';
            }else{
                $fule_arr[] = 'required';
            }
            //type
            switch($col['type']){
                case 'integer':
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                case 'bigint':
                case 'boolean':
                    $fule_arr[] = 'integer';
                break;
                case 'float':
                case 'double':
                case 'decimal':
                    $fule_arr[] = 'numeric';
                break;
                case 'string':
                    $fule_arr[] = 'string';
                    $fule_arr[] = 'max:'.$col['length'];
                break;
                case 'text':
                    $fule_arr[] = 'string';
                break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                    $fule_arr[] = 'date';
                break;
            }
            $arr_base[$col['name']] = $fule_arr;
        }
        foreach ($this->tableIndexesUnique as $unique) {
            $name = $unique[0];
            if (in_array($name,['id', '_id'])) {
                continue;
            }
            //主键判断
            if (!empty($this->primary_key) && $name== $this->primary_key) {
                continue;
            }
            //create at判断
            if (!empty($this->create_at) && $name == $this->create_at){
                continue;
            }
            //update at判断
            if (!empty($this->update_at) && $name == $this->update_at){
                continue;
            }
            //create
            $fule_arr_create = [];
            $fule_arr_create_item = 'unique:'.$this->tableName.','.$name.',NULL,'.$this->primary_key.'';

            //update
            $fule_arr_update = [];
            $fule_arr_update_item = 'unique:'.$this->tableName.','.$name.',\'.$id.\','.$this->primary_key.'';

            //parameters
            $parameters_name = strtolower(str_replace('_', '', $this->tableName));
            $arr_unique[] = '$id = isset($parameters[\''.$parameters_name.'\']) ? $parameters[\''.$parameters_name.'\'] : \'\';';
            foreach($unique as $k=>$v){
                if($k==0) continue;
                $arr_unique[] = '$'.$v.' = isset($params[\''.$v.'\']) ? $params[\''.$v.'\'] : \'\';';
                $fule_arr_create_item .= ','. $unique[$k].',\'.$'.$v.'.\'';
                $fule_arr_update_item .= ','. $unique[$k].',\'.$'.$v.'.\'';
            }
            $fule_arr_create[] = $fule_arr_create_item;
            $fule_arr_update[] = $fule_arr_update_item;
            $arr_create[$name]  = array_merge($arr_base[$name],$fule_arr_create);
            $arr_update[$name]  = array_merge($arr_base[$name],$fule_arr_update);
            if(isset($arr_base[$name])){
                unset($arr_base['sys']);
            }

        }
        //base
        foreach ($arr_base as $name => $fule_arr) {
            $fule_str = "'".implode(' | ',$fule_arr)."'";
            $base     .= "        '" . $name . "' => " . $fule_str . ",\n";
        }
        //ceate
        $k=0;
        foreach ($arr_create as $name => $fule_arr) {
            $fule_str = "'".implode(' | ',$fule_arr)."'";
            $create     .=  str_repeat(" ",20)."'" . $name . "' => " . $fule_str.",";
            if($k != count($arr_create)-1){
                $create .= ",\n";
            }
            $k++;
        }
        //update
        $k=0;
        foreach ($arr_update as $name => $fule_arr) {
            $fule_str = "'".implode(' | ',$fule_arr)."'";
            $update     .= str_repeat(" ",20)."'" . $name . "' => " . $fule_str.",";
            if($k != count($arr_update)-1){
                $update .= "\n";
            }
            $k++;
        }
        //unique
        foreach ($arr_unique as $key=>$val) {
            $unique_vars .= str_repeat(" ",8).$val;
            if($key != count($arr_unique)-1){
                $unique_vars .= "\n";
            }
        }
        return [
            'base'=>$base,
            'unique_vars'=>$unique_vars,
            'create'=>$create,
            'update'=>$update
        ];
    }

    /**
     * @return string
     */
    public function getJsRules(){
        $this->getTableColumns();
        $rules = "\n";
        $arr = [];
        foreach ($this->tableColumns as $col) {
            // The primary key is filtered when generating attributes. The primary key does not need a default value, otherwise the write will be empty or null
            if (in_array($col['name'], ['id', '_id'])) {
                continue;
            }
            //主键判断
            if (!empty($this->primary_key) && $col['name']== $this->primary_key) {
                continue;
            }
            //create at判断
            if (!empty($this->create_at) && $col['name'] == $this->create_at){
                continue;
            }
            //update at判断
            if (!empty($this->update_at) && $col['name'] == $this->update_at){
                continue;
            }
            //comment
            $label = $this->getComment($col);
            $fule_arr = [];
            //nullable
            $required = false;
            if($col['notnull']){
                $required = true;
                if(!in_array($col['type'],['date', 'datetime', 'timestamp'])){
                    $fule_arr[] = "{ required :true, message: '请输入".$label."', trigger: 'blur'}";
                }
            }
            //type
            switch($col['type']){
                // case 'integer':
                // case 'tinyint':
                // case 'smallint':
                // case 'mediumint':
                // case 'bigint':
                // case 'boolean':
                //     $fule_arr[] = "{ type :integer, message: '请输入".$label."', trigger: 'blur'}";
                // break;
                // case 'float':
                // case 'double':
                // case 'decimal':
                //     $fule_arr[] = "{ required :number, message: '请输入".$label."', trigger: 'blur'}";
                // break;
                case 'string':
                    $fule_arr[] = "{ max:".$col['length'].", message:'长度不能超过".$col['length']."个字符', trigger: 'blur' }";
                break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                    if($required){
                        $fule_arr[] = "{ type: 'string', required: true, message: '请选择".$label."', trigger: 'change' }";
                    }else{
                        $fule_arr[] = "{ type: 'string', required: false, message: '请选择".$label."', trigger: 'change' }";
                    }

                break;
            }
            $arr[$col['name']] = $fule_arr;
        }

        //base
        $offset_base = 16;
        $offset_add = $offset_base + 4;
        foreach ($arr as $name => $fule_arr) {
            //如何为空，空行从头开始
            if(empty($fule_arr)){
                $fule_str = "";
            }else{
                $fule_str = str_repeat(" ",$offset_add)."".implode(",\n". str_repeat(" ",$offset_add),$fule_arr)."";
            }
            $rules     .= str_repeat(" ",$offset_base) . $name . " : [\n".  $fule_str . "\n". str_repeat(" ",$offset_base)."],\n";
        }
        return $rules;
    }

    /**
     * @return string
     */
    public function attributes()
    {
        // init table columns
        $this->getTableColumns();
        $arr = [];
        foreach ($this->tableColumns as $col) {
            $item = [];
            $item['label'] = $col['comment']?$col['comment']:$col['name'];
            $item['name'] = $col['name'];
            $arr[] = $item;
        }
        return $arr;
    }

    /**
     * @return string
     */
    private function createProperty()
    {
        $str = "/**\n";
        foreach ($this->tableColumns as $col) {
            $str .= "* @property {$col['type']} $" . $col['name'];
            if(!empty($col['comment'])){
                $str .= " ";
            }
            $str .= "{$col['comment']}\n";
        }

        return $str . "*/";
    }

    /**
     * @return array
     */
    public static function getTableList()
    {
        $list = DB::connection('mysql')->select('show tables');
        $list = json_decode(json_encode($list), true);

        $tableList = [];
        foreach ($list as $item) {
            $tableList[] = array_shift($item);
        }

        ksort($tableList);

        return $tableList;
    }

    /**
     * @return string
     */
    private function getSearchType(){
        $str = "\n";
        foreach ($this->tableColumns as $col) {
            // The primary key is filtered when generating attributes. The primary key does not need a default value, otherwise the write will be empty or null
            if (in_array($col['name'], ['id', '_id'])) {
                continue;
            }
            //主键判断
            if (!empty($this->primary_key) && $col['name']== $this->primary_key) {
                continue;
            }
            //如果是 date datetime
            if (in_array($col['type'],['date','datetime'])) {
                $seach_type = "['".$col['name']."', '>=']";
                $str     .= "            '" . $col['name'] . "_start' => " . $seach_type . ",\n";
                $seach_type = "['".$col['name']."', '<',  date('Y-m-d', (strtotime(\$model->".$col['name'].") + 3600*24))]";
                $str     .= "            '" . $col['name'] . "_end' => " . $seach_type . ",\n";
            }else{
                $seach_type = "'='";
                $str     .= "            '" . $col['name'] . "' => " . $seach_type . ",\n";
            }
        }
        return $str;
    }

    /**
     * 获取comment
     *
     * @description
     * @example
     * @author doujinya 401298791@email.com
     * @date 2020-07-27
     * @param [type] $col
     *
     * @return void
     */
    private function getComment($col){
        $comment = $col['comment']?$col['comment']:$col['name'];
        $comment = explode(':',$comment)[0];
        $comment = explode('：',$comment)[0];
        return $comment;
    }

}
