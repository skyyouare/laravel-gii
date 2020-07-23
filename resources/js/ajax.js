//get
Vue.prototype.ajaxGet = function (selfthis, url, params, func, errorfunc) {
    var selfthis = selfthis
    if (params == undefined) params = []
    axios({
        method: 'get',
        url: url,
        params: params,

    }).then(function (res) {
        if (res.data.code == '1000') {
            if (func != undefined) func(selfthis, res.data.data)
            // selfthis.staff=res.data.data.top
            // selfthis.navList=res.data.data.left
            // //显示左侧菜单
            // urlpath=window.location.pathname.trim().substr(1)
            // selfthis.showChildren(urlpath)

        } else {
            console.log("get 返回错误信息:")
            console.log(res)
            if (errorfunc != undefined) errorfunc(selfthis, res.data)
            else {
                var message = res.data.msg
                selfthis.$message({
                    message: message,
                    type: 'warning'
                });

            }

        }

    }).catch(function (error) {
        // selfthis.$message({
        //              message: "网络异常:"+url,
        //              type: 'warning'
        //             });
        console.log("errorurl:" + url)
        console.log(error)
    });
}
Vue.prototype.ajaxPost = function (selfthis, url, data, func) {
    axios.post(url, data, {
        transformRequest: [
            function (data) {
                // let params = '';
                // for(let index in data){
                //     params +=index+'='+data[index]+'&';
                // }
                // console.log(params)
                return Qs.stringify(data)
            }
        ]
    }).then(function (res) {
        //console.log(res.data.code)
        if (res.data.code == 1000) {
            if (func != undefined) func(selfthis, res.data.data)
            // selfthis.$message({
            //    message: '提交成功！',
            //    type: 'success'
            //  });
            // selfthis.onekey_form.value_fkyj=""
            // selfthis.isShowOneKeyHelp=false
        } else {
            var message = res.data.msg
            selfthis.$message({
                message: message,
                type: 'warning',
                offset: 200
            });
        }

    }).catch(function (error) {
        // selfthis.$message({
        //          message: "网络异常:"+url,
        //          type: 'warning'
        //         });
        console.log("errorurl:" + url)
        console.log(error)
    });

}

Vue.prototype.ajaxPut = function (selfthis, url, data, func) {
    axios.put(url, data, {
        transformRequest: [
            function (data) {
                // let params = '';
                // for(let index in data){
                //     params +=index+'='+data[index]+'&';
                // }
                // console.log(params)
                return Qs.stringify(data)
            }
        ]
    }).then(function (res) {
        //console.log(res.data.code)
        if (res.data.code == 1000) {
            if (func != undefined) func(selfthis, res.data.data)
            // selfthis.$message({
            //    message: '提交成功！',
            //    type: 'success'
            //  });
            // selfthis.onekey_form.value_fkyj=""
            // selfthis.isShowOneKeyHelp=false
        } else {
            var message = res.data.msg
            selfthis.$message({
                message: message,
                type: 'warning',
                offset: 200
            });
        }

    }).catch(function (error) {
        // selfthis.$message({
        //          message: "网络异常:"+url,
        //          type: 'warning'
        //         });
        console.log("errorurl:" + url)
        console.log(error)
    });

}

Vue.prototype.ajaxDelete = function (selfthis, url, data, func) {
    axios.delete(url, data, {
        transformRequest: [
            function (data) {
                // let params = '';
                // for(let index in data){
                //     params +=index+'='+data[index]+'&';
                // }
                // console.log(params)
                return Qs.stringify(data)
            }
        ]
    }).then(function (res) {
        //console.log(res.data.code)
        if (res.data.code == 1000) {
            if (func != undefined) func(selfthis, res.data.data)
            // selfthis.$message({
            //    message: '提交成功！',
            //    type: 'success'
            //  });
            // selfthis.onekey_form.value_fkyj=""
            // selfthis.isShowOneKeyHelp=false
        } else {
            var message = res.data.msg
            selfthis.$message({
                message: message,
                type: 'warning',
                offset: 200
            });
        }

    }).catch(function (error) {
        // selfthis.$message({
        //          message: "网络异常:"+url,
        //          type: 'warning'
        //         });
        console.log("errorurl:" + url)
        console.log(error)
    });

}

//post--上传图片
Vue.prototype.ajaxPostfile = function (selfthis, url, data, func, config) {
    axios.post(url, data, config).then(function (res) {
        //console.log(res.data.code)
        if (res.data.code == 1000) {
            if (func != undefined) func(selfthis, res.data.data)
            // selfthis.$message({
            //    message: '提交成功！',
            //    type: 'success'
            //  });
            // selfthis.onekey_form.value_fkyj=""
            // selfthis.isShowOneKeyHelp=false
        } else {
            var message = res.data.msg
            selfthis.$message({
                message: message,
                type: 'warning',
                offset: 200
            });
        }

    }).catch(function (error) {
        // selfthis.$message({
        //         message: "网络异常:"+url,
        //         type: 'warning'
        //        });
        console.log("errorurl:" + url)
        console.log(error)

    });
}


