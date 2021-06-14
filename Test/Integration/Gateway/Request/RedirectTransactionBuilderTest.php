<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is provided with Magento in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Copyright © 2021 MultiSafepay, Inc. All rights reserved.
 * See DISCLAIMER.md for disclaimer details.
 *
 */

declare(strict_types=1);

namespace MultiSafepay\ConnectCore\Test\Integration\Gateway\Request;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use MultiSafepay\ConnectCore\Test\Integration\AbstractTestCase;
use MultiSafepay\ConnectCore\Gateway\Request\Builder\RedirectTransactionBuilder;

class RedirectTransactionBuilderTest extends AbstractTestCase
{
    /**
     * Test to see if this could be build
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @throws LocalizedException
     */
    public function testBuild(): void
    {
        /** @var RedirectTransactionBuilder $genericTransactionBuilder */
        $genericTransactionBuilder = $this->getObjectManager()->get(RedirectTransactionBuilder::class);

        $stateObject = new DataObject();
        $buildSubject = [
            'payment' => $this->getPaymentDataObject(),
            'stateObject' => $stateObject,
        ];
        $genericTransactionBuilder->build($buildSubject);

        $modifiedStateObject = $buildSubject['stateObject'];
        self::assertEquals('pending_payment', $modifiedStateObject->getStatus());
        self::assertEquals(Order::STATE_PENDING_PAYMENT, $modifiedStateObject->getState());
        self::assertEquals(false, $modifiedStateObject->getIsNotified());
    }
}
