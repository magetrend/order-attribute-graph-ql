
query{
  getOrderAttributes (
    order_id: 22
  ) {
    attributes {
      attributeCode
      label
      value
      valueText
    }
  }
}

query{
  getCartAttributes (
    cart_id: "Pd9KjU3wQV4v5EMd2IItREuWohcw6SzR"
  ) {
    attributes {
      attributeCode
      label
      value
      valueText
    }
  }
}


mutation {
  setCartAttributes(
    input: {
      cart_id: "Pd9KjU3wQV4v5EMd2IItREuWohcw6SzR",
      attributes: [
        {
          attribute_code: "attribute_1"
          value: "value 1"
        },
        {
          attribute_code: "attribute_2"
          value: "214"
        }
      ]
    }
  ) {
    attributes {
      attributeCode
      label
      value
      valueText
    }
  }
}

mutation {
  setOrderAttributes(
    input: {
      order_id: 23,
      attributes: [
        {
          attribute_code: "attribute_1"
          value: "value 1"
        },
        {
          attribute_code: "attribute_2"
          value: "214"
        }
      ]
    }
  ) {
    attributes {
      attributeCode
      label
      value
      valueText
    }
  }
}