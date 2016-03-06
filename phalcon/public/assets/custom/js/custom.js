if (typeof jasApp == "undefined") var jasApp = new Object();

jasApp = {

    csrfName: '',
    csrfValue: '',
    csrf: null,
    prettyUrl: false,
    environment: 'dev',

    init: function(objData){
        this.csrfName = objData['csrf_name'];
        this.csrfValue = objData['csrf_value'];
        this.csrf = objData['csrf'];
        this.prettyUrl = (objData['pretty_url'] && typeof objData['pretty_url'] != 'undefined') ? ((objData['pretty_url'] == 1) ? true : false) : false;
        this.environment = objData['environment'];
    }

}


if (typeof jasHelper == "undefined") var jasHelper = new Object();

jasHelper = {

    errorHandler: function(res, title) {
        if (res){
            if (res.callback){
                this.callbackHandler(res);
                if (res.message){
                    console.log(res.message);
                }
            } else
            {
                if (res.message){
                    alert(res.message);
                }
            }
        }
    },


    callbackHandler: function(res) {
        if (res.callback && typeof res.callback != 'undefined') {
            if (! (Object.prototype.toString.call( res.callback ) === '[object Object]')){
                var adder = new Function(res.callback);
                adder();
            }
        }
    },


    urlAppend: function(url, part, value) {
        if (jasApp.prettyUrl === 1) {
            return url + '/' + value;
        } else {
            return url + '&' + part + '=' + value;
        }
    },


    parseResult: function(json) {
        if (!(typeof json =='object')){
            var res = jQuery.parseJSON(json);
        } else
        {
            var res = json;
        }
        return res;
    },

    isEmpty: function(obj) {

        if (obj == null) return true;

        if (obj.length > 0)    return false;  //string or array
        if (obj.length === 0)  return true;   //string or array
        if (typeof(obj) === 'object'){  // declared object, but there is no keys
            count = 0 ;
            for (i in obj) {
                if (obj.hasOwnProperty(i)) {
                    count++;
                }
            }
            if (count > 0){
                return false;
            }
        }

        return true;
    },

    checkResultObject: function(res, nullIsTrue){
        if (!(typeof res =='object')){
            if (this.isEmpty(res)){
                return (nullIsTrue === true) ? true : false
            } else {
                return true;
            }
        } else {
            return true;
        }
    },

    e: function(data) {
        if (jasApp.environment == 'dev') {
            console.log(data)
        }
    }
}


if (typeof jasHttp == "undefined") var jasHttp = new Object();

jasHttp = {

    simpleAjaxPost: function(ajaxurl, requestData, callback) {
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: requestData,
            async: true,
            success: function (res) {
                if (callback) {
                    callback(res);
                }
            }
        });
    },


    simpeAjaxGet: function(ajaxurl, requestData, callback) {
        $.ajax({
            type: 'GET',
            url: ajaxurl,
            data: requestData,
            async: true,
            success: function (res) {
                if (callback) {
                    callback(res);
                }
            }
        });
    },


    request: function(method, url, params){
        var d = new $.Deferred();
        $.ajax({
            method: method,
            url: url,
            data: params,
            async: true
        }).success(function (data, status, xhr) {
            switch (xhr.status) {
                case 200:
                    d.resolve(data);
                    break;
                case 201:
                    d.resolve(data);
                    break;
                case 400:
                    d.reject(data);
                    break;
                case 500:
                    d.reject(data);
                    break;
                default:
                    d.reject(data);
                    break;
            }
        }).error(function (data, status, headers) {
            var e = {'Result': 'ERROR', 'data' : data}
            jasHelper.e(e);
            if ((status == 401) || (status == 403)){
                //jasHelper.unauthorizedAlert();
            }
            d.reject(data);
        });
        return d.promise();
    },


    getData: function(scope, success, error){

        p = this.request(scope.method, scope.url, scope.params);

        p.then(function (data) {
            scope.data = (jasHelper.checkResultObject(data)) ? jasHelper.parseResult(data) : {}
            if (success){
                //success callback
                success(scope, data);
            }
        }).fail(function(data){
            if (error){
                //error callback
                error(scope, data);
            }
        });

    }

}




