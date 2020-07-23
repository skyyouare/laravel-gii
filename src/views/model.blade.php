@extends('gii_views::index')
<style>
    .ivu-table .demo-table-info-cell {
        background-color: #2db7f5;
        color: #fff;
    }

    .ivu-table .demo-table-warning-cell {
        background-color: #ff6600;
        color: #fff;
    }

    .ivu-table .demo-table-error-cell {
        background-color: #187;
        color: #fff;
    }
    .ivu-form-item-content .ivu-input-wrapper{
        float: left;
        width: 300px;
    }
    .tip{
        float: left;
        padding-left: 10px;
    }
    .ivu-form-item-content .ivu-select{
        float: left;
    }
</style>
@section('content')
    <i-row>
        <i-col span="24">
            <i-form :label-width="200" method="post">
                <i-form-item label="MySQL table name">
                    <i-auto-complete :data="table_list" value="{{request()->table_name}}" name="table_name"
                                     placeholder="click to choose or input custom" @on-change="select_table"></i-auto-complete>
                </i-form-item>
                <i-form-item label="Model name">
                    <i-select name="model_class_name" :model.sync="model_class_name" :value="model_class_name"  style="width:300px">
                        <i-option v-for="item in model_class_name_arr" :value="item">@{{ item }}</i-option>
                    </i-select>
                </i-form-item>
                <i-form-item label="Parent class name">
                    <i-select name="parent_class_name" :model.sync="parent_class_name" :value="parent_class_name"  style="width:300px">
                        <i-option v-for="item in parent_class_name_arr" :value="item">@{{ item }}</i-option>
                    </i-select>
                </i-form-item>
                <i-form-item label="Primary key">
                    <i-select name="primary_key" :model.sync="primary_key" :value="primary_key"  style="width:300px">
                        <i-option v-for="item in fields" :value="item.name">@{{ item.name }}</i-option>
                    </i-select>
                </i-form-item>
                <i-form-item label="Create at">
                    <i-select name="create_at" :model.sync="create_at" :value="create_at"  style="width:300px">
                        <i-option v-for="item in create_at_arr" :value="item">@{{ item }}</i-option>
                    </i-select>
                </i-form-item>
                <i-form-item label="Update at">
                    <i-select name="update_at" :model.sync="update_at" :value="update_at"  style="width:300px">
                        <i-option v-for="item in update_at_arr" :value="item">@{{ item }}</i-option>
                    </i-select>
                </i-form-item>
                <i-form-item>
                    <i-button type="primary" html-type="submit" name="preview">Preview</i-button>
                    @if(isset($files))
                        <i-button type="success" html-type="submit" name="generate" :value="1">Generate</i-button>
                        <input type="hidden" name="waitingfiles" v-model="waitingfiles">
                    @endif
                </i-form-item>
                {{csrf_field()}}
            </i-form>
            @if( isset($alert))
                <i-alert type="{{$alert['type']}}" show-icon>
                    {{$alert['type']}}
                    <span slot="desc">
                        {{$alert['message']}}
                    </span>
                </i-alert>
            @endif
        </i-col>
    </i-row>
    {{--@if(isset($files))--}}
    @if(isset($generate_info))
        @foreach($generate_info as $file)
            <i-alert type="{{$file['status']['type']}}" show-icon>
                {{$file['status']['type']}} : {{$file['virtual_path']}}:{{$file['status']['message']}}
            </i-alert>
        @endforeach
    @endif
    <i-row>
        <i-col span="20">
            <div style="text-align: right">
                <i-button @click="handleSelectAll(true)">Select all</i-button>
                <i-button @click="handleSelectAll(false)">Unselect all</i-button>
            </div>
            <br>
            <i-table :columns="table_col" :data="table_data" ref="selection"
                     @on-selection-change="table_selection_change">
                <template slot-scope="{ row, index }" slot="table_button">
                    <i-button type="info" size="small" @click="openModal(index)">Detail</i-button>
                    <template v-if="row.isdiff == 'y'">
                        <i-button type="warning" size="small" @click="openDiffModal(index)">diff</i-button>
                    </template>
                    <template v-else>
                        <i-button size="small">unchanged</i-button>
                    </template>
                </template>
            </i-table>
        </i-col>
    </i-row>
    @foreach ($files as $key=>$file)
        <i-modal v-model="modal{{$key}}" title="{{$file['path']}}" width="80">
            <pre><code v-text="modalcontent{{$key}}"></code></pre>
        </i-modal>
        @if($file['diff_content'])
            <i-modal v-model="diffmodal{{$key}}" title="{{$file['path']}}" width="80">
                <div id="diffcode{{$key}}" v-html="diffmodalcontent{{$key}}"></div>
            </i-modal>
        @endif
    @endforeach
@endsection
@section('assets')
    <link rel="stylesheet" href="{{URL::asset('gii_assets/highlightjs/default.min.css')}}">
    <script src="{{URL::asset('gii_assets/highlightjs/highlight.min.js')}}"></script>
    <script>hljs.initHighlightingOnLoad();</script>
    <script src="{{URL::asset('gii_assets/diff2html/diff2html.min.js')}}" crossorigin="anonymous"></script>
    <script src="{{URL::asset('gii_assets/diff2html/diff2html-ui.min.js')}}"  crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{URL::asset('gii_assets/diff2html/diff2html.min.css')}}" crossorigin="anonymous"/>
    <script src="{{URL::asset('gii_assets/axios.min.js')}}"></script>
