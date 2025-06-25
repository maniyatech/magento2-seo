<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_Seo
 */

declare(strict_types=1);

namespace ManiyaTech\Seo\Plugin;

use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\DataObject;
use ManiyaTech\Seo\Helper\Data as SeoHelper;

class ProductMetaPlugin
{
    /**
     * @var SeoHelper
     */
    protected $seoHelper;

    /**
     * ProductMetaPlugin Constructor
     *
     * @param SeoHelper $seoHelper
     */
    public function __construct(SeoHelper $seoHelper)
    {
        $this->seoHelper = $seoHelper;
    }

    /**
     * Plugin afterInitProduct method for Product Helper.
     *
     * @param ProductHelper $subject
     * @param Product|null $result
     * @param int $productId
     * @param ActionInterface $controller
     * @param DataObject|null $params
     * @return Product|null
     */
    public function afterInitProduct(
        ProductHelper $subject,
        ?Product $result,
        int $productId,
        ActionInterface $controller,
        ?DataObject $params = null
    ): ?Product {
        if (!$result || !$this->seoHelper->isModuleEnabled() || !$this->seoHelper->isProductMetaEnabled()) {
            return $result;
        }

        $metaFields = [
            'meta_title'       => 'setMetaTitle',
            'meta_keyword'     => 'setMetaKeyword',
            'meta_description' => 'setMetaDescription',
        ];

        foreach ($metaFields as $field => $setter) {
            $value = $this->seoHelper->getProductMeta($field, $result);
            if ($value) {
                $result->$setter($value);
            }
        }

        return $result;
    }
}
