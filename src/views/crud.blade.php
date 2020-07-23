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
    .ivu-form-item-content .ivu-select{
        float: left;
        width: 300px;
    }
    .ivu-form-item-content .ivu-input-wrapper{
        float: left;
        width: 300px;
    }
    .tip{
        float: left;
        padding-left: 10px;
    }
</style>
@section('content')
    <i-row>
        <i-col span="24">
            <i-form :label-width="200" method="post">
                <i-form-item label="Model">
                    <i-select name="model_class_name" :model.sync="model_class_name" :value="model_class_name" style="width:300px" @on-change="select_model">
                        <i-option v-for="item in model_lists" :value="item">@{{ item }}</i-option>
                    </i-select>
                </i-form-item>
                <i-form-item label="Controller namespace">
                    <i-select name="controller_namespace" :model.sync="controller_namespace" :value="controller_namespace"  style="width:300px">
                        <i-option v-for="item in controller_namespace_arr" :value="item">@{{ item }}</i-option>
                    </i-select>
                </i-form-item>
                <i-form-item label="Controller name">
                    <i-input name="controller_name" :value="controller_name"
                             placeholder="ex: {controller_name}"
                             :autocomplete="true"></i-input>
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
                model_class_name: '',
                model_lists:[],
                controller_namespace:'',
                controller_namespace_arr:[],
                controller_name:'',
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
                waitingfiles: []
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
                    return 'crud'
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
                select_model(value){
                    var arr = value.split('\\');
                    this.controller_name = arr.pop() + 'Controller';
                },
                set_model_lists(){
                    this.model_lists = [
                        @foreach (str_replace('\\','\\\\',$model_lists) as $f)
                            "{{$f}}",
                        @endforeach
                    ];
                },
                set_controller_namespaces(){
                    this.controller_namespace_arr = [
                        @foreach (str_replace('\\','\\\\',config('gii.controller_namespaces')) as $f)
                            "{{$f}}",
                        @endforeach
                    ];
                }
            },
            mounted() {
                this.model_class_name = "{{str_replace('\\','\\\\',request()->model_class_name)}}";
                this.set_model_lists();
                this.controller_namespace = "{{str_replace('\\','\\\\',request()->controller_namespace?request()->controller_namespace:config('gii.controller_namespaces')[0])}}";
                this.set_controller_namespaces();
                this.controller_name = "{{request()->controller_name}}";
            },
        }).$mount('#app');
    </script>
@endsection
