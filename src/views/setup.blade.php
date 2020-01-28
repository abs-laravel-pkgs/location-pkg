@if(config('custom.PKG_DEV'))
    <?php $location_pkg_prefix = '/packages/abs/location-pkg/src';?>
@else
    <?php $location_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var admin_theme = "{{$theme}}";
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
