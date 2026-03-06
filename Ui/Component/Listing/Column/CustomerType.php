<?php
/**
 * Thinkbeat_SmartCustomerGrid Customer Type Column
 *
 * Renders customer type (Registered/Guest) with visual indicator
 *
 * @category  Thinkbeat
 * @package   Thinkbeat_SmartCustomerGrid
 * @author    Thinkbeat
 * @copyright Copyright (c) 2026 Thinkbeat
 */

declare(strict_types=1);

namespace Thinkbeat\SmartCustomerGrid\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CustomerType extends Column
{
    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (isset($item['customer_type'])) {
                $type = $item['customer_type'];

                // Add a raw property for Javascript and other plugins to identify guests
                $item['is_guest_customer'] = ($type === 'guest');

                if ($type === 'registered') {
                    $item[$this->getData('name')] = '<span style="color: #006400; font-weight: 500;">Registered</span>';
                }
                else {
                    $item[$this->getData('name')] = '<span style="color: #666;">Guest</span>';
                }
            }
            else {
                $item['is_guest_customer'] = false;
                $item[$this->getData('name')] = '<span style="color: #999;">Unknown</span>';
            }
        }

        return $dataSource;
    }
}