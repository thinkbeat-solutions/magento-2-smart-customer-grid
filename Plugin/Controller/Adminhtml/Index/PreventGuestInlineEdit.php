<?php
/**
 * Thinkbeat_SmartCustomerGrid Prevent Guest Customer Inline Edit
 *
 * Prevents inline editing of guest customers (which don't exist in customer_entity).
 *
 * @category  Thinkbeat
 * @package   Thinkbeat_SmartCustomerGrid
 * @author    Thinkbeat
 * @copyright Copyright (c) 2026 Thinkbeat
 */

declare(strict_types=1);

namespace Thinkbeat\SmartCustomerGrid\Plugin\Controller\Adminhtml\Index;

use Magento\Customer\Controller\Adminhtml\Index\InlineEdit;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;

class PreventGuestInlineEdit
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param JsonFactory $resultJsonFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection
        )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Prevent inline edit for guest customers
     *
     * @param InlineEdit $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function aroundExecute(InlineEdit $subject, callable $proceed)
    {
        $request = $subject->getRequest();
        $postItems = $request->getParam('items', []);

        if (!($request->getParam('isAjax') && count($postItems))) {
            return $proceed();
        }

        // Check if any of the customers being edited are guests
        $customerIds = array_keys($postItems);
        $guestCustomerIds = $this->getGuestCustomerIds($customerIds);

        if (!empty($guestCustomerIds)) {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData([
                'messages' => [
                    __('Guest customers cannot be edited. Guest customer records are read-only.')
                ],
                'error' => true,
            ]);
        }

        // Not a guest customer, proceed with normal inline edit
        return $proceed();
    }

    /**
     * Get guest customer IDs from the provided list
     *
     * @param array $customerIds
     * @return array
     */
    private function getGuestCustomerIds(array $customerIds): array
    {
        if (empty($customerIds)) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection();
        $customerGridTable = $this->resourceConnection->getTableName('customer_grid_flat');

        // Query the grid to find which of these IDs are actually registered.
        // Guest profiles don't exist in the customer_grid_flat table.
        $select = $connection->select()
            ->from($customerGridTable, ['entity_id'])
            ->where('entity_id IN (?)', $customerIds);

        $registeredIds = $connection->fetchCol($select);

        // Any requested ID missing from the flat table is implicitly a guest ID.
        return array_diff($customerIds, $registeredIds);
    }
}