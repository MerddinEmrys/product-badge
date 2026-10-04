<?php

namespace MerddinEmrys\ProductBadge\Plugin;

use Magento\Catalog\Model\Product;

class ProductPlugin
{
    public function afterGetName(Product $subject, string $result): string
    {
        $badge = $subject->getAttributeText('badge');
        if ($badge) {
            return sprintf("%s [%s]", $result, __($badge));
        }
        return $result;
    }
}
