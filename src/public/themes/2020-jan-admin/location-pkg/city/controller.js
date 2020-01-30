app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/location-pkg/city/list', {
        template: '<city-list></city-list>',
        title: 'Cities',
    }).
    when('/location-pkg/city/add', {
        template: '<city-form></city-form>',
        title: 'Add City',
    }).
    when('/location-pkg/city/edit/:id', {
        template: '<city-form></city-form>',
        title: 'Edit City',
    }).
    when('/location-pkg/city/view/:id', {
        template: '<city-view></city-view>',
        title: 'View City',
    });
}]);

app.component('cityList', {
    templateUrl: city_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element, $mdSelect) {
        $scope.loading = true;
        var self = this;
        self.theme = admin_theme;
        self.hasPermission = HelperService.hasPermission;
        var dataTable = $('#city_list').DataTable({
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
            processing: true,
            serverSide: true,
            paging: true,
            "ordering": false,
            stateSave: true,
            ajax: {
                url: laravel_routes['getCityList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.city_name = $('#name').val();
                    d.filter_state_id = $('#filter_state_id').val();
                    d.country_id = $('#country_id').val();
                    d.status = $('#status').val();
                },
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'name', name: 'cities.name' },
                { data: 'state_name', name: 'states.name' },
                { data: 'state_code', name: 'states.code' },
                { data: 'country_name', name: 'countries.name' },
                { data: 'country_code', name: 'countries.code' },
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
        $('.page-header-content .display-inline-block .data-table-title').html('Cities <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        if (self.hasPermission('add-city')) {
            // var addnew_block = $('#add_new_wrap').html();
            $('.page-header-content .alignment-right .add_new_button').html(
                '<a href="#!/location-pkg/city/add" role="button" class="btn btn-secondary">Add New</a>' +
                '<a role="button" id="open" data-toggle="modal"  data-target="#modal-city-filter" class="btn btn-img"> <img src="' + image_scr + '" alt="Filter" onmouseover=this.src="' + image_scr1 + '" onmouseout=this.src="' + image_scr + '"></a>'
                // '' + addnew_block + ''
            );
        }
        $('.btn-add-close').on("click", function() {
            $('#city_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#city_list').DataTable().ajax.reload();
        });

        //FOCUS ON SEARCH FIELD
        setTimeout(function() {
            $('div.dataTables_filter input').focus();
        }, 2500);

        //DELETE
        $scope.deleteCity = function($id) {
            $('#city_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#city_id').val();
            $http.get(
                laravel_routes['deleteCity'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'City Deleted Successfully');
                    $('#city_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/location-pkg/city/list');
                }
            });
        }

        //FOR FILTER
        $http.get(
            laravel_routes['getCityFilter']
        ).then(function(response) {
            // console.log(response);
            self.country_list = response.data.country_list;
        });
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

        //SELECT STATE BASED COUNTRY
        $scope.onSelectedCountry = function(id) {
            if (id) {
                self.state_list = [];
                $("#country_id").val(id);
                datatables.fnFilter();
                $http.get(
                    laravel_routes['getStateBasedCountry'], {
                        params: {
                            country_id: id,
                        }
                    }
                ).then(function(response) {
                    angular.forEach(response.data.state_list, function(value, key) {
                        self.state_list.push({
                            id: value.id,
                            name: value.name,
                        });
                    });
                });
            }
        }

        var datatables = $('#city_list').dataTable();
        $('#name').on('keyup', function() {
            datatables.fnFilter();
        });
        $scope.onSelectedStatus = function(val) {
            $("#status").val(val);
            datatables.fnFilter();
        }
        $scope.onSelectedState = function(val) {
            $("#filter_state_id").val(val);
            datatables.fnFilter();
        }
        $scope.reset_filter = function() {
            $("#name").val('');
            $("#code").val('');
            $("#status").val('');
            $("#filter_state_id").val('');
            datatables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('cityForm', {
    templateUrl: city_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['getCityFormData'], {
                params: {
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                }
            }
        ).then(function(response) {
            // console.log(response);
            self.city = response.data.city;
            self.state_list = response.data.state_list;
            self.theme = response.data.theme;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.city.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
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

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 255,
                },
                'state_id': {
                    required: true,
                },
            },
            // invalidHandler: function(event, validator) {
            //     custom_noty('error', 'You have errors,Please check all tabs');
            // },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveCity'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message);
                            $location.path('/location-pkg/city/list');
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                custom_noty('error', errors);
                            } else {
                                $('#submit').button('reset');
                                $location.path('/location-pkg/city/list');
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            }
        });
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('cityView', {
    templateUrl: city_view_template_url,
    controller: function($http, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['viewCity'], {
                params: {
                    id: $routeParams.id,
                }
            }
        ).then(function(response) {
            // console.log(response);
            self.city = response.data.city;
            self.action = response.data.action;
            self.theme = response.data.theme;
        });
    }
});
