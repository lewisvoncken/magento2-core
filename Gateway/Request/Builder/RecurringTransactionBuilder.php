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

namespace MultiSafepay\ConnectCore\Gateway\Request\Builder;

use Exception;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use MultiSafepay\ConnectCore\Service\EmailSender;

class RecurringTransactionBuilder implements BuilderInterface
{
    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * RecurringTransactionBuilder constructor.
     *
     * @param EmailSender $emailSender
     */
    public function __construct(
        EmailSender $emailSender
    ) {
        $this->emailSender = $emailSender;
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws Exception
     */
    public function build(array $buildSubject): array
    {
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $order = $payment->getOrder();

        if (!$this->emailSender->checkOrderConfirmationBeforeTransaction(
            $payment->getMethod() !== '' ? $payment->getMethod() : $payment->getMethodInstance()->getCode()
        )) {
            $order->setCanSendNewEmailFlag(false);
        }

        return [
            'order' => $order,
        ];
    }
}
