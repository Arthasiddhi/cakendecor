/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiElement',
    'CND_Baker/js/baker-data'
], function (_, Element, bakerData) {
    'use strict';

    return Element.extend({
        /**
         * Initialize object
         */
        initialize: function () {
            this._super();
            this.process(bakerData);
        },

        /**
         * Process all rules in loop, each rule can invalidate some sections in baker data
         *
         * @param {Object} bakerDataObject
         */
        process: function (bakerDataObject) {
            _.each(this.invalidationRules, function (rule, ruleName) {
                _.each(rule, function (ruleArgs, rulePath) {
                    require([rulePath], function (Rule) {
                        var currentRule = new Rule(ruleArgs);

                        if (!_.isFunction(currentRule.process)) {
                            throw new Error('Rule ' + ruleName + ' should implement invalidationProcessor interface');
                        }
                        currentRule.process(bakerDataObject);
                    });
                });
            });
        }
    });
});
