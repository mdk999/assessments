'use strict';

describe('Controller: MainCtrl', function () {

  // load the controller's module
  beforeEach(module('otusApp'));

  let MainCtrl, scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    MainCtrl = $controller('MainCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should return a float', function () {
    expect(MainCtrl.getGPA([{0:{"grade":2.0},1:{"grade":1.2},2:{"grade":3.5}}]).toBe(4.3));
  });
});
