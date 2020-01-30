app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    //CUSTOMER
    when('/location-pkg/country/list', {
        template: '<country-list></country-list>',
        title: 'Countries',
    }).
    when('/location-pkg/country/add', {
        template: '<country-form></country-form>',
        title: 'Add Country',
    }).
    when('/location-pkg/country/edit/:id', {
        template: '<country-form></country-form>',
        title: 'Edit Country',
    }).
    when('/location-pkg/country/view/:id', {
        template: '<country-view></country-view>',
        title: 'View Country',
    });
}]);

app.component('countryList', {
    templateUrl: country_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $element, $mdSelect) {
        $scope.loading = true;
        var self = this;
        self.theme = admin_theme;
        self.hasPermission = HelperService.hasPermission;
        var dataTable = $('#country_list').DataTable({
            "dom": dom_structure,
            "language": {
                "search": "",
                "searchPlaceholder": "Search",
                "lengthMenu": "Rows _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            "ordering": false,
            processing: true,
            serverSide: true,
            paging: true,
            stateSave: true,
            ajax: {
                url: laravel_routes['getCountryList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.country_code = $('#code').val();
                    d.country_name = $('#name').val();
                    d.iso_code = $('#iso_code').val();
                    d.status = $('#status').val();
                },
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'name', name: 'countries.name' },
                { data: 'code', name: 'countries.code' },
                { data: 'iso_code', name: 'countries.iso_code' },
                { data: 'mobile_code', name: 'countries.mobile_code' },
                { data: 'states', name: 'states', searchable: false },
            ],
            "initComplete": function(settings, json) {
                $('.dataTables_length select').select2();
                $('#modal-loading').modal('hide');
            },
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(total + ' / ' + max)
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });

        /* Page Title Appended */
        $('.page-header-content .display-inline-block .data-table-title').html('Countries <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        if (self.hasPermission('add-country')) {
            // var addnew_block = $('#add_new_wrap').html();
            $('.page-header-content .alignment-right .add_new_button').html(
                '<a href="#!/location-pkg/country/add" role="button" class="btn btn-secondary">Add New</a>' +
                '<a role="button" id="open" data-toggle="modal"  data-target="#modal-country-filter" class="btn btn-img"> <img src="' + image_scr + '" alt="Filter" onmouseover=this.src="' + image_scr1 + '" onmouseout=this.src="' + image_scr + '"></a>'
                // '' + addnew_block + ''
            );
        }
        $('.btn-add-close').on("click", function() {
            $('#country_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#country_list').DataTable().ajax.reload();
        });

        //FOCUS ON SEARCH FIELD
        setTimeout(function() {
            $('div.dataTables_filter input').focus();
        }, 2500);

        //DELETE
        $scope.deleteCountry = function($id) {
            $('#country_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#country_id').val();
            $http.get(
                laravel_routes['deleteCountry'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'Country Deleted Successfully');
                    $('#country_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/location-pkg/country/list');
                }
            });
        }

        //FOR FILTER
        self.status = [
            { id: '', name: 'Select Status' },
            { id: '1', name: 'Active' },
            { id: '0', name: 'Inactive' },
        ];
        $element.find('input').on('keydown', function(ev) {
            ev.stopPropagation();
        });
        /* Modal Md Select Hide */
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });

        var datatables = $('#country_list').dataTable();
        $('#name').on('keyup', function() {
            datatables.fnFilter();
        });
        $('#code').on('keyup', function() {
            datatables.fnFilter();
        });
        $('#iso_code').on('keyup', function() {
            datatables.fnFilter();
        });
        $scope.onSelectedStatus = function(val) {
            $("#status").val(val);
            datatables.fnFilter();
        }
        $scope.reset_filter = function() {
            $("#name").val('');
            $("#code").val('');
            $("#iso_code").val('');
            $("#status").val('');
            datatables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('countryForm', {
    templateUrl: country_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        //get_form_data_url = typeof($routeParams.id) == 'undefined' ? country_get_form_data_url : country_get_form_data_url + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.state_permission = self.hasPermission('states')
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['getCountryFormData'], {
                params: {
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                }
            }
        ).then(function(response) {
            // console.log(response);
            self.country = response.data.country;
            self.state_list = response.data.state_list;
            self.action = response.data.action;
            self.theme = response.data.theme;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.country.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
        });

        //ADD STATE
        $scope.add_state = function() {
            self.state_list.push({
                switch_value: 'Active',
            });
        }
        //REMOVE STATE
        self.remove_state_id = [];
        $scope.removestate = function(index, state_id) {
            if (state_id) {
                self.remove_state_id.push(state_id);
                $("#removed_state_id").val(JSON.stringify(self.remove_state_id));
            }
            self.state_list.splice(index, 1);
        }

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-pills').on("click", function() {
            tabPaneFooter();
        });
        $scope.btnNxt = function() {}
        $scope.prev = function() {}


        //VALIDATEOR FOR MULTIPLE 
        jQuery.validator.addClassRules("state_name", {
            required: true,
            minlength: 3,
            maxlength: 191,
        });
        jQuery.validator.addClassRules("state_code", {
            required: true,
            minlength: 1,
            maxlength: 2,
        });

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'code': {
                    required: true,
                    minlength: 1,
                    maxlength: 2,
                },
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 64,
                },
                'iso_code': {
                    required: true,
                    minlength: 1,
                    maxlength: 3,
                },
                'mobile_code': {
                    maxlength: 10,
                },
            },
            invalidHandler: function(event, validator) {
                custom_noty('error', 'You have errors,Please check all tabs');
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('.submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveCountry'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message);
                            $location.path('/location-pkg/country/list');
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('.submit').button('reset');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                custom_noty('error', errors);
                            } else {
                                $('.submit').button('reset');
                                $location.path('/location-pkg/country/list');
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('.submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            }
        });
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('countryView', {
    templateUrl: country_view_template_url,
    controller: function($http, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.state_permission = self.hasPermission('states');
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['viewCountry'], {
                params: {
                    id: $routeParams.id,
                }
            }
        ).then(function(response) {
            // console.log(response);
            self.country = response.data.country;
            self.states = response.data.state_list;
            self.action = response.data.action;
            self.theme = response.data.theme;
        });

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-pills').on("click", function() {
            tabPaneFooter();
        });
        $scope.btnNxt = function() {}
        $scope.prev = function() {}
    }
});
