<?php
/**
 * Created by PhpStorm.
 * User: jaysun
 * Date: 2019-11-24
 * Time: 12:25
 */

namespace Skyyouare\Gii\Models;

use SebastianBergmann\Diff\Differ;

/**
 * Class BaseGenerate
 * @package Skyyouare\Gii\Models
 */
class BaseGenerate
{


    /**
     * @param $namespace
     * @param $class
     * @param $fields
     * @param $stubFilePath
     * @return array
     */
    public static function handleFile($namespace, $class, $fields, $stubFilePath)
    {

        $className = $namespace . '\\' . $class;

        $defaultPath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR . $class . '.php';
        // if class file do not exits, then  generate virtual path
        $defaultPath = lcfirst($defaultPath);
        $virtualPath = base_path($defaultPath);
        $isNewFile   = file_exists($virtualPath) ? false : true;
        // if file exists
        // generate new content
        $content = str_replace(array_keys($fields), array_values($fields), file_get_contents($stubFilePath));
        // get current class file content
        $currentContent = !$isNewFile ? file_get_contents($virtualPath) : '';

        $diffContent = (new Differ())->diff($currentContent, $content);

        if (trim($diffContent) == "--- Original\n+++ New") {
            $diffContent = '';
        }

        return [
            'path'           => $defaultPath,
            'virtual_path'   => $virtualPath,
            'is_new_file'    => $isNewFile ? 'y' : 'n',
            'content'        => rawurlencode($content),
            'origin_content' => $content,
            'diff_content'   => rawurlencode($diffContent),
            'is_diff'        => $diffContent ? 'y' : 'n'
        ];
    }


    public static function handleViewFile($namespace, $class, $fields, $stubFilePath, $viewPath = '')
    {
        // 根据控制器寻找view
        $defaultPathParent = str_replace('\\', '/', trim(str_replace('App\\Http\\Controllers', '', $namespace), '\\'));
        $defaultPathParent = $defaultPathParent ? $defaultPathParent . '/' : '';
        $defaultPath       = $defaultPathParent . str_replace('Controller', '', $class) . '/' . $viewPath . '.vue';
        $virtualPath       = resource_path('js/pages/' . strtolower($defaultPath));

        $isNewFile = file_exists($virtualPath) ? false : true;

        // generate new content
        $content = str_replace(array_keys($fields), array_values($fields), file_get_contents($stubFilePath));
        // get current class file content
        $currentContent = !$isNewFile ? file_get_contents($virtualPath) : '';

        $diffContent = (new Differ())->diff($currentContent, $content);

        if (trim($diffContent) == "--- Original\n+++ New") {
            $diffContent = '';
        }

        return [
            'path'           => $defaultPath,
            'virtual_path'   => $virtualPath,
            'is_new_file'    => $isNewFile ? 'y' : 'n',
            'content'        => rawurlencode($content),
            'origin_content' => $content,
            'diff_content'   => rawurlencode($diffContent),
            'is_diff'        => $diffContent ? 'y' : 'n'
        ];
    }


    public static function handleLayoutdefaultFile($namespace, $class, $fields, $stubFilePath, $viewPath = '')
    {
        // 根据控制器寻找view
        $defaultPathParent = str_replace('\\', '/', trim(str_replace('App\\Http\\Controllers', '', $namespace), '\\'));
        $defaultPathParent = $defaultPathParent ? $defaultPathParent . '/' : '';
        $defaultPath       = $defaultPathParent . str_replace('Controller', '', $class) . '/' . $viewPath . '.php';
        $virtualPath       = resource_path('views/' . strtolower($defaultPath));

        $isNewFile = file_exists($virtualPath) ? false : true;

        // get current class file content
        $currentContent = !$isNewFile ? file_get_contents($virtualPath) : '';

        // generate new content
        if ($isNewFile) {
            $content = str_replace(array_keys($fields), array_values($fields), file_get_contents($stubFilePath));
        } else {
            $fields  = [
                '//-----routes append-----' => $fields['{{routes}}'],
                '//-----menus append-----'  => $fields['{{menus}}']
            ];
            $content = str_replace(array_keys($fields), array_values($fields), $currentContent);
        }

        $diffContent = (new Differ())->diff($currentContent, $content);

        if (trim($diffContent) == "--- Original\n+++ New") {
            $diffContent = '';
        }

        return [
            'path'           => $defaultPath,
            'virtual_path'   => $virtualPath,
            'is_new_file'    => $isNewFile ? 'y' : 'n',
            'content'        => rawurlencode($content),
            'origin_content' => $content,
            'diff_content'   => rawurlencode($diffContent),
            'is_diff'        => $diffContent ? 'y' : 'n'
        ];
    }

