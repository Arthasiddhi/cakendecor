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
    'underscore',
    './address-list'
], function ($, ko, _, addressList) {
    'use strict';

    var isLoggedIn = ko.observable(window.isBakerLoggedIn),
        bakerData = {};

    if (isLoggedIn()) {
        bakerData = window.bakerData;
    } else {
        bakerData = {};
    }

    return {
        bakerData: bakerData,
        bakerDetails: {},
        isLoggedIn: isLoggedIn,

        /**
         * @param {Boolean} flag
         */
        setIsLoggedIn: function (flag) {
            isLoggedIn(flag);
        },

        /**
         * @return {Array}
         */
        getBillingAddressList: function () {
            return addressList();
        },

        /**
         * @return {Array}
         */
        getShippingAddressList: function () {
            return addressList();
        },

        /**
         * @param {String} fieldName
         * @param {*} value
         */
        setDetails: function (fieldName, value) {
            if (fieldName) {
                this.bakerDetails[fieldName] = value;
            }
        },

        /**
         * @param {String} fieldName
         * @return {*}
         */
        getDetails: function (fieldName) {
            if (fieldName) {
                if (this.bakerDetails.hasOwnProperty(fieldName)) {
                    return this.bakerDetails[fieldName];
                }

                return undefined;
            }

            return this.bakerDetails;
        },

        /**
         * @param {Array} address
         * @return {Number}
         */
        addBakerAddress: function (address) {
            var fields = [
                    'baker_id', 'country_id', 'street', 'company', 'telephone', 'fax', 'postcode', 'city',
                    'firstname', 'lastname', 'middlename', 'prefix', 'suffix', 'vat_id', 'default_billing',
                    'default_shipping'
                ],
                bakerAddress = {},
                hasAddress = 0,
                existingAddress;

            if (!this.bakerData.addresses) {
                this.bakerData.addresses = [];
            }

            bakerAddress = _.pick(address, fields);

            if (address.hasOwnProperty('region_id')) {
                bakerAddress.region = {
                    'region_id': address['region_id'],
                    region: address.region
                };
            }

            for (existingAddress in this.bakerData.addresses) {
                if (this.bakerData.addresses.hasOwnProperty(existingAddress)) {
                    if (_.isEqual(this.bakerData.addresses[existingAddress], bakerAddress)) { //eslint-disable-line
                        hasAddress = existingAddress;
                        break;
                    }
                }
            }

            if (hasAddress === 0) {
                return this.bakerData.addresses.push(bakerAddress) - 1;
            }

            return hasAddress;
        },

        /**
         * @param {*} addressId
         * @return {Boolean}
         */
        setAddressAsDefaultBilling: function (addressId) {
            if (this.bakerData.addresses[addressId]) {
                this.bakerData.addresses[addressId]['default_billing'] = 1;

                return true;
            }

            return false;
        },

        /**
         * @param {*} addressId
         * @return {Boolean}
         */
        setAddressAsDefaultShipping: function (addressId) {
            if (this.bakerData.addresses[addressId]) {
                this.bakerData.addresses[addressId]['default_shipping'] = 1;

                return true;
            }

            return false;
        }
    };
});
