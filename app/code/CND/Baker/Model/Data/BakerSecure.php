<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model\Data;

/**
 * Class containing secure customer data that cannot be exposed as part of \CND\Baker\Api\Data\BakerInterface
 *
 * @method string getRpToken()
 * @method string getRpTokenCreatedAt()
 * @method string getPasswordHash()
 * @method string getDeleteable()
 * @method setRpToken(string $rpToken)
 * @method setRpTokenCreatedAt(string $rpTokenCreatedAt)
 * @method setPasswordHash(string $hashedPassword)
 * @method setDeleteable(bool $deleteable)
 */
class BakerSecure extends \Magento\Framework\DataObject
{
}
