(function () {
    'use strict';

    angular
        .module('app')
        .run(appInit);

    // @ngInject
    function appInit(Auth) {
        // attempt to set up user from local storage. this is faster than waiting for the automatic refresh
        Auth.setUserFromLocalStorage();
        Auth.startTokenRenewInterval(true);
    }

})();