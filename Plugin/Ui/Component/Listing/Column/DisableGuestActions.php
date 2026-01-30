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
                // Check if customer is guest (identified by customer_type or missing typical customer data)
                // In our module, we set customer_type = 'guest'
                if (isset($item['customer_type']) && $item['customer_type'] === 'guest') {
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