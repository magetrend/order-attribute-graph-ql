<?php

namespace Magetrend\OrderAttributeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

class GetOrderAttributes implements ResolverInterface
{
    /**
     * @var \Magetrend\OrderAttribute\Helper\Data
     */
    public $moduleHelper;

    public $orderRepository;

    public $collectionFactory;

    public $authorization;

    /**
     * OrderAttributeGraphql constructor.
     * @param \Magetrend\OrderAttribute\Model\DataResolver $dataResolver
     */
    public function __construct(

        \Magetrend\OrderAttributeGraphQl\Helper\Data $moduleHelper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        $this->orderRepository = $orderRepository;
        $this->moduleHelper = $moduleHelper;
        $this->authorization = $authorization;
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()
            && $context->getUserType() != \Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (!isset($args['order_id']) ||  empty($args['order_id'])) {
            throw new GraphQlInputException(__('Invalid parameter list.'));
        }

        $orderId = $args['order_id'];
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlInputException(__('Invalid order id'));
        }

        if ($context->getUserType() == \Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER
            && $order->getCustomerId() != $context->getUserId()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized for this order.'));
        }

        if ($context->getUserType() == \Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN
            && !$this->authorization->isAllowed('Magetrend_OrderAttribute::attribute')
        ) {
            throw new GraphQlAuthorizationException(__('Not enough permission'));
        }

        $orderData = $this->moduleHelper->getOrderValues($order->getId());
        $output = $this->moduleHelper->prepareResultData($orderData);

        return $output ;
    }
}