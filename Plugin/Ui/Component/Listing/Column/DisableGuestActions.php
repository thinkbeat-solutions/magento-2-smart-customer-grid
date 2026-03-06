<?php
/**
 * Thinkbeat_SmartCustomerGrid Disable Guest Actions
 *
 * Removes the 'Edit' action for Guest customers in the grid.
 *
 * @category  Thinkbeat
 * @package   Thinkbeat_SmartCustomerGrid
 * @author    Thinkbeat
 * @copyright Copyright (c) 2026 Thinkbeat
 */

declare(strict_types=1);

namespace Thinkbeat\SmartCustomerGrid\Plugin\Ui\Component\Listing\Column;

use Magento\Customer\Ui\Component\Listing\Column\Actions;

class DisableGuestActions
{
    /**
     * Remove Edit action for Guest customers
     *
     * @param Actions $subject
     * @param array $dataSource
     * @return array
     */
    public function afterPrepareDataSource(Actions $subject, array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $actionColumnName = $subject->getName();

            foreach ($dataSource['data']['items'] as &$item) {
                // Check using is_guest_customer flag (integer 1/0) set by CustomerType column,
                // or fall back to customer_type_raw if available, or raw customer_type string
                $isGuest = false;
                if (isset($item['is_guest_customer']) && $item['is_guest_customer']) {
                    $isGuest = true;
                }
                elseif (isset($item['customer_type_raw']) && $item['customer_type_raw'] === 'guest') {
                    $isGuest = true;
                }
                elseif (isset($item['customer_type']) && $item['customer_type'] === 'guest') {
                    $isGuest = true;
                }

                if ($isGuest) {
                    // Remove the 'edit' action if it exists
                    if (isset($item[$actionColumnName]['edit'])) {
                        unset($item[$actionColumnName]['edit']);
                    }
                }
            }
        }

        return $dataSource;
    }
}