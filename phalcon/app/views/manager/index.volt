<div class="page-header">
    <h1>File Manager</h1>
</div>

<div class="page-content">
    <div class="col-lg-12 manager">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 manager-left">
                <div class="col-lg-12 manager-list" data-gridname="left">
                    <div class="row">
                        <div id="result-list-left-header" class="col-lg-12 manager-header active">
                            Header
                        </div>
                    </div>
                    <div class="row">
                        <ul id="result-list-left" class="result-list">

                        </ul>
                        <div id="result-list-left-pagination"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 manager-right">
                <div class="col-lg-12 manager-list" data-gridname="right">
                    <div class="row">
                        <div id="result-list-right-header" class="col-lg-12 manager-header">
                            Header
                        </div>
                    </div>
                    <div class="row">
                        <ul id="result-list-right" class="result-list">

                        </ul>
                        <div id="result-list-right-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="controller" class="col-lg-12 manager-actions text-center">
                <a class="btn btn-success btn-create-file" href="#">Create File</a>
                <a class="btn btn-success btn-create-folder" href="#">Create Folder</a>
                <a class="btn btn-success btn-copy" href="#">Copy</a>
                <a class="btn btn-success btn-move" href="#">Move</a>
                <a class="btn btn-success btn-rename" href="#">Rename</a>
                <a class="btn btn-danger btn-delete" href="#">Delete</a>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="newFile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="newFormLabel"></h4>
            </div>
            <div class="modal-body">

                <form>
                    <div class="form-group">
                        <label for="name">Filename</label>
                        <input type="text" class="form-control" id="name" placeholder="Filename" value="" name="name" >
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea type="text" class="form-control" id="content" placeholder="" name="content"></textarea>
                    </div>
                    <input type="hidden" name="parent" value="">
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="newFileSave" type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="newFolder" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="newFolderLabel">New Folder</h4>
            </div>
            <div class="modal-body">

                <form>
                    <div class="form-group">
                        <label for="name">Folder Name</label>
                        <input type="text" class="form-control" id="name" placeholder="Folder Name" value="" name="name">
                    </div>
                    <input type="hidden" name="parent" value="">
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="newFolderSave" type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="renameForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="renameFormLabel">New Folder</h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="name">New Name</label>
                        <input type="text" class="form-control" id="name" placeholder="New Name" value="" name="name">
                    </div>
                    <input type="hidden" name="old_name" value="">
                    <input type="hidden" name="parent" value="">
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="renameFormSave" type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>


<script>


    $(document).ready(function(){

        initData = new Array();
        initData['default_url'] = jasApp.rootUrl + '/manager/scanFolder';
        initData['id_grid_item'] = $('#result-list-left');
        initData['id_pagination_item'] = $('#result-list-left-pagination');
        initData['id_header_item'] = $('#result-list-left-header');
        initData['behaviour'] = [];
        initData['behaviour']['infinity'] = false;
        initData['behaviour']['refresh_after_delete'] = true;
        initData['behaviour']['no_result'] = '<div class="alert">Please create a file.</div>'; //optional

        var leftGrid = new jasGridInstance('left',initData);

        initData = new Array();
        initData['default_url'] = jasApp.rootUrl + '/manager/scanFolder';
        initData['id_grid_item'] = $('#result-list-right');
        initData['id_pagination_item'] = $('#result-list-right-pagination');
        initData['id_header_item'] = $('#result-list-right-header');
        initData['behaviour'] = [];
        initData['behaviour']['infinity'] = false;
        initData['behaviour']['refresh_after_delete'] = true;
        initData['behaviour']['no_result'] = '<div class="alert">Please create a file</div>'; //optional

        var rightGrid = new jasGridInstance('right',initData);

        var initData = new Array();
        initData['controller'] = $('#controller');
        initData['modal_form_file'] = $('#newFile');
        initData['modal_form_folder'] = $('#newFolder');
        initData['modal_form_rename'] = $('#renameForm');
        controlService.init(initData, leftGrid, rightGrid);

    });


</script>