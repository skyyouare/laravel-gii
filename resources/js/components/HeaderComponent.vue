<template>
    <div class="index_title">
        <div class="title_logo">
            <img src="/images/logo.png" alt="">
        </div>
        <div class="title_option">
            <!-- <div class="option_item tiding">
                <i><img src="/images/icon/index_icon_xiaoxi.png" alt=""></i>
                消息
                <div class="buoy">8</div>
            </div> -->
            <!-- <div class="option_item">一键求助</div> -->
            <!-- <div class="option_item">帮助文档</div> -->
            <div class="option_item user" @click='changePreTbale'>
                <i><img src="/images/icon/index_top_icon_xiaoxi.png" alt=""></i>
                王小丫
                <span v-if='isPerTableShow'><img src="/images/icon/icon_shouqi.png" alt=""></span>
                <span v-else><img src="/images/icon/icon_xiala.png" alt=""></span>
                <div class="sign_out" v-show="isPerTableShow">
                    <img src="/images/icon/icon_tuichu.png" alt=""> 退出登录
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                isPerTableShow: false,
            }
        },
        methods: {
            changePreTbale: function () {//个人信息显示隐藏
                this.isPerTableShow = !this.isPerTableShow
            },
        },
        mounted() {
            console.log("HeaderComponent mounted.");
        },
    }
</script>

