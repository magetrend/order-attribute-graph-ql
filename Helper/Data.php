<?php

namespace Magetrend\OrderAttributeGraphQl\Helper;

class Data
{
    public $dataResolver;

    public function __construct(
        \Magetrend\OrderAttribute\Model\DataResolver $dataResolver
    ) {
        $this->dataResolver = $dataResolver;
    }

    public function prepareResultData($data)
    {
        if (empty($data)) {
            return ['attributes' => []];
        }

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'attributeCode' => $row->getAttributeCode(),
                'label' => $row->getLabel(),
                'value' => !empty($row->getValue())?$row->getValue():'',
                'valueText' => !empty($row->getValueText())?$row->getValueText():'',
            ];
        }

        return ['attributes' => $result];
    }

    public function prepareInputForSave($data)
    {
        if (empty($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $row) {
            $result[$row['attribute_code']] = $row['value'];
        }

        return $result;
    }

    public function getQuoteValues($quoteId)
    {
        $this->dataResolver->resetResolver();
        $quoteData = $this->dataResolver->getQuoteValues($quoteId);
        return $quoteData;
    }

    public function getOrderValues($orderId)
    {
        $this->dataResolver->resetResolver();
        $orderData = $this->dataResolver->getOrderValues($orderId);
        return $orderData;
    }
}