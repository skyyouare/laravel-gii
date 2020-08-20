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
        } else {
            console.log("get 返回错误信息:")
            console.log(res)
            if (errorfunc != undefined) {
                errorfunc(selfthis, res.data)
            }else {
                var message = res.data.msg
                selfthis.$message({
                    message: message,
                    type: 'warning'
                });
            }
        }
    }).catch(function (error) {
        console.log("errorurl:" + url)
        console.log(error)
    });
}
Vue.prototype.ajaxPost = function (selfthis, url, data, func) {
    axios.post(url, data, {
        transformRequest: [
            function (data) {
                return JSON.stringify(data)
            }
        ]
    }).then(function (res) {
        if (res.data.code == 1000) {
            if (func != undefined) func(selfthis, res.data.data)
        } else {
            var message = res.data.msg
            selfthis.$message({
                message: message,
                type: 'warning',
                offset: 200
            });
        }

    }).catch(function (error) {
        console.log("errorurl:" + url)
        console.log(error)
    });

}

Vue.prototype.ajaxPut = function (selfthis, url, data, func) {
    axios.put(url, data, {
        transformRequest: [
            function (data) {
                return JSON.stringify(data)
            }
        ]
    }).then(function (res) {
        if (res.data.code == 1000) {
            if (func != undefined) func(selfthis, res.data.data)
        } else {
            var message = res.data.msg
            selfthis.$message({
                message: message,
                type: 'warning',
                offset: 200
            });
        }

    }).catch(function (error) {
        console.log("errorurl:" + url)
        console.log(error)
    });

}

Vue.prototype.ajaxDelete = function (selfthis, url, data, func) {
    axios.delete(url).then(function (res) {
        if (res.data.code == 1000) {
            if (func != undefined) func(selfthis, res.data.data)
        } else {
            var message = res.data.msg
            selfthis.$message({
                message: message,
                type: 'warning',
                offset: 200
            });
        }
    }).catch(function (error) {
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
        } else {
            var message = res.data.msg
            selfthis.$message({
                message: message,
                type: 'warning',
                offset: 200
            });
        }

    }).catch(function (error) {
        console.log("errorurl:" + url)
        console.log(error)
    });
}
