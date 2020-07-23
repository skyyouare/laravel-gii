<template>
    <div>
        <div class="btn_normal" @click="highsearch_open" ref="ref_highsearch_click">高级搜索</div>
        <el-dialog title="高级搜索" :visible.sync="dialogVisible_highSearch" width="60%" custom-class="c_dialog"
            :modal=false :append-to-body=false ref="ref_highsearch">
            <el-form ref="highSearch_form" :model="highSearch_data" label-width="120px" style="width:100%">
                <el-row v-for="(hsearch_field,index) in hsearch_fields" :key="index">
                    <template  v-for="(item,i) in hsearch_field">
                        <!--类型为text-->
                        <template v-if="item.type=='text'">
                            <el-col :span="item.span">
                                <el-form-item :label="item.name" :prop="item.id">
                                    <el-input v-model="highSearch_data[item.id]" :placeholder="item.name"></el-input>
                                </el-form-item>
                            </el-col>
                        </template>
                        <!--类型为quicksearch_user-->
                        <template v-if="item.type=='quicksearch_user'">
                            <el-col :span="item.span">
                                <el-form-item :label="item.name" :prop="item.id">
                                      <el-autocomplete style="width: 100%;"
                                            v-model="highSearch_data[item.id]"
                                            :fetch-suggestions="querySearchAsyncUser"
                                            :placeholder="item.name"
                                            @select="handleSelectUser"
                                      ></el-autocomplete>
                                  </el-form-item>
                              </el-col>
                        </template>
                        <!--类型为select-->
                        <template v-if="item.type=='select'">
                            <el-col :span="item.span">
                                <el-form-item :label="item.name" :prop="item.id">
                                    <el-select v-model="highSearch_data[item.id]" :placeholder="'请选择'+item.name" style="width: 100%;">
                                        <el-option :key="item_dic.key" :label="item_dic.val" :value="item_dic.key" v-for="(item_dic,key_dic) in item.dic">
                                        </el-option>
                                    </el-select>
                                </el-form-item>
                              </el-col>
                        </template>
                        <!--类型为daterange-->
                        <template v-if="item.type=='daterange'">
                            <el-col :span="item.span">
                                <el-form-item :label="item.name" :prop="item.id">
                                    <el-date-picker
                                    style="width: 100%;"
                                      v-model="highSearch_data[item.id]"
                                      type="daterange"
                                      range-separator="至"
                                      start-placeholder="开始日期"
                                      end-placeholder="结束日期"
                                      :value-format="item.format?item.format:'yyyy-MM-dd'"
                                      >
                                    </el-date-picker>
                                </el-form-item>
                            </el-col>
                        </template>
                        <!--类型为range-->
                        <template v-if="item.type=='range'">
                            <el-col :span="item.span[0]">
                                <el-form-item :label="item.name" :prop="item.id+'_start'">
                                    <el-input v-model="highSearch_data[item.id+'_start']" :placeholder="item.placeholder&&item.placeholder[0]?item.placeholder[0]:''" oninput="value=value.replace(/[^\d.]/g,'');"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="item.span[1]" style="text-align: center;line-height: 40px;">
                              至
                            </el-col>
                            <el-col :span="item.span[2]">
                                <el-form-item :prop="item.id+'_end'" label-width="0">
                                    <el-input v-model="highSearch_data[item.id+'_end']" :placeholder="item.placeholder&&item.placeholder[1]?item.placeholder[1]:''" oninput="value=value.replace(/[^\d.]/g,'');"></el-input>
                                </el-form-item>
                            </el-col>
                        </template>
                    </template>
                </el-row>
            </el-form>
            <span slot="footer" class="dialog-footer" style="background-color: #f4f4f4;margin-top:-20px;">
                <el-button type="primary" @click="highsearch_submit">搜索</el-button>
                <el-button type="primary" @click="highsearch_reset">重置</el-button>
                <el-button @click="dialogVisible_highSearch = false">取 消</el-button>
            </span>
        </el-dialog>
    </div>
