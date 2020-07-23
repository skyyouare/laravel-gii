<template>
    <el-pagination style="margin-top:10px;text-align:right" background layout="sizes,prev, pager, next,total"
        @current-change="page_numchange" @size-change="page_sizeChange" :current-page.sync="page" :total="page_total"
        :page-size="page_size">
    </el-pagination>
</template>
<script>
    export default {
        props: {
            // //总记录数
            page_total: {
                type: Number,
                default: 0,
            },
        },
        data() {
            return {
                page_size :10,
                page :1,
                page_func:'',//分页list函数
                page_func_params:{},//分页函数变量
            }
        },
        methods: {
            /**
             * [page_init 分页初始化]
             * @param  {[type]} func   [列表页函数,例如传递this.initlist]
             * @param  {[type]} params [列表页函数需要的搜索变量,例如{tab:this.orderActive}]
             * @return {[type]}        [description]
             */
            page_init: function (func, params) {
                this.page_func=func
                params.page=this.page
                if(params.issearch==1){
                    this.page_size=this.page_defaultsize
                }
                params.page_num=this.page_size
                this.page_func_params=params
                this.page_func(this.page_func_params)
            },
            //分页-每页多少条记录
            page_sizeChange: function (val) {
                console.log("page_sizeChange");
                console.log(val);
                this.page_size=val
                this.page=1
                this.page_func_params.page=this.page
                this.page_func_params.page_num=this.page_size
                this.page_func(this.page_func_params)
            },
            //分页--变更
            page_numchange: function (val) {
                console.log("page_numchange");
                console.log(val);
                this.page=val
                this.page_func_params.page=this.page
                this.page_func_params.page_num=this.page_size
                this.page_func(this.page_func_params)
            },
        },
        mounted() {
            console.log("PageComponent mounted.");
        },
    }
</script>
