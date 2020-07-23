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
    protected $primary_key;

    /**
     * @var
     */
    protected $tableColumns;


    /**
     * Model constructor.
     * @param $tableName
     * @param $baseModelClassName
     * @param $modelParentClassName
     * @throws \Exception
     */
    public function __construct($tableName, $baseModelClassName, $modelParentClassName, $primary_key,$create_at,$update_at)
    {
        if(!$tableName || !$baseModelClassName || !$modelParentClassName || !$primary_key){
            throw new \Exception('params is empty!');
        }

        $this->tableName            = $tableName;
        $this->baseModelClassName   = $baseModelClassName;
        $this->modelParentClassName = $modelParentClassName;
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

        // model
        $modelClass     = $baseModelClass . 'Model';
        $modelNamespace = $baseModelNamespace;
        $modelClassName = $modelNamespace . '\\' . $modelClass;

        // init table columns
        $this->getTableColumns();

        $create_at = $this->create_at ? "'".$this->create_at."'" : "null";
        $update_at = $this->update_at ? "'".$this->update_at."'" : "null";

        $fields = [
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
            '{{rules}}'                   => '',
            '{{search_type}}'             => $this->getSearchType(),
            '{{observer_class}}'          => $observerClass,
            '{{primary_key}}'             => $this->primary_key,
            '{{create_at}}'               => $create_at,
            '{{update_at}}'               => $update_at,
        ];


        $list = [];

        foreach ($stubFiles as $type => $stubFilePath) {

            switch ($type) {
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

        $cols = $schema->listTableColumns($this->tableName);
        $columns = [];
        foreach ($cols as $col) {
            $columns[] = [
                'name'    => $col->getName(),
                'type'    => $col->getType()->getName() ?? '', // Use Doctrine convert type
                'default' => $col->getDefault() ?? '',
                'comment' => $col->getComment() ?? '',
            ];
        }
        $this->tableColumns = $columns;

        // $createdAt = $this->modelParentClassName::CREATED_AT;
        // $updatedAt = $this->modelParentClassName::UPDATED_AT;


        // // table struct verify
        // foreach ($columns as $key => $col) {
        //     if (in_array($col['name'], [$createdAt, $updatedAt])) {
        //         unset($columns[$key]);
        //     }
        // }

        // // Make sure always have fields `create_at` & `update_at`
        // $this->tableColumns = array_merge($columns, [
        //     [
        //         'name'    => $createdAt,
        //         'type'    => 'datetime',
        //         'comment' => '',
        //     ],
        //     [
        //         'name'    => $updatedAt,
        //         'type'    => 'datetime',
        //         'comment' => '',
        //     ]
        // ]);
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

}
