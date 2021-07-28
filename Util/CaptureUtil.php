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

namespace MultiSafepay\ConnectCore\Util;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use MultiSafepay\Api\Transactions\CaptureRequest;
use MultiSafepay\Api\Transactions\Transaction as TransactionStatus;
use MultiSafepay\ConnectAdminhtml\Model\Config\Source\PaymentAction;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\MaestroConfigProvider;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\MastercardConfigProvider;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\VisaConfigProvider;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\CreditCardConfigProvider;
use MultiSafepay\ConnectCore\Observer\Gateway\CreditCardDataAssignObserver;

class CaptureUtil
{
    public const AVAILABLE_MANUAL_CAPTURE_METHODS = [
        VisaConfigProvider::CODE,
        CreditCardConfigProvider::CODE,
        MastercardConfigProvider::CODE,
        MaestroConfigProvider::CODE,
    ];

    public const AVAILABLE_MANUAL_CAPTURE_CARD_BRANDS = ['VISA', 'MASTERCARD', 'MAESTRO'];

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * CaptureUtil constructor.
     *
     * @param DateTime $dateTime
     */
    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @param array $transaction
     * @param float $amount
     * @return bool
     */
    public function isManualCapturePossibleForAmount(array $transaction, float $amount): bool
    {
        $paymentDetails = $transaction['payment_details'] ?? [];

        return isset($paymentDetails['capture_remain'])
               && (float)$paymentDetails['capture_remain'] >= round($amount * 100, 10);
    }

    /**
     * @param array $transaction
     * @return bool
     */
    public function isCaptureManualTransaction(array $transaction): bool
    {
        $paymentDetails = $transaction['payment_details'] ?? [];

        return $transaction['financial_status'] === TransactionStatus::INITIALIZED && isset($paymentDetails['capture'])
               && $paymentDetails['capture'] === CaptureRequest::CAPTURE_MANUAL_TYPE;
    }

    /**
     * @param array $transaction
     * @return bool
     */
    public function isCaptureManualReservationExpired(array $transaction): bool
    {
        $paymentDetails = $transaction['payment_details'] ?? [];

        if (!($reservationExpirationData = $paymentDetails['capture_expiry'] ?? '')) {
            return true;
        }

        return $this->dateTime->timestamp($reservationExpirationData) < $this->dateTime->timestamp();
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    public function isCaptureManualPayment(OrderPaymentInterface $payment): bool
    {
        if (!in_array($payment->getMethod(), self::AVAILABLE_MANUAL_CAPTURE_METHODS)
            || !($payment->getMethodInstance()->getConfigPaymentAction()
                 === PaymentAction::PAYMENT_ACTION_AUTHORIZE_ONLY)
        ) {
            return false;
        }

        if ($payment->getMethod() === CreditCardConfigProvider::CODE) {
            $cardBrand = $payment->getAdditionalInformation(CreditCardDataAssignObserver::CREDIT_CARD_BRAND_PARAM_NAME);

            return $cardBrand && in_array($cardBrand, self::AVAILABLE_MANUAL_CAPTURE_CARD_BRANDS);
        }

        return true;
    }
}
