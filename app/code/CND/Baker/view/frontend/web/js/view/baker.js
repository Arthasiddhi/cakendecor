/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/

define([
    'uiComponent',
    'CND_Baker/js/baker-data'
], function (Component, bakerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.baker = bakerData.get('baker');
        }
    });
});
