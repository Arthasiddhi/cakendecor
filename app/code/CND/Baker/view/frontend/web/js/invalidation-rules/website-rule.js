/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiClass'
], function (Element) {
    'use strict';

    return Element.extend({

        defaults: {
            scopeConfig: {}
        },

        /**
         * Takes website id from current baker data and compare it with current website id
         * If baker belongs to another scope, we need to invalidate current section
         *
         * @param {Object} bakerData
         */
        process: function (bakerData) {
            var baker = bakerData.get('baker');

            if (this.scopeConfig && baker() &&
                ~~baker().websiteId !== ~~this.scopeConfig.websiteId && ~~baker().websiteId !== 0) {
                bakerData.reload(['baker']);
            }
        }
    });
});
