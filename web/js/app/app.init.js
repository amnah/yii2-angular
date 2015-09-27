(function () {
    'use strict';

    angular
        .module('app')
        .run(appInit);

    // @ngInject
    function appInit(User) {
        // attempt to set up user from local storage. this is faster than waiting for the automatic refresh
        User.loadFromLocalStorage();
        User.startJwtRefreshInterval(true);
    }

})();