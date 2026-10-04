<?php

namespace MerddinEmrys\ProductBadge\Model\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventorySalesApi\Api\AreProductsSalableInterface;

class ProductDetails implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;
    protected GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite;
    protected AreProductsSalableInterface $areProductsSalable;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param AreProductsSalableInterface $areProductsSalable
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        AreProductsSalableInterface $areProductsSalable
    ) {
        $this->productRepository = $productRepository;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->areProductsSalable = $areProductsSalable;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        if (!isset($args['sku']) || trim($args['sku']) === '') {
            throw new GraphQlInputException(__('Product SKU is required'));
        }

        $sku = trim((string)$args['sku']);

        $store = null;
        if ($context && method_exists($context, 'getExtensionAttributes') && $context->getExtensionAttributes()) {
            $store = $context->getExtensionAttributes()->getStore();
        }

        $currency = $store ? $store->getDefaultCurrencyCode() : 'GBP';

        try {
            $product = $this->productRepository->get($sku, storeId: $store->getId());
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(
                __('Product with SKU "%1" does not exist.', $sku),
                $e
            );
        }

        $price = $product->getFinalPrice();
        $badge = $product->getAttributeText('badge');

        return [
            'sku' => $sku,
            'name' => __($product->getName()),
            'price' => $price,
            'currency' => $currency,
            'stock_status' => $this->getStockStatusForProduct($product),
            'badge' => __($badge)
        ];
    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    protected function getStockStatusForProduct(ProductInterface $product): string
    {
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        $isProductSalable = $this->areProductsSalable->execute(
            [$product->getSku()],
            $stockId
        );
        return current($isProductSalable)->isSalable() ? 'In Stock' : 'Out of Stock';
    }
}
