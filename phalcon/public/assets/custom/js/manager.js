if (typeof jasGridService == "undefined") var jasGridService = new Object();

jasGridService = {

    getAjaxList: function(grid, url){
        grid.scope.url = (url && typeof url != 'undefined') ? ((url != '') ? url : grid.defaultUrl) : grid.defaultUrl;
        grid.scope.data = null;
        grid.scope.params[jasApp.csrfName] = jasApp.csrfValue;

        var p = grid.idGridItem.parents('.content')[0];
        var loading = $('<div class="loading"><i class="fa fa-refresh fa-spin"></i></div>');

        loading.appendTo(p);
        loading.fadeIn();

        jasHttp.getData(grid.scope, function(scope, res){
            grid.scope = scope;
            loading.fadeOut();
            grid.showList(grid, scope, res)

        }, function(scope, res){
            jasHelper.errorHandler(res)
            loading.fadeOut();
        });
    },


    getExternalAjaxList: function(grid, externalData){
        externalData[jasApp.csrfName] = jasApp.csrfValue;
        grid.scope.url = id.defaultUrl;
        grid.scope.data = null;
        grid.scope.params = externalData;

        jasHttp.getData(grid.scope, function(res){
            grid.showList(grid, res)
        }, function(){
            jasHelper.errorHandler(res)
        });
    },


    showAjaxList: function(grid, scope, res){

        res = jasHelper.parseResult(res);

        if (res['success']){

            if (! grid.behaviour.infinity) {
                grid.clearSelected(grid);
                grid.idGridItem.empty();
            }

            if (grid.behaviour.id_no_result_item) {
                grid.behaviour.id_no_result_item.empty();
                grid.behaviour.id_no_result_item.hide();
            }

            grid.idPaginationItem.empty();
            if (grid.idLimitSelectorItem){
                grid.idLimitSelectorItem.empty();
            }
            var a = res.data;


            if (a){
                if (a.length > 0){

                    for(i = 0; i < a.length; i++){

                        grid.idGridItem.append(a[i]);

                    }

                    grid.idPaginationItem.empty();
                    grid.idPaginationItem.append(res.pagination);
                    if (res.pagination == ''){
                        grid.idPaginationItem.hide();
                    } else
                    {
                        grid.idPaginationItem.show();
                    }


                    grid.idPaginationItem.find('a').click(function() {
                        grid.getList(grid, $(this).attr('href'));
                        return false;
                    });


                    if (grid.idLimitSelectorItem){
                        grid.idLimitSelectorItem.append(res.pagination_limit_selector);
                        if (res.pagination_limit_selector == ''){
                            grid.idLimitSelectorItem.hide();
                        } else
                        {
                            grid.idLimitSelectorItem.show();
                        }

                        grid.idLimitSelectorItem.find('select').change(function() {
                            grid.refreshList(grid, {'ci_limit_link' : $(this).val()});
                            return false;
                        });

                    }


                } else
                {

                    if (grid.behaviour.id_no_result_item) {
                        grid.behaviour.id_no_result_item.append(grid.behaviour.no_result);
                        grid.behaviour.id_no_result_item.show();
                    }
                    if (grid.idLimitSelectorItem){
                        grid.idLimitSelectorItem.hide();
                    }
                }
            }else
            {
                if (grid.behaviour.id_no_result_item) {
                    grid.behaviour.id_no_result_item.append(grid.behaviour.no_result);
                    grid.behaviour.id_no_result_item.show();
                }
                if (grid.idLimitSelectorItem){
                    grid.idLimitSelectorItem.hide();
                }
            }

        } else
        {
            jasHelper.errorHandler(res);
            return false;
        }

        /*
         if (res.callbacks){
         if (res.callbacks.length > 0){
         var calls = res.callbacks;
         for(i = 0; i < calls.length; i++){
         var adder = new Function(calls[i]);
         adder();
         }
         }
         }


         if (res.ajax_refresh_links){
         if (res.ajax_refresh_links.length > 0){
         var links = res.ajax_refresh_links;
         for(i = 0; i < links.length; i++){
         $(links[i]).unbind('click');
         $(links[i]).click(function(e){
         if (grid.behaviour.infinity) {
         grid.clearSelected(grid);
         $("#"+grid.idGridItem).empty();
         }
         grid.getList(grid, $(this).attr("href"));
         grid.lastClickedItem = $(this);
         return false;
         });
         }
         }
         }


         if (res.ajax_action_links){
         if (res.ajax_action_links.length > 0){
         var links = res.ajax_action_links;
         for(i = 0; i < links.length; i++){
         $(links[i]).unbind('click');
         $(links[i]).click(function(e){

         grid.getResult(grid, $(this).attr("href"),jasApp.csrf, function(json){

         if (!(typeof json =='object')){
         var res = jQuery.parseJSON(json);
         } else
         {
         var res = json;
         }
         if (res.success) {
         if (! (Object.prototype.toString.call( res.data ) === '[object Object]')){
         var adder = new Function(res.data);
         adder();
         }
         }else
         {
         jasHelper.errorHandler(res);
         }

         });
         return false;

         });
         }
         }
         }


         if (res.ajax_delete_links){
         if (res.ajax_delete_links.length > 0){
         var links = res.ajax_delete_links;
         for(i = 0; i < links.length; i++){
         $(links[i]).unbind('click');
         $(links[i]).click(function(e){
         e.preventDefault();
         if (confirm(grid.behaviour.are_u_sure)) {
         grid.getResult(grid, $(this).attr("href"),jasApp.csrf,function(res){
         if (grid.behaviour.refresh_after_delete) {
         grid.getList(grid);
         }
         if (res.success) {
         if (! (Object.prototype.toString.call( res.data ) === '[object Object]')){
         var adder = new Function(res.data);
         adder();
         }
         } else
         {
         jasHelper.errorHandler(res);
         }

         });

         }
         return false;
         });
         }
         }
         }
         */
    },

    /*
     saveToCsv: function(external_data){
     var me = this;
     external_data[me.csrfName] = me.csrfValue;
     me.getResult(this.defaultUrl, external_data, function(json){
     if (!(typeof json =='object')){
     var res = jQuery.parseJSON(json);
     } else
     {
     var res = json;
     }
     document.location = res.data;
     });
     },
     */

    clearAjaxList: function(grid){
        grid.clearSelected();
        grid.idGridItem.empty();
        grid.idPaginationItem.empty();
    },


    /*
     hasCiSelection: function(){
     var me = this;
     if (me.selectedItem){
     return true;
     } else
     {
     return false;
     }
     },


     selectCiRow: function(row){
     var me = this;
     me.clearSelected();
     me.selectedItem = row;
     row.addClass(me.selected_class);
     return false;
     },



     getCiSelection: function(){
     var me = this;
     if (me.selectedItem){
     return me.selectedItem;
     } else
     {
     return false;
     }
     },
     */

    clearGridSelection: function(grid){
        var grid = this;
        if (grid.selectedItem){
            grid.selectedItem.removeClass(grid.selected_class);
            grid.selectedItem = null;
        }
    }

}

