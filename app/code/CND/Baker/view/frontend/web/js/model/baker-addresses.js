/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'ko',
    './baker/address'
], function ($, ko, Address) {
    'use strict';

    var isLoggedIn = ko.observable(window.isBakerLoggedIn);

    return {
        /**
         * @return {Array}
         */
        getAddressItems: function () {
            var items = [],
                bakerData = window.bakerData;

            if (isLoggedIn()) {
                if (Object.keys(bakerData).length) {
                    $.each(bakerData.addresses, function (key, item) {
                        items.push(new Address(item));
                    });
                }
            }

            return items;
        }
    };
});
