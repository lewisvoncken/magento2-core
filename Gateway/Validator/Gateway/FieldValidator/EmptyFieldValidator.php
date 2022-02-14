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
 * Copyright © 2022 MultiSafepay, Inc. All rights reserved.
 * See DISCLAIMER.md for disclaimer details.
 *
 */

declare(strict_types=1);

namespace MultiSafepay\ConnectCore\Gateway\Validator\Gateway\FieldValidator;

use Magento\Framework\Phrase;

class EmptyFieldValidator implements GatewayFieldValidatorInterface
{
    public const EMPTY_VALIDATOR_FIELDS_KEY_NAME = 'empty_validation_fields';

    /**
     * @var string
     */
    private $currentFieldName = '';

    /**
     * @param array $gatewayAdditionalFieldData
     * @return bool
     */
    public function validate(array $gatewayAdditionalFieldData): bool
    {
        if (isset($gatewayAdditionalFieldData[self::EMPTY_VALIDATOR_FIELDS_KEY_NAME])) {
            foreach ($gatewayAdditionalFieldData[self::EMPTY_VALIDATOR_FIELDS_KEY_NAME] as $fieldName) {
                $this->currentFieldName = $fieldName;

                if (empty($gatewayAdditionalFieldData[$fieldName])) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @return Phrase
     */
    public function getValidationMessage(): Phrase
    {
        return __('The %1 can not be empty', $this->getMessageFieldName());
    }

    /**
     * @return string
     */
    private function getMessageFieldName(): string
    {
        return $this->currentFieldName ? str_replace('_', ' ', $this->currentFieldName)
            : '';
    }
}