@endsection
@section('new_vue')
    <script>
        new Vue({
            data: {
                model_class_name:'',
                model_class_name_arr:[],
                parent_class_name:'',
                parent_class_name_arr:[],
                primary_key:'',
                create_at:'',
                create_at_arr:[],
                update_at:'',
                update_at_arr:[],
                fields:[
                    @if(isset($fields))
                        @foreach ($fields as $key => $field)
                        {
                            name: "{{$field['name']}}",
                            label: "{{$field['label']}}",
                        },
                        @endforeach
                    @endif
                ],
                table_data: [
                        @foreach ($files as $key => $file)
                    {
                        path: "{{$file['virtual_path']}}",
                        isdiff: "{{$file['is_diff']}}",
                        cellClassName: {
                            @if($file['is_new_file'] == 'y')
                            path: 'demo-table-info-cell',
                            @elseif($file['is_diff'] == 'y')
                            path: 'demo-table-warning-cell'
                            @endif
                        }
                    },
                    @endforeach
                ],
                @foreach ($files as $key => $file)
                modal{{$key}}: false,
                modalcontent{{$key}} : decodeURIComponent(`{{$file['content']}}`),
                diffmodal{{$key}}: false,
                @endforeach
                table_col: [
                    {
                        type: 'index',
                        width: 60,
                        align: 'center'
                    },
                    {
                        title: 'path',
                        key: 'path',
                    },
                    {
                        title: 'operation',
                        slot: "table_button",
                        width: 200,
                    },
                    {
                        type: 'selection',
                        width: 60,
                        align: 'center'
                    },
                ],
                waitingfiles: [],
                table_list:[
                    @foreach ($table_list as $key => $t)
                        '{{$t}}',
                    @endforeach
                ],
            },
            computed: {
                @foreach($files as $key=>$file)
                        @if($file['diff_content'])
                diffmodalcontent{{$key}} () {
                    return Diff2Html.getPrettyHtml(
                        decodeURIComponent(`{!! $file['diff_content']  !!}`),
                        {inputFormat: 'diff', showFiles: true, matching: 'lines', outputFormat: 'line-by-line'}
                    );
                },
                @endif
                @endforeach
                getActiveName: function() {
                    return 'model'
                }
            },
            methods: {
                openModal(index) {
                    this['modal' + index] = true
                },
                openDiffModal(index) {
                    this['diffmodal' + index] = true
                },
                handleSelectAll(status) {
                    this.$refs.selection.selectAll(status);
                },
                table_selection_change(rows) {
                    this.waitingfiles = []
                    for (var i in rows) {
                        this.waitingfiles.push(rows[i].path);
                    }
                },
                select_table(value){
                    var s = '';
                    var fields = [];
                    if(value){
                        //model name
                        var s1 = "{{str_replace('\\','\\\\',config('gii.model_namespaces')[0])}}" , s2 = this.pascal(value);
                        var s = s1 + '\\' + s2 ;
                        this.set_model_namespaces(s2);
                        //fields
                        var _this = this;
                        axios.get('/gii/model/fields?table_name='+value).then(function (response) {
                            _this.fields = response.data;
                            _this.primary_key = response.data[0]['name'];
                        })["catch"](function (error) {
                            console.log(error);
                        });
                    }
                    this.model_class_name = s;
                },
                //转pascal
                pascal(str){
                    // 去除中划线分隔符获取单词数组
                    var strArr = str.split('_');
                    // 如果第一个为空，则去掉
                    if(strArr[0] === '') {
                        strArr.shift();
                    }
                    // 遍历第二个单词到最后一个单词，并转换单词首字母为答谢
                    for(var i = 0, len = strArr.length; i < len; i++){
                        // 如果不为空，则转成大写
                        if(strArr[i] !== '') {
                            console.log(i);
                            strArr[i] = strArr[i][0].toUpperCase() + strArr[i].substring(1);
                        }
                    }
                    return strArr.join('');
                },
                set_model_namespaces(table_name){
                    var s2 = this.pascal(table_name);
                    var model_namespaces = [
                        @foreach (str_replace('\\','\\\\',config('gii.model_namespaces')) as $namespace)
                            "{{$namespace}}",
                        @endforeach
                    ];
                    this.model_class_name_arr = [];
                    for(i=0;i<model_namespaces.length;i++){
                        this.model_class_name_arr.push(model_namespaces[i]+'\\'+s2);
                    }
                },
                set_parent_class_names(){
                    this.parent_class_name_arr = [
                        @foreach (str_replace('\\','\\\\',config('gii.base_model_defaults')) as $models)
                            "{{$models}}",
                        @endforeach
                    ];
                },
                set_create_ats(){
                    this.create_at_arr = [
                        @foreach (str_replace('\\','\\\\',config('gii.create_at_defaults')) as $f)
                            "{{$f}}",
                        @endforeach
                    ];
                },
                set_update_ats(){
                    this.update_at_arr = [
                        @foreach (str_replace('\\','\\\\',config('gii.update_at_defaults')) as $f)
                            "{{$f}}",
                        @endforeach
                    ];
                }
            },
            mounted() {
                this.model_class_name = "{{str_replace('\\','\\\\',request()->model_class_name)}}";
                //model class arr
                @if(isset(request()->table_name))
                    this.set_model_namespaces("{{request()->table_name}}")
                @endif
                this.parent_class_name = "{{str_replace('\\','\\\\',request()->parent_class_name?request()->parent_class_name:config('gii.base_model_defaults')[0])}}";
                this.set_parent_class_names();
                this.primary_key = '{{request()->primary_key}}';
                this.create_at = "{{str_replace('\\','\\\\',request()->create_at)}}";
                this.update_at = "{{str_replace('\\','\\\\',request()->update_at)}}";
                this.set_create_ats();
                this.set_update_ats();
            }
        }).$mount('#app');
    </script>
@endsection

