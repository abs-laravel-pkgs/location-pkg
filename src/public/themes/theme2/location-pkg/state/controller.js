app.component('stateList', {
    templateUrl: state_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element, $mdSelect) {
        $scope.loading = true;
        var self = this;
        self.theme = admin_theme;
        self.hasPermission = HelperService.hasPermission;
        var dataTable = $('#state_list').DataTable({
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
            stateSave: true,
            "ordering": false,
            ajax: {
                url: laravel_routes['getStateList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.state_code = $('#code').val();
                    d.state_name = $('#name').val();
                    d.status = $('#status').val();
                    d.filter_country_id = $('#filter_country_id').val();
                },
            },
            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'name', name: 'states.name' },
                { data: 'code', name: 'states.code' },
                { data: 'regions_count', name: 'regions', searchable: false },
                { data: 'cities_count', name: 'cities', searchable: false },
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
        $('.page-header-content .display-inline-block .data-table-title').html('States <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        if (self.hasPermission('add-state')) {
            // var addnew_block = $('#add_new_wrap').html();
            $('.page-header-content .alignment-right .add_new_button').html(
                '<a href="#!/location-pkg/state/add" role="button" class="btn btn-secondary">Add New</a>' +
                '<a role="button" id="open" data-toggle="modal"  data-target="#modal-state-filter" class="btn btn-img"> <img src="' + image_scr + '" alt="Filter" onmouseover=this.src="' + image_scr1 + '" onmouseout=this.src="' + image_scr + '"></a>'
                // '' + addnew_block + ''
            );
        }
        $('.btn-add-close').on("click", function() {
            $('#state_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#state_list').DataTable().ajax.reload();
        });

        //FOCUS ON SEARCH FIELD
        setTimeout(function() {
            $('div.dataTables_filter input').focus();
        }, 2500);

        //DELETE
        $scope.deleteState = function($id) {
            $('#state_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#state_id').val();
            $http.get(
                laravel_routes['deleteState'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'State Deleted Successfully');
                    $('#state_list').DataTable().ajax.reload();
                    $location.path('/location-pkg/state/list');
                }
            });
        }

        //FOR FILTER
        $http.get(
            laravel_routes['getStateFilter']
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
        $scope.clearSearchTerm = function() {
            $scope.searchTerm = '';
            $scope.searchTerm1 = '';
        };
        /* Modal Md Select Hide */
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });

        var datatables = $('#state_list').dataTable();
        $('#name').on('keyup', function() {
            datatables.fnFilter();
        });
        $('#code').on('keyup', function() {
            datatables.fnFilter();
        });
        $scope.onSelectedStatus = function(val) {
            $("#status").val(val);
            datatables.fnFilter();
        }
        $scope.onSelectedCountry = function(val) {
            $("#filter_country_id").val(val);
            datatables.fnFilter();
        }
        $scope.reset_filter = function() {
            $("#name").val('');
            $("#code").val('');
            $("#filter_country_id").val('');
            $("#status").val('');
            datatables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('stateForm', {
    templateUrl: state_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        //get_form_data_url = typeof($routeParams.id) == 'undefined' ? state_get_form_data_url : state_get_form_data_url + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.region_permission = self.hasPermission('regions');
        self.city_permission = self.hasPermission('cities');
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['getStateFormData'], {
                params: {
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                }
            }
        ).then(function(response) {
            // console.log(response);
            self.state = response.data.state;
            self.country_list = response.data.country_list;
            self.region_list = response.data.region_list;
            self.city_list = response.data.city_list;
            self.action = response.data.action;
            self.theme = response.data.theme;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.state.deleted_at) {
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

        //ADD REGIONS
        $scope.add_region = function() {
            self.region_list.push({
                switch_value: 'Active',
            });
        }
        //REMOVE REGIONS
        self.region_list_id = [];
        $scope.removeRegion = function(index, region_id) {
            // console.log(index, region_id);
            if (region_id) {
                self.region_list_id.push(region_id);
                $('#removed_region_id').val(JSON.stringify(self.region_list_id));
            }
            self.region_list.splice(index, 1);
        }

        //ADD CITIES
        $scope.add_city = function() {
            self.city_list.push({
                switch_value: 'Active',
            });
        }
        //REMOVE CITIES
        self.city_list_id = [];
        $scope.removeCity = function(index, city_id) {
            if (city_id) {
                self.city_list_id.push(city_id);
                $('#removed_city_id').val(JSON.stringify(self.city_list_id));
            }
            self.city_list.splice(index, 1);
        }

        //MULTIPLE VALIDATION FOR REGION
        jQuery.validator.addClassRules('region_code', {
            required: true,
            minlength: 1,
            maxlength: 4,
        });
        jQuery.validator.addClassRules('region_name', {
            required: true,
            minlength: 3,
            maxlength: 191,
        });

        //MULTIPLE VALIDATION FOR CITY
        jQuery.validator.addClassRules('city_name', {
            required: true,
            minlength: 3,
            maxlength: 191,
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
                    maxlength: 191,
                },
                'country_id': {
                    required: true,
                },
            },
            invalidHandler: function(event, validator) {
                custom_noty('error', 'You have errors,Please check all tabs');
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('.submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveState'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message);
                            $location.path('/location-pkg/state/list');
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
                                $location.path('/location-pkg/state/list');
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
app.component('stateView', {
    templateUrl: state_view_template_url,
    controller: function($http, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.region_permission = self.hasPermission('regions');
        self.city_permission = self.hasPermission('cities');
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['viewState'], {
                params: {
                    id: $routeParams.id,
                }
            }
        ).then(function(response) {
            // console.log(response);
            self.state = response.data.state;
            self.regions = response.data.regions;
            self.cities = response.data.cities;
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