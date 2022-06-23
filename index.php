<?php
  require_once('src/config.php');
  require_once('vendor/autoload.php');
  use \App\
  {
    AddToShopify,
    MagentoProducts
  };

  $productObj = new MagentoProducts(
      MAGENTO_BASE_URL, 
      MAGENTO_PRODUCTS_URL, 
      MAGENTO_TOKEN_URL, 
      MAGENTO_ADMIN_USERNAME, 
      MAGENTO_ADMIN_PASSWORD
    );

  $addProducts = new AddToShopify(
    SHOPIFY_BASE_URL,
    SHOPIFY_API_ENDPOINT_V,
    SHOPIFY_API_PRODUCTS,
    SHOPIFY_API_KEY,
    SHOPIFY_API_SECRET,
    HTTPS
  );

  $products = $productObj->processResults();
  $results = $addProducts->add_product($products);
