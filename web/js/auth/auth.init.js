(function () {
    'use strict';

    angular
        .module('app')
        .run(initConfig);

    // @ngInject
    function initConfig(Auth) {
        // attempt to set up user from local storage. this is faster than waiting for the automatic refresh
        var user = Auth.setUserFromLocalStorage();
        var runAtStart = user ? true : false;
        Auth.startTokenRenewInterval(runAtStart);
    }

})();