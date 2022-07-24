<?php

namespace Magetrend\OrderAttributeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\SetShippingMethodsOnCartInterface;
use Magento\QuoteGraphQl\Model\Cart\CheckCartCheckoutAllowance;
use Magetrend\OrderAttribute\Api\Data\EntityInterface;


class SetCartAttributes implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var SetShippingMethodsOnCartInterface
     */
    private $setShippingMethodsOnCart;

    /**
     * @var CheckCartCheckoutAllowance
     */
    private $checkCartCheckoutAllowance;

    /**
     * @var \Magetrend\OrderAttribute\Api\EntityManagementInterface
     */
    private $entityManagement;

    /**
     * @var \Magetrend\OrderAttributeGraphQl\Helper\Data
     */
    private $moduleHelper;

    /**
     * @param GetCartForUser $getCartForUser
     * @param SetShippingMethodsOnCartInterface $setShippingMethodsOnCart
     * @param CheckCartCheckoutAllowance $checkCartCheckoutAllowance
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        SetShippingMethodsOnCartInterface $setShippingMethodsOnCart,
        CheckCartCheckoutAllowance $checkCartCheckoutAllowance,
        \Magetrend\OrderAttribute\Api\EntityManagementInterface $entityManagement,
        \Magetrend\OrderAttributeGraphQl\Helper\Data $moduleHelper

    ) {
        $this->getCartForUser = $getCartForUser;
        $this->setShippingMethodsOnCart = $setShippingMethodsOnCart;
        $this->checkCartCheckoutAllowance = $checkCartCheckoutAllowance;
        $this->entityManagement = $entityManagement;
        $this->moduleHelper = $moduleHelper;
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
        $input = $args['input'];
        if (!isset($input['cart_id']) || !isset($input['attributes']) || empty($input['cart_id']) || empty($input['attributes'])) {
            throw new GraphQlInputException(__('Invalid parameter list.'));
        }

        $maskedCartId = $input['cart_id'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $this->checkCartCheckoutAllowance->execute($cart);

        $data = $this->moduleHelper->prepareInputForSave($input['attributes']);
        $this->entityManagement->save($cart->getId(), EntityInterface::PARENT_TYPE_QUOTE, $data);

        $quoteData = $this->moduleHelper->getQuoteValues($cart->getId());
        $output = $this->moduleHelper->prepareResultData($quoteData);

        return $output ;
    }

}