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

class GetCartAttributes implements ResolverInterface
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
     * @var \Magetrend\OrderAttribute\Helper\Data
     */
    public $moduleHelper;

    /**
     * OrderAttributeGraphql constructor.
     * @param \Magetrend\OrderAttribute\Model\DataResolver $dataResolver
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        SetShippingMethodsOnCartInterface $setShippingMethodsOnCart,
        CheckCartCheckoutAllowance $checkCartCheckoutAllowance,
        \Magetrend\OrderAttributeGraphQl\Helper\Data $moduleHelper
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->setShippingMethodsOnCart = $setShippingMethodsOnCart;
        $this->checkCartCheckoutAllowance = $checkCartCheckoutAllowance;
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
        if (!isset($args['cart_id']) ||  empty($args['cart_id'])) {
            throw new GraphQlInputException(__('Invalid parameter list.'));
        }

        $maskedCartId = $args['cart_id'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $this->checkCartCheckoutAllowance->execute($cart);

        $quoteData = $this->moduleHelper->getQuoteValues($cart->getId());
        $output = $this->moduleHelper->prepareResultData($quoteData);

        return $output ;
    }
}