</template>
<script>
    export default {
        props: {
            //搜索字段
            hsearch_fields: {
                type: Array,
                default: () => ([])
            },
        },
        data() {
            return {
                dialogVisible_highSearch: false,//高级搜索--是否打开
                highSearch_data: {},//搜索内容设置,可覆盖,例如/
                highsearch_offsettop: 0,//缓存top
                highsearch_offsetleft: 0,//缓存left
            }
        },
        methods: {
            //快捷搜索
            querySearchAsyncUser:function(queryString, cb){//快捷搜索
                var selfthis=this
                var selfcb=cb
                selfthis.searched_item={}
                selfcb([])
                if(queryString){
                    clearTimeout(this.timeout);
                    this.timeout = setTimeout(() => {
                        selfthis.ajaxGet(selfthis,'/api/spa/selectUser',{name:queryString.trim()},function(selfthis,result){
                            if(result.length>=1){
                                selfcb(result)
                            }else{
                                var message='对不起，该员工不存在'
                                selfthis.$message({
                                    message: message,
                                    type: 'warning'
                                });
                            }
                        })
                    }, 100 * Math.random());
                }
            },
            //快捷搜索选择
            handleSelectUser(item) {
                //搜索赋值
                this.hsearch_fields.forEach(hsearch_field=>{
                    hsearch_field.forEach(v=>{
                        if(v.type=='quicksearch_user'){
                            this.highSearch_data[v.kid] = item.id;
                        }
                    });
                });
            },
            /**
             * [highsearch_reset 高级搜索重置]
             * @return {[type]} [description]
             */
            highsearch_reset() {
                //清空表单
                this.$refs["highSearch_form"].resetFields();
                console.log('清空表单');
                console.log(this.highSearch_data);
            },
            highsearch_open(event) {
                //console.log(event)
                this.dialogVisible_highSearch = !this.dialogVisible_highSearch
                var selfthis = this
                //按钮高度 offsetHeight
                var click_heigth = Number(this.$refs.ref_highsearch_click.offsetHeight)
                //按钮top offsetTop
                var click_top = Number(this.$refs.ref_highsearch_click.offsetTop)
                //按钮宽度 offsetWidth
                var click_width = Number(this.$refs.ref_highsearch_click.offsetWidth)
                //按钮 offsetLeft
                var click_left = Number(this.$refs.ref_highsearch_click.offsetLeft)
                setTimeout(function () {
                    //console.log(selfthis.$refs.ref_highsearch.$el.children[0].getClientRects())
                    //弹框 offsetTop
                    var dialog_top = Number(selfthis.$refs.ref_highsearch.$el.children[0].offsetTop)
                    var offsettop = click_heigth + click_top - dialog_top + 5
                    //console.log(offsettop+'='+click_heigth+'+'+click_top+'-'+dialog_top)
                    //弹框宽度 offsetWidth
                    var dialog_width = Number(selfthis.$refs.ref_highsearch.$el.children[0].getClientRects()[0].width)
                    //弹框 offsetLeft
                    var dialog_left = Number(selfthis.$refs.ref_highsearch.$el.children[0].offsetLeft)
                    var offsetleft = click_width + click_left - dialog_width - dialog_left - 1
                    //console.log(click_width+'+'+click_left+'-'+dialog_width)
                    //dialog_left1=Number(selfthis.$refs.ref_highsearch.$el.children[0].offsetLeft)
                    //console.log("old_left:"+dialog_left+"new+left:"+dialog_left1+"dialog_left:"+dialog_left+"="+offsetleft)
                    if (selfthis.highsearch_offsettop) {//如果存在缓存,取缓存
                        offsettop = selfthis.highsearch_offsettop
                        offsetleft = selfthis.highsearch_offsetleft
                    }
                    selfthis.$refs.ref_highsearch.$el.children[0].style.top = offsettop + "px";
                    selfthis.$refs.ref_highsearch.$el.children[0].style.left = offsetleft + "px"
                    selfthis.highsearch_offsettop = offsettop;
                    selfthis.highsearch_offsetleft = offsetleft;
                }, 80);


            },
            highsearch_submit() {
                console.log(this.highSearch_data.updated_at);
                //搜索赋值
                this.hsearch_fields.forEach(hsearch_field=>{
                    hsearch_field.forEach(v=>{
                        if(v.type=='daterange'){
                            this.highSearch_data[v.id+'_start'] = this.highSearch_data[v.id][0];
                            this.highSearch_data[v.id+'_end'] = this.highSearch_data[v.id][1];
                        }
                    });
                });
                this.$parent.highsearch_submit();
            }
        },
        mounted() {
            console.log("SearchComponent mounted.");
            this.hsearch_fields.forEach(hsearch_field=>{
                hsearch_field.forEach(v=>{
                    console.log(v);
                    switch(v.type){
                        case 'text':
                        case 'select':
                            this.$set(this.highSearch_data,v.id,v.default)
                            break;
                        case 'quicksearch_user':
                            this.$set(this.highSearch_data,v.id,v.default)
                            this.$set(this.highSearch_data,v.kid,v.default)
                            break;
                        case 'daterange':
                            this.$set(this.highSearch_data,v.id,[v.default,v.default]);
                            break;
                        case 'range':
                            this.$set(this.highSearch_data,v.id+'_start',v.default);
                            this.$set(this.highSearch_data,v.id+'_end',v.default);
                            break;
                    }
                });
            });
            console.log(this.highSearch_data);
        },
    }
</script>
