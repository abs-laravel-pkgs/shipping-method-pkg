@if(config('shipping-method-pkg.DEV'))
    <?php $shipping_method_pkg_prefix = '/packages/abs/shipping-method-pkg/src';?>
@else
    <?php $shipping_method_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var shipping_method_list_template_url = "{{asset($shipping_method_pkg_prefix.'/public/themes/'.$theme.'/shipping-method-pkg/shipping-method/list.html')}}";
    var shipping_method_form_template_url = "{{asset($shipping_method_pkg_prefix.'/public/themes/'.$theme.'/shipping-method-pkg/shipping-method/form.html')}}";
</script>
<script type="text/javascript" src="{{asset($shipping_method_pkg_prefix.'/public/themes/'.$theme.'/shipping-method-pkg/shipping-method/controller.js')}}"></script>
