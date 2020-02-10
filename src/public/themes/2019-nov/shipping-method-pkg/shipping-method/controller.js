app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/shipping-methods', {
        template: '<shipping-methods></shipping-methods>',
        title: 'ShippingMethods',
    });
}]);

app.component('shippingMethods', {
    templateUrl: shipping_method_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http({
            url: laravel_routes['getShippingMethods'],
            method: 'GET',
        }).then(function(response) {
            self.shipping_methods = response.data.shipping_methods;
            $rootScope.loading = false;
        });
        $rootScope.loading = false;
    }
});