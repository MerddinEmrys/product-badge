# MerddinEmrys Product Badge

The module adds a new product attribute for to assign a badge to products. This badge will be displayed after the product 
name. The module also adds a custom GraphQl query to retrieve the product data including the badge attribute.

## Installation

To install the module, follow these steps:

1. Upload the module to `app/code`
2. Run the following Magento commands from the root Magento directory:

    ```bash
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento setup:static-content:deploy
   bin/magento cache:flush
    ```
## Configuration and Assumptions

### Product Attribute

The new badge attribute is created and is added to all available product attribute sets. The attribute uses a standard 
dropdown type and the options use default Magento 2 attribute option configuration. The module adds some default options 
for the attribute:
- New
- Best Seller
- Sale

The attribute labels are set on the default Magento 2 store and are translatable. Translations or different labels can 
be added for different store views or translated using translation dictionaries in a theme or module. The default options 
added have translations available for French and German using tranlation dictionaries.

I used a standard Magento 2 dropdown for the attribute options to allow for users to add new badges as they wish, and 
to allow for using different values or translations per store view.

If it is set, the product badge is included as a suffix to the product name. The badge will only be visible on the product page, 
category page, search results page and where the product is included in product lists like related products etc. It will 
not show in the basket or mini cart, as I felt it was not necessary to show the badge in these areas.

#### GraphQl Query

The query can be accessed with a POST request to the following endpoint:

`https://<your-magento-url>/graphql`

With the query POST object format: 

```
query {
  productDetails(sku: "<sku>") {
    sku
    name
    price
    currency
    stock_status
    badge
  }
}
```

Example:

```
query {
  productDetails(sku: "24-MB01") {
    sku
    name
    price
    currency
    stock_status
    badge
  }
}
```

Response:

```
{
  "data": {
    "productDetails": {
      "sku": "24-MB01",
      "name": "Joust Duffle Bag",
      "price": 34,
      "currency": "GBP",
      "stock_status": "In Stock",
      "badge": "New"
    }
  }
}
```
