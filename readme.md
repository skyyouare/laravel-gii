# # laravel-gii 可视化代码生成工具 CRUD +GUI

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]


> Laravel Gii  参考sunshinev/laravel-gii，使用laravel7 + vue + elemnt-ui 架构，api使用resftul api，前后端分离,项目代码使用php artisan vendor:publish发布后，不依赖扩展本身，全部部署到项目各目录下,可根据需求灵活修改

[TOC]

## 原理

1. 通过解析MySQL的数据表结构，来提取字段、以及类型，并填充到`stub`模板。
2. 生成对应的Model、Observer、Controller、View、Route等相关项目文件。
3. 根据MySQL表结构生成Model
4. 根据Model生成Controller

## 注意
因为是解析MySQL的表结构，并且根据字段生成模板，所以目前生成的Model类时只支持MySQL，
MySQL表结构请保证`id`,`created_at`,`updated_at`三个字段必须存在。

## 安装
###扩展包
请先安装laravel7（其他版本未测试）

Via Composer

``` bash
$ composer require skyyouare/laravel-gii --dev
```

### 发布
```
需要先按
1、composer require laravel/ui --dev

2、php artisan ui vue

3、npm install

4、npm install element-ui vue-router qs --save-dev

5、php artisan vendor:publish  --tag gii.config --tag gii.views --tag gii.images --tag gii.js --tag gii.blade --tag  gii.sass --tag gii.controller --tag gii.request --tag gii.route --tag gii.exception --tag gii.provider --force

6、运行 npm run watch-poll 编译
```

### 访问
在发布完成后，已经进行了路由的注册，可以通过下面的路由访问Gii页面,请确认配置好**数据库**(否则会报错)，网站域名(ip)等
```
http:[domain]/gii/model
```

## 操作说明
### 生成Model模型
表单说明

1. MySQL table name：选择表名称
2. Model name：***自动***生成并选择mode名称,可以选择预定义的命名空间 可以修改config/gii.php增加或修改model_namespaces,下拉选择按配置排序
3. Parent class name：***自动***选择模型继承的父类,可调整config/gii.php文件中base_model_defaults配置就行修改
4. select fileds：需***手动***选择需要生成下拉框的文件，字典需手动修改 生成model的 get_dicts方法
5. Primary key：主键，默认选择第一个字段
6. Create at：选择创建时间字段，可以修改config/gii.php文件create_at_defaults进行调整
7. Update at：选择更新时间字段，可以修改config/gii.php文件update_at_defaults进行调整

生成的文件列表，蓝色代表全新文件，红色代表已有文件但是存在不同，白色代表已有文件。

比如指定生成的Model命名空间为`App\Models\Admin\AlarmLog`，那么生成的目录结构为:
```
    .app
    ├── Http
    │   └── Requests
    │       └── Admin
    │           └── AlarmLogRequests.php
    ├── Models
    │   └── Admin
    │       ├── AlarmLogModel.php
    │       └── AlarmLog.php
    ├── Observers
    │   └── Models
    │       └── Admin
    └──         └── AlarmLogObserver.php

```
通过上面的结构，我们可以发现命名空间与目录之间的关系。

### 生成CRUD

CRUD的创建，需要依赖之前创建的模型。

该操作会同时生成：

- route
- controller
- views

表单说明

1. Model：选择model,可配置config/gii.php文件model_base_path获取加载下拉列表目录
2. Controller namespace：选择命名空间,可修改config/gii.php文件create_at_defaults进行调整controller_namespaces
3. 自动生成文件名

如果指定命名空间的类为`App\Http\Controllers\Admin`,控制器为`AlarmLogController` ，以及关联的Model为`App\Models\Admin\AlarmLog`，那么生成的目录结构为:

```
    app
    ├── Http
    │   └── Controllers
    │   │   └── Admin
    │   │       └── AlarmLogController.php
    │   └── Requests
    │       └── Admin
    │           └── AlarmLogRequests.php
    ├── Models
    │   └── Admin
    │       ├── AlarmLogModel.php
    │       └── AlarmLog.php
    └── Observers
        └── Models
            └── Admin
                └── AlarmLogObserver.php
```

以及生成的视图文件
```
.resources
    └── pages
        └── admin
            └── alarmlog
                ├── list.vue
                ├── edit.vue
                ├── create.vue
                └── detail.vue
```
#### 如何访问CRUD?
***注意：路由是追加的，请勿多次生成***
CRUD的路由会自动添加到路由文件中，根据Controller的命名空间`App\Http\Controllers\Admin\AlarmLogController`会生成如下的路由，所以请直接访问路由
```
    //--------- append route 2020-08-27 09:58:16----------
    {
      name: 'alarmlog',
      path:'/alarmlog/list',
      component: resolve =>void(require(['./pages/alarmlog/list.vue'], resolve))
    },
    {
      name: 'alarmlog',
      path:'/alarmlog/create',
      component: resolve =>void(require(['./pages/alarmlog/create.vue'], resolve))
    },
    {
      name: 'alarmlog',
      path:'/alarmlog/edit',
      component: resolve =>void(require(['./pages/alarmlog/edit.vue'], resolve))
    },
    {
      name: 'alarmlog',
      path:'/alarmlog/detail',
      component: resolve =>void(require(['./pages/alarmlog/detail.vue'], resolve))
    },
```

## CRUD后台效果

#### 列表页
包含全面的增删查改功能

- 列表
- 分页
- 搜索
- 删除
- 详情
- 编辑

![截屏2020-08-27 下午6.11.24](media/15985194419423/%E6%88%AA%E5%B1%8F2020-08-27%20%E4%B8%8B%E5%8D%886.11.24.png)





#### 快捷搜索


![截屏2020-08-27 下午6.12.05](media/15985194419423/%E6%88%AA%E5%B1%8F2020-08-27%20%E4%B8%8B%E5%8D%886.12.05.png)



#### 添加页面
![截屏2020-08-27 下午6.12.20](media/15985194419423/%E6%88%AA%E5%B1%8F2020-08-27%20%E4%B8%8B%E5%8D%886.12.20.png)

### 编辑页面
![截屏2020-08-27 下午6.12.25](media/15985194419423/%E6%88%AA%E5%B1%8F2020-08-27%20%E4%B8%8B%E5%8D%886.12.25.png)

### 详情
![截屏2020-08-27 下午6.12.14](media/15985194419423/%E6%88%AA%E5%B1%8F2020-08-27%20%E4%B8%8B%E5%8D%886.12.14.png)


## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/skyyouare/laravel-gii.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/skyyouare/laravel-gii.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/skyyouare/laravel-gii/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/skyyouare/laravel-gii
[link-downloads]: https://packagist.org/packages/skyyouare/laravel-gii
[link-travis]: https://travis-ci.org/skyyouare/laravel-gii
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/skyyouare
[link-contributors]: ../../contributors

