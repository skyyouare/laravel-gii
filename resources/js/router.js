window.routes =[
    {
        name:"home",
        path:'/',
        component: resolve =>void(require(['./pages/home.vue'], resolve))
    },
];
