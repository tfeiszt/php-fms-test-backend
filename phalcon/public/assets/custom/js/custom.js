if (typeof jasApp == "undefined") var jasApp = new Object();

jasApp = {

    rootUrl: '',
    csrfName: '',
    csrfValue: '',
    csrf: null,
    prettyUrl: false,
    environment: 'dev',

    init: function(objData){
        this.rootUrl = objData['root_url'];
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


if (typeof controlService == "undefined") var controlService = new Object();

controlService = {

    scope: {
        data: null
    },
    controller: null,
    modalFormFile: null,
    modalFormFolder: null,
    modalFormRename: null,
    grids: [],

    init: function(objData, left, right){
        this.modalFormFile = objData['modal_form_file'];
        this.modalFormFolder = objData['modal_form_folder'];
        this.modalFormRename = objData['modal_form_rename'];
        this.controller = objData['controller'];
        this.grids.push(left);
        this.grids.push(right);

        this.controller.find('.btn-create-file').unbind('click');
        this.controller.find('.btn-create-file').bind('click', function(){
            controlService.modalFormFile.find('.modal-title').html('Create Textfile');
            controlService.createFile();
            return false;
        });
        this.controller.find('.btn-create-folder').unbind('click');
        this.controller.find('.btn-create-folder').bind('click', function(){
            controlService.modalFormFolder.find('.modal-title').html('Create New Folder');
            controlService.createFolder();
            return false;
        });
        this.modalFormFile.find('#newFileSave').unbind('click');
        this.modalFormFile.find('#newFileSave').bind('click', function(){
            controlService.saveFile();
            return false;
        });
        this.modalFormFolder.find('#newFolderSave').unbind('click');
        this.modalFormFolder.find('#newFolderSave').bind('click', function(){
            controlService.saveFolder();
            return false;
        });

        this.controller.find('.btn-copy').unbind('click');
        this.controller.find('.btn-copy').bind('click', function(){
            controlService.copyFile();
            return false;
        });

        this.controller.find('.btn-move').unbind('click');
        this.controller.find('.btn-move').bind('click', function(){
            controlService.moveFile();
            return false;
        });

        this.controller.find('.btn-delete').unbind('click');
        this.controller.find('.btn-delete').bind('click', function(){
            controlService.deleteObject();
            return false;
        });

        this.controller.find('.btn-rename').unbind('click');
        this.controller.find('.btn-rename').bind('click', function(){
            controlService.modalFormRename.find('.modal-title').html('Rename');
            controlService.renameObject();
            return false;
        });
        this.modalFormRename.find('#renameFormSave').unbind('click');
        this.modalFormRename.find('#renameFormSave').bind('click', function(){
            controlService.doRename();
            return false;
        });

        $('.manager-list').bind('click', function(){
            controlService.setActiveByName($(this).attr('data-gridname'));
        });

        this.refreshGrids();

    },


    getActiveGrid: function(){
        for(var i = 0; i < this.grids.length; i++){
            if (this.grids[i].idGridHeaderItem.hasClass('active')){
                return this.grids[i];
            }
        }
        return false;
    },


    getInActiveGrid: function(){
        for(var i = 0; i < this.grids.length; i++){
            if (! this.grids[i].idGridHeaderItem.hasClass('active')){
                return this.grids[i];
            }
        }
        return false;
    },

    refreshActiveGrid: function(){
        grid = this.getActiveGrid();
        grid.getList(grid);
    },


    refreshGrids: function(){
        for(var i = 0; i < this.grids.length; i++){
            this.grids[i].getList(this.grids[i]);
        }
    },


    copyFile: function() {
        source = this.getActiveGrid();
        target = this.getInActiveGrid();
        if (source.isSelected()) {
            if (source.getSelected().attr('id') != '..') {
                data = {};
                data['name'] = source.getSelected().attr('id');
                data['target'] = target.scope.data.data.entry_path;
                data['source'] = source.scope.data.data.entry_path;
                jasHttp.simpleAjaxPost(jasApp.rootUrl + 'manager/copy', data, function (json) {
                    controlService.refreshGrids();
                });
            } else {
                alert('You can\'t copy the parent folder');
            }
        } else {
            alert('There is no selected file');
            return false;
        }
    },

    moveFile: function(){
        source = this.getActiveGrid();
        target = this.getInActiveGrid();
        if (source.isSelected()) {
            if (source.getSelected().attr('id') != '..') {
                data = {};
                data['name'] = source.getSelected().attr('id');
                data['target'] = target.scope.data.data.entry_path;
                data['source'] = source.scope.data.data.entry_path;
                jasHttp.simpleAjaxPost(jasApp.rootUrl + 'manager/move', data, function (json) {
                    controlService.refreshGrids();
                });
            } else {
                alert('You can\'t move the parent folder');
            }
        } else {
            alert('There is no selected file');
            return false;
        }
    },

    createFile: function() {
        var activeGrid = this.getActiveGrid();
        this.modalFormFile.find('input[name="name"]').val('');
        this.modalFormFile.find('textarea[name="content"]').val('');
        this.modalFormFile.find('input[name="parent"]').val(activeGrid.scope.data.data.entry_path);
        this.modalFormFile.modal('show');
    },

    createFolder: function() {
        var activeGrid = this.getActiveGrid();
        this.modalFormFolder.find('input[name="name"]').val('');
        this.modalFormFolder.find('input[name="parent"]').val(activeGrid.scope.data.data.entry_path);
        this.modalFormFolder.modal('show');
    },

    renameObject: function() {
        source = this.getActiveGrid();
        if (source.isSelected()) {
            if (source.getSelected().attr('id') != '..') {
                this.modalFormRename.find('input[name="name"]').val(source.getSelected().attr('id'));
                this.modalFormRename.find('input[name="old_name"]').val(source.getSelected().attr('id'));
                this.modalFormRename.find('input[name="parent"]').val(source.scope.data.data.entry_path);
                this.modalFormRename.modal('show');
                return false;
            } else {
                alert('You can\'t rename the parent folder');
            }
        } else {
            alert('There is no selected file');
            return false;
        }
    },

    deleteObject: function() {
        source = this.getActiveGrid();
        if (source.isSelected()) {
            if (source.getSelected().attr('id') != '..') {
                if (confirm('Are you sure?')) {
                    data = {parent: source.scope.data.data.entry_path, name: source.getSelected().attr('id')};
                    jasHttp.simpleAjaxPost(jasApp.rootUrl + 'manager/deleteObject', data, function (json) {
                        var res = jasHelper.parseResult(json);
                        if (res.success != true) {
                            alert('Deletion has not been success');
                        }
                        controlService.refreshGrids();
                        return false;
                    });
                    return false;
                } else {
                    return false;
                }
            } else {
                alert('You can\'t remove the parent folder');
            }
        } else {
            alert('There is no selected file');
            return false;
        }
    },


    doRename: function() {
        data = this.modalFormRename.find('form').serialize();
        jasHttp.simpleAjaxPost(jasApp.rootUrl + 'manager/renameObject', data, function (json) {
            controlService.refreshGrids();
            controlService.modalFormRename.modal('hide');
        });
    },

    saveFile: function() {
        data = this.modalFormFile.find('form').serialize();
        jasHttp.simpleAjaxPost(jasApp.rootUrl + 'manager/createFile', data, function (json) {
            controlService.refreshGrids();
            controlService.modalFormFile.modal('hide');
        });

    },

    saveFolder: function() {
        data = this.modalFormFolder.find('form').serialize();
        jasHttp.simpleAjaxPost(jasApp.rootUrl + 'manager/createFolder', data, function (json) {
            controlService.refreshGrids();
            controlService.modalFormFolder.modal('hide');
        });
    },


    setActiveByName: function(name) {
        for(var i = 0; i < this.grids.length; i++){
            this.grids[i].idGridHeaderItem.removeClass('active');
            if (this.grids[i].name == name) {
                this.grids[i].idGridHeaderItem.addClass('active');

            }
        }
    }


}



