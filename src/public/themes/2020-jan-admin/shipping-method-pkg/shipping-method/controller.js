app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/shipping-method-pkg/shipping-method/list', {
        template: '<shipping-method-list></shipping-method-list>',
        title: 'Shipping Methods',
    }).
    when('/shipping-method-pkg/shipping_method/add', {
        template: '<shipping-method-form></shipping-method-form>',
        title: 'Add Shipping Method',
    }).
    when('/shipping-method-pkg/shipping_method/edit/:id', {
        template: '<shipping-method-form></shipping-method-form>',
        title: 'Edit Shipping Method',
    });
}]);

app.component('shippingMethodList', {
    templateUrl: shipping_method_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var table_scroll;
        table_scroll = $('.page-main-content').height() - 37;
        var dataTable = $('#shipping_methods_list').DataTable({
            "dom": dom_structure,
            "language": {
                "search": "",
                "searchPlaceholder": "Search",
                "lengthMenu": "Rows Per Page _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            stateSave: true,
            pageLength: 10,
            processing: true,
            serverSide: true,
            paging: true,
            ordering: false,
            ajax: {
                url: laravel_routes['getShippingMethodList'],
                data: function(d) {}
            },
            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'question', name: 'shipping_methods.question', searchable: true },
            ],
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(total + '/' + max)
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            },
            initComplete: function() {
                $('.search label input').focus();
            },
        });
        $('.dataTables_length select').select2();
        $('.page-header-content .display-inline-block .data-table-title').html('Shipping Methods <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        $('.add_new_button').html(
            '<a href="#!/shipping-method-pkg/shipping_method/add" type="button" class="btn btn-secondary" dusk="add-btn">' +
            'Add Shipping Method' +
            '</a>'
        );

        $('.btn-add-close').on("click", function() {
            $('#shipping_methods_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#shipping_methods_list').DataTable().ajax.reload();
        });

        $('.dataTables_length select').select2();

        $scope.clear_search = function() {
            $('#search_shipping_method').val('');
            $('#shipping_methods_list').DataTable().search('').draw();
        }

        var dataTables = $('#shipping_methods_list').dataTable();
        $("#search_shipping_method").keyup(function() {
            dataTables.fnFilter(this.value);
        });

        //DELETE
        $scope.deleteShippingMethod = function($id) {
            $('#shipping_method_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#shipping_method_id').val();
            $http.get(
                shipping_method_delete_data_url + '/' + $id,
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'ShippingMethod Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#shipping_methods_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/shipping-method-pkg/shipping_method/list');
                }
            });
        }

        //FOR FILTER
        $('#shipping_method_code').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#shipping_method_name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#mobile_no').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#email').on('keyup', function() {
            dataTables.fnFilter();
        });
        $scope.reset_filter = function() {
            $("#shipping_method_name").val('');
            $("#shipping_method_code").val('');
            $("#mobile_no").val('');
            $("#email").val('');
            dataTables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('shippingMethodForm', {
    templateUrl: shipping_method_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        get_form_data_url = typeof($routeParams.id) == 'undefined' ? laravel_routes['getShippingMethodFormData'] : laravel_routes['getShippingMethodFormData'] + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http({
            url: laravel_routes['getShippingMethodFormData'],
            method: 'GET',
            params: {
                'id': typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
            }
        }).then(function(response) {
            self.shipping_method = response.data.shipping_method;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.shipping_method.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
        });

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'question': {
                    required: true,
                    minlength: 3,
                    maxlength: 255,
                },
                'answer': {
                    required: true,
                    minlength: 3,
                    maxlength: 255,
                },
            },
            invalidHandler: function(event, validator) {
                checkAllTabNoty()
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveShippingMethod'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message)
                            $location.path('/shipping-method-pkg/shipping-method/list');
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                showErrorNoty(res)
                            } else {
                                $('#submit').button('reset');
                                $location.path('/shipping-method-pkg/shipping-method/list');
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        showServerErrorNoty()
                    });
            }
        });
    }
});