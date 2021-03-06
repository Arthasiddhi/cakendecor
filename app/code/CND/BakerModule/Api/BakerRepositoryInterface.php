<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Api;

/**
 * Customer CRUD interface.
 * @api
 * @since 100.0.2
 */
interface BakerRepositoryInterface
{
    /**
     * Create or update a customer.
     *
     * @param \CND\Baker\Api\Data\BakerInterface $baker
     * @param string $passwordHash
     * @return \CND\Baker\Api\Data\BakerInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\CND\Baker\Api\Data\BakerInterface $baker, $passwordHash = null);

    /**
     * Retrieve customer.
     *
     * @param string $email
     * @param int|null $websiteId
     * @return \CND\Baker\Api\Data\BakerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified email does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($email, $websiteId = null);

    /**
     * Get customer by Customer ID.
     *
     * @param int $bakerId
     * @return \CND\Baker\Api\Data\BakerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($bakerId);

    /**
     * Retrieve customers which match a specified criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See http://devdocs.magento.com/codelinks/attributes.html#CustomerRepositoryInterface to determine
     * which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \CND\Baker\Api\Data\BakerSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete customer.
     *
     * @param \CND\Baker\Api\Data\BakerInterface $baker
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\CND\Baker\Api\Data\BakerInterface $baker);

    /**
     * Delete customer by Customer ID.
     *
     * @param int $bakerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($bakerId);
}
