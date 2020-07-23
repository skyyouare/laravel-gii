<?php

return [
    /*******************model generate config ************************/
    //model namespace
    'model_namespaces'=>[
        'App\Models',
        'App\Models\Admin'
    ],
    //base_model_default
    'base_model_defaults'=>[
        'Illuminate\Database\Eloquent\Model',
    ],
    //create_at default
    'create_at_defaults'=>[
        'create_time',
        'create_at',
        ''
    ],
    //update_at default
    'update_at_defaults'=>[
        'update_time',
        'update_at',
        ''
    ],
    /*******************crud generate config ************************/
    'controller_namespaces'=>[
        'App\Http\Controllers',
        'App\Http\Controllers\Admin'
    ],
    'model_base_path'=>[
        'app/Models'=>'App\Models',
        'app/Models/Admin'=>'App\Models\Admin'
    ],
];
