@if(config('custom.PKG_DEV'))
    <?php $location_pkg_prefix = '/packages/abs/location-pkg/src';?>
@else
    <?php $location_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var country_list_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/country/list.html')}}";
    var country_form_template_url = "{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/country/form.html')}}";
</script>
<script type="text/javascript" src="{{asset($location_pkg_prefix.'/public/themes/'.$theme.'/location-pkg/country/controller.js?v=2')}}"></script>

<script type="text/javascript">
    var state_list_template_url = "{{URL::asset($location_pkg_prefix.'/public/angular/location-pkg/pages/state/list.html')}}";
    var state_get_form_data_url = "{{url('location-pkg/state/get-form-data/')}}";
    var state_form_template_url = "{{URL::asset($location_pkg_prefix.'/public/angular/location-pkg/pages/state/form.html')}}";
    var state_delete_data_url = "{{url('location-pkg/state/delete/')}}";
</script>
<script type="text/javascript" src="{{URL::asset($location_pkg_prefix.'/public/angular/location-pkg/pages/state/controller.js?v=2')}}"></script>

<script type="text/javascript">
    var city_list_template_url = "{{URL::asset($location_pkg_prefix.'/public/angular/location-pkg/pages/city/list.html')}}";
    var city_get_form_data_url = "{{url('location-pkg/city/get-form-data/')}}";
    var city_form_template_url = "{{URL::asset($location_pkg_prefix.'/public/angular/location-pkg/pages/city/form.html')}}";
    var city_delete_data_url = "{{url('location-pkg/city/delete/')}}";
</script>
<script type="text/javascript" src="{{URL::asset($location_pkg_prefix.'/public/angular/location-pkg/pages/city/controller.js?v=2')}}"></script>

<script type="text/javascript">
    var region_list_template_url = "{{URL::asset($location_pkg_prefix.'/public/angular/location-pkg/pages/region/list.html')}}";
    var region_get_form_data_url = "{{url('location-pkg/region/get-form-data/')}}";
    var region_form_template_url = "{{URL::asset($location_pkg_prefix.'/public/angular/location-pkg/pages/region/form.html')}}";
    var region_delete_data_url = "{{url('location-pkg/region/delete/')}}";
</script>
<script type="text/javascript" src="{{URL::asset($location_pkg_prefix.'/public/angular/location-pkg/pages/region/controller.js?v=2')}}"></script>