/**
 *
 * @param objname
 * @param objData
 */
function jasGridInstance(objname, objData){

    this.name =  objname;
    this.scope = {
        params: {
        },
        method: 'POST',
        url: '',
        data: null
    };
    this.defaultUrl = objData['default_url'];
    this.idGridItem = objData['id_grid_item'];
    this.idPaginationItem = objData['id_pagination_item'];
    this.idLimitSelectorItem = objData['id_limit_selector_item'];
    this.selectedItem = false;
    this.lastClickedItem = null;

    if (objData['behaviour'] && typeof objData['behaviour'] != 'undefined') {
        this.behaviour = {
            'infinity': (objData['behaviour']['infinity'] ? objData['behaviour']['infinity'] : false),
            'refresh_after_delete': ((objData['behaviour']['refresh_after_delete'] === false) ? false : true),
            'selected_class': ((objData['behaviour']['selected_class']) ? objData['behaviour']['selected_class'] : 'selected'),
            'are_u_sure': ((objData['behaviour']['are_u_sure']) ? objData['behaviour']['are_u_sure'] : 'Are you sure?'),
            'no_result': ((objData['behaviour']['no_result']) ? objData['behaviour']['no_result'] : 'No results found!'),
            'id_no_result_item': ((objData['behaviour']['id_no_result_item']) ? objData['behaviour']['id_no_result_item'] : objData['id_pagination_item']),
            'export_link': ((objData['ci_export_link']) ? objData['ci_export_link'] :'id_ci_export_link')
        }
    } else {
        this.behaviour = {
            'infinity': false,
            'refresh_after_delete': true,
            'selected_class': 'selected',
            'are_u_sure': 'Are you sure?',
            'no_result': 'No results found!',
            'id_no_result_item': objData['id_pagination_item'],
            'export_link': 'id_ci_export_link'
        }
    }

    //console.log(this);

    this.getList = jasGridService.getAjaxList;

    this.showList = jasGridService.showAjaxList;

    this.refreshList = jasGridService.getExternalAjaxList;

    //this.downloadList = saveToCsv;

    //this.isSelected = hasCiSelection;

    //this.setSelected = selectCiRow;

    //this.getSelected = getCiSelection;

    this.clearSelected = jasGridService.clearGridSelection;

    this.clearList = jasGridService.clearAjaxList;

    //this.showLoader = showAjaxLoader;

    //this.hideLoader = hideAjaxLoader;

    this.clearList(this);
    /*
     var me = this;
     if ($("#"+this.export_link)){
     $("#"+this.export_link).unbind('click');
     $("#"+this.export_link).bind('click', function(){
     me.downloadList({'ci_export_link' : true} );
     return false;
     });
     }
     */

}


function jasLimitSelectorInstance(objName, objData, grid){
    this.name =  objName;
    this.idSelectorItem = objData['id_limit_selector_item'];
    this.grid = grid;
    var me = this;
    this.idSelectorItem.bind('change', function(){
        me.grid.scope.params['limit'] = $(this).val();
        jasGridService.getAjaxList(me.grid);
    });
}