    public static function handleRouteFile($appendContent, $routeType)
    {
        // 根据控制器寻找view
        $defaultPath = 'routes' .DIRECTORY_SEPARATOR. $routeType . '.php';
        $virtualPath = base_path($defaultPath);

        $isNewFile = file_exists($virtualPath) ? false : true;

        // generate new content
        $currentContent = file_exists($virtualPath) ? file_get_contents($virtualPath) : '';
        // get current class file content
        $begin   = "\n//--------- append route " . date('Y-m-d H:i:s') . "----------\n";
        $content = $currentContent . $begin . $appendContent;

        $diffContent = (new Differ())->diff($currentContent, $content);

        if (trim($diffContent) == "--- Original\n+++ New") {
            $diffContent = '';
        }

        return [
            'path'           => $defaultPath,
            'virtual_path'   => $virtualPath,
            'is_new_file'    => $isNewFile ? 'y' : 'n',
            'content'        => rawurlencode($content),
            'origin_content' => $content,
            'diff_content'   => rawurlencode($diffContent),
            'is_diff'        => $diffContent ? 'y' : 'n'
        ];
    }

    public static function handleJsRouteFile($appendContent)
    {
        // 根据控制器寻找view
        $defaultPath = 'resources' .DIRECTORY_SEPARATOR. 'js' .DIRECTORY_SEPARATOR . 'router.js';
        $virtualPath = base_path($defaultPath);

        $isNewFile = file_exists($virtualPath) ? false : true;

        // generate new content
        $currentContent = file_exists($virtualPath) ? file_get_contents($virtualPath) : '';
        $currentContent = str_replace('];', '', $currentContent);
        $currentContent = preg_replace('/[\r\n]+/', "\n", $currentContent);
        // get current class file content
        $begin   = "    //--------- append route " . date('Y-m-d H:i:s') . "----------\n";
        $content = $currentContent . $begin . $appendContent ."];\n";

        $diffContent = (new Differ())->diff($currentContent, $content);

        if (trim($diffContent) == "--- Original\n+++ New") {
            $diffContent = '';
        }

        return [
            'path'           => $defaultPath,
            'virtual_path'   => $virtualPath,
            'is_new_file'    => $isNewFile ? 'y' : 'n',
            'content'        => rawurlencode($content),
            'origin_content' => $content,
            'diff_content'   => rawurlencode($diffContent),
            'is_diff'        => $diffContent ? 'y' : 'n'
        ];
    }


    /**
     * 生成文件
     *
     * @param $fileList
     * @param $filePaths
     * @return array
     */
    public static function generateFile($fileList, $filePaths)
    {
        $filePaths = explode(',', $filePaths);

        $files = [];

        foreach ($fileList as $file) {
            foreach ($filePaths as $virtualPath) {
                if ($virtualPath == $file['virtual_path']) {
                    $files[] = $file;
                }
            }
        }

        foreach ($files as $key => $f) {

            $files[$key]['status'] = [];

            if ($f['is_new_file'] == 'n') {
                try {
                    file_put_contents($f['virtual_path'], $f['origin_content']);
                } catch (\Exception $exception) {
                    $files[$key]['status'] = [
                        'type'    => 'error',
                        'message' => $exception->getMessage()
                    ];
                }
            } else {
                try {
                    $p = substr($f['virtual_path'], 0, strrpos($f['virtual_path'], '/'));
                    if (!is_dir($p)) {
                        mkdir($p, 0755, true);
                    }

                    file_put_contents($f['virtual_path'], $f['origin_content']);
                } catch (\Exception $exception) {
                    $files[$key]['status'] = [
                        'type'    => 'error',
                        'message' => $exception->getMessage()
                    ];
                }
            }

            $files[$key]['status'] = [
                'type'    => 'success',
                'message' => ''
            ];
        }

        return $files;
    }


    /**
     *
     *
     * @param array $paths
     * @return string
     */
    public function pathJoin($paths = [])
    {
        return join(DIRECTORY_SEPARATOR, $paths);
    }


}
