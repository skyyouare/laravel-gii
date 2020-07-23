<template>
    <ul class="nav_list">
        <li class="nav_item " v-for="(navItem,index) in navList">
            <div :class=" navItem.children.indexOf(navChild) !== -1 ? 'navChild_active' :navActive === navItem.name ? 'nav_item_active ' : ''"
                @click='navItemClick(navItem,index)'>
                <i><img :src="(navItem.children.indexOf(navChild) !== -1 ||navActive === navItem.name) ? navItem.tagIcon: navItem.icon"
                        alt=""></i>
                {{navItem.name}}
                <span v-if="!navItem.isShow"><img src="/images/icon/icon_xiala.png" alt=""></span>
                <span v-else><img src="/images/icon/icon_shouqi.png" alt=""></span>
            </div>
            <div v-show="navItem.isShow" :class="navChild === item ? 'nav_item_active navChild' :'navChild'"
                v-for="(item,index) in navItem.children" @click='navChildClick(item)'>
                {{item}}
            </div>
        </li>
    </ul>
</template>
<script>
    export default {
        // props: {
        //     nav: {
        //         type: Array,
        //         default: () => ([])
        //     },
        // },
        data() {
            return {
                navActive: '首页',
                navChild: '',
                navList:([])//this.nav
            }
        },
        methods: {
            navItemClick: function (item, index) {//导航栏高亮
                this.navActive = item.name
                this.navList[index].isShow = !item.isShow
                if (!item.isShow) {
                    this.navChild = ''
                }
            },
            navChildClick: function (item) { //二级导航栏高亮
                this.navChild = item
                this.navActive = ''
            },
        },
        mounted() {
            console.log("NavComponent mounted.");
            var _this= this;
            axios.get('/api/spa/nav').then(function (response) {
                _this.navList = response.data
            }).catch(function (error) {
                console.log(error);
            });

        },
        // watch: {
        //     navList:function(navList){
        //         if(navList != this.nav){
        //             this.navList = nav;
        //         }
        //     }
        // },
    }
</script>
