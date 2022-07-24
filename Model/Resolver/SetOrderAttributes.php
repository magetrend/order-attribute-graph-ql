<?php

namespace Magetrend\OrderAttributeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magetrend\OrderAttribute\Api\Data\EntityInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;


class SetOrderAttributes implements ResolverInterface
{
    /**
     * @var \Magetrend\OrderAttribute\Api\EntityManagementInterface
     */
    private $entityManagement;

    /**
     * @var \Magetrend\OrderAttributeGraphQl\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    public $authorization;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * @param GetCartForUser $getCartForUser
     * @param SetShippingMethodsOnCartInterface $setShippingMethodsOnCart
     * @param CheckCartCheckoutAllowance $checkCartCheckoutAllowance
     */
    public function __construct(
        \Magetrend\OrderAttribute\Api\EntityManagementInterface $entityManagement,
        \Magetrend\OrderAttributeGraphQl\Helper\Data $moduleHelper,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->authorization = $authorization;
        $this->entityManagement = $entityManagement;
        $this->moduleHelper = $moduleHelper;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if ($context->getUserType() != \Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN) {
            throw new GraphQlAuthorizationException(__('The current user isn\'t authorized for this action'));
        }

        if (!$this->authorization->isAllowed('Magetrend_OrderAttribute::value_edit')) {
            throw new GraphQlAuthorizationException(__('Not enoguth permission for operation'));
        }

        $input = $args['input'];
        if (!isset($input['order_id']) || !isset($input['attributes']) || empty($input['order_id']) || empty($input['attributes'])) {
            throw new GraphQlInputException(__('Invalid parameter list.'));
        }

        $orderId = $input['order_id'];
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlInputException(__('Invalid order id'));
        }

        $data = $this->moduleHelper->prepareInputForSave($input['attributes']);
        $this->entityManagement->save($order->getId(), EntityInterface::PARENT_TYPE_ORDER, $data);

        $orderData = $this->moduleHelper->getOrderValues($orderId);
        $output = $this->moduleHelper->prepareResultData($orderData);

        return $output ;
    }
}