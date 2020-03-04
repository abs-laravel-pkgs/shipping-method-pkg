@if(config('shipping-method-pkg.DEV'))
    <?php $shipping_method_pkg_prefix = '/packages/abs/shipping-method-pkg/src';?>
@else
    <?php $shipping_method_pkg_prefix = '';?>
@endif

<script type="text/javascript">

	app.config(['$routeProvider', function($routeProvider) {

	    $routeProvider.
	    when('/shipping-method-pkg/shipping-method/list', {
	        template: '<shipping-method-list></shipping-method-list>',
	        title: 'Shipping Methods',
	    }).
	    when('/shipping-method-pkg/shipping-method/add', {
	        template: '<shipping-method-form></shipping-method-form>',
	        title: 'Add Shipping Method',
	    }).
	    when('/shipping-method-pkg/shipping-method/edit/:id', {
	        template: '<shipping-method-form></shipping-method-form>',
	        title: 'Edit Shipping Method',
	    });
	}]);


    var shipping_method_list_template_url = "{{asset($shipping_method_pkg_prefix.'/public/themes/'.$theme.'/shipping-method-pkg/shipping-method/list.html')}}";
    var shipping_method_form_template_url = "{{asset($shipping_method_pkg_prefix.'/public/themes/'.$theme.'/shipping-method-pkg/shipping-method/form.html')}}";
</script>
<script type="text/javascript" src="{{asset($shipping_method_pkg_prefix.'/public/themes/'.$theme.'/shipping-method-pkg/shipping-method/controller.js')}}"></script>
