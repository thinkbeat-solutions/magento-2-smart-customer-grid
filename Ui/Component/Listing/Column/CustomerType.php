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

                // Preserve raw type before overwriting with HTML (for other plugins)
                $item['customer_type_raw'] = $type;

                // Use integer 1/0 for reliable JSON serialization across all PHP versions
                $item['is_guest_customer'] = ($type === 'guest') ? 1 : 0;

                if ($type === 'registered') {
                    $item[$this->getData('name')] = '<span style="color: #006400; font-weight: 500;">Registered</span>';
                }
                else {
                    $item[$this->getData('name')] = '<span style="color: #666;">Guest</span>';
                }
            }
            else {
                $item['customer_type_raw'] = 'unknown';
                $item['is_guest_customer'] = 0;
                $item[$this->getData('name')] = '<span style="color: #999;">Unknown</span>';
            }
        }

        return $dataSource;
    }
}