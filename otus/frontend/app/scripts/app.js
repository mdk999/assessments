'use strict';

/**
 * @ngdoc overview
 * @name otusApp
 * @description
 * # otusApp
 *
 * Main module of the application.
 */
angular
  .module('otusApp', [
    'ngResource',
    'ngRoute',
    'ngSanitize',
    'ngTouch'
  ])
  .config( function($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: 'views/main.html',
      })
      .otherwise({
        redirectTo: '/404',
      templateUrl: '404.html',
      });
  });
