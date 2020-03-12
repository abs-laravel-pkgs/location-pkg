@if(config('location-pkg.DEV'))
    <?php $location_pkg_prefix = '/packages/abs/location-pkg/src';?>
@else
    <?php $location_pkg_prefix = '';?>
@endif

<script type="text/javascript">
app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    //COUNTRY
    when('/location-pkg/country/list', {
        template: '<country-list-pkg></country-list-pkg>',
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
    }).

    //STATE
    when('/location-pkg/state/list', {
        template: '<state-list></state-list>',
        title: 'States',
    }).
    when('/location-pkg/state/add', {
        template: '<state-form></state-form>',
        title: 'Add State',
    }).
    when('/location-pkg/state/edit/:id', {
        template: '<state-form></state-form>',
        title: 'Edit State',
    }).
    when('/location-pkg/state/view/:id', {
        template: '<state-view></state-view>',
        title: 'View State',
    }).

    //CITY
    when('/location-pkg/city/list', {
        template: '<city-list-pkg></city-list-pkg>',
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
    }).

    //REGION
    when('/location-pkg/region/list', {
        template: '<region-list></region-list>',
        title: 'Regions',
    }).
    when('/location-pkg/region/add', {
        template: '<region-form></region-form>',
        title: 'Add Region',
    }).
    when('/location-pkg/region/edit/:id', {
        template: '<region-form></region-form>',
        title: 'Edit Region',
    }).
    when('/location-pkg/region/view/:id', {
        template: '<region-view></region-view>',
        title: 'View Region',
    });

}]);

    var admin_theme = '{{$theme}}';
    var country_list_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/country/list.html')}}";
    var country_form_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/country/form.html')}}";
    var country_view_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/country/view.html')}}";
</script>
<script type="text/javascript" src="{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/country/controller.js?v=2')}}"></script>

<script type="text/javascript">
    var state_list_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/state/list.html')}}";
    var state_form_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/state/form.html')}}";
    var state_view_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/state/view.html')}}";
</script>
<script type="text/javascript" src="{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/state/controller.js?v=2')}}"></script>

<script type="text/javascript">
    var city_list_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/city/list.html')}}";
    var city_form_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/city/form.html')}}";
    var city_view_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/city/view.html')}}";
</script>
<script type="text/javascript" src="{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/city/controller.js?v=2')}}"></script>

<script type="text/javascript">
    var region_list_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/region/list.html')}}";
    var region_form_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/region/form.html')}}";
    var region_view_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/region/view.html')}}";
</script>
<script type="text/javascript" src="{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/region/controller.js?v=2')}}"></script>
