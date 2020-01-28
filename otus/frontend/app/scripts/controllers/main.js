'use strict';

/**
 * @ngdoc function
 * @name otusApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the otusApp
 */
angular.module('otusApp')
  .controller('MainCtrl', function($scope,$http,$httpParamSerializer) {

  /**
   * globally used variables
   *
   */
    //let model = this; //localized?

    $scope.apiUrl = 'http://localhost:9001/api'; //could be passed in

    $scope.students = $scope.classes = [];

    $scope.searchFirstName = $scope.searchLastName = '';

    $scope.showResults = false;

    $scope.showNotFound = false;

    $scope.toggle = [];

  /**
   * This method toggles display of details
   *
   * @returns void
   */
    $scope.toggleFilter = (idx) => {
      $scope.toggle[idx] = !$scope.toggle[idx];
    };

  /**
   * This method loads initial data we may need
   *
   * @returns boolean
   */
  $scope.init = () => {

        $scope.getClasses();

    };

  /**
   * This method gets the list of classes
   *
   * @returns void
   */
  $scope.getClasses = () => {
    $scope.classes = [];
    $http.get($scope.apiUrl + '/getClasses', {
    }).then(
      response => {
        $scope.classes = response.data;
      },
      (err) => ($scope, err)
    );
  };

  /**
   * This method loads the list of matched students
   *
   * @returns void
   */
  $scope.searchStudents = () =>{
    $scope.students = [];
    $scope.showResults = $scope.showNotFound = false;
    let fname = document.getElementsByName("first_name")[0].value,
        lname = document.getElementsByName("last_name")[0].value;

    $http.post($scope.apiUrl + '/findStudents', {
      params: {
        fname: fname,
        lname: lname
      }
    }).then(
      response => {
        if(response.data.length > 0) {
          $scope.showResults = true;
          $scope.students = response.data;
        }
        else $scope.showNotFound = true;
      },
      (err) => ($scope, err)
    );

  };

  /**
   * This method calculates each student GPA
   *
   * @returns float
   */
  $scope.getGPA = (classes) => {

    let {total, count} = classes.reduce( (a, b) => {

      a.total += b.grade;

      a.count++;

      return a;

    }, {total: 0, count: 0});

    let avg = total / count;

    return avg.toFixed(2); //lots of ways to go about this and really depends on the specificity :)

  };

  //call init to load initial data
  $scope.init();

  });
