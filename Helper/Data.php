<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_Seo
 */

namespace ManiyaTech\Seo\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Data extends AbstractHelper
{
    private const XML_PATH_MODULE_ENABLED       = 'seo_config/general/enabled';
    private const XML_PATH_PRODUCT_MODULE       = 'seo_config/product/enabled';
    private const CATEGORY_TAGS                 = ['CL1', 'CL2', 'CL3', 'CL4', 'CL5', 'CL6'];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * AbstractData constructor.
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }

    /**
     * Check if the SEO module is enabled in system configuration.
     *
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_MODULE_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if product meta updates are enabled.
     *
     * @return bool
     */
    public function isProductMetaEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_MODULE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if category meta updates are enabled for a specific level.
     *
     * @param int|string $level Category depth level.
     * @return bool
     */
    public function isCategoryMetaEnabled($level): bool
    {
        return (bool) $this->scopeConfig->getValue(
            'seo_config/category' . $level . '/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the configured meta value for a category and resolve dynamic shortcodes.
     *
     * @param string $level Category level (e.g. "1", "2", etc.)
     * @param string $field Meta field key (e.g. "meta_title", "meta_description")
     * @param AbstractModel $category Category model instance
     * @return string|null
     */
    public function getCategoryMeta(string $level, string $field, AbstractModel $category): ?string
    {
        $value = $this->scopeConfig->getValue("seo_config/category{$level}/{$field}", ScopeInterface::SCOPE_STORE);
        return !empty($value) ? $this->shortcode($value, $category) : null;
    }

    /**
     * Retrieve the configured meta value for a product and resolve dynamic shortcodes.
     *
     * @param string $field Meta field key (e.g. "meta_title", "meta_description")
     * @param AbstractModel $product Product model instance
     * @return string|null
     */
    public function getProductMeta(string $field, AbstractModel $product): ?string
    {
        $value = $this->scopeConfig->getValue("seo_config/product/{$field}", ScopeInterface::SCOPE_STORE);
        return !empty($value) ? $this->shortcode($value, $product) : null;
    }

    /**
     * Parse template string and replace shortcodes like [name], [sku], [CL1], etc.
     *
     * @param string $template The template string containing placeholders
     * @param AbstractModel $entity Product or Category instance
     * @return string
     */
    public function shortcode(string $template, AbstractModel $entity): string
    {
        preg_match_all('/\[(.*?)\]/', $template, $matches);

        if (empty($matches[1])) {
            return $template;
        }

        $isProduct = method_exists($entity, 'getTypeId') && !empty($entity->getTypeId());

        foreach ($matches[1] as $i => $tag) {
            $replacement = '';

            if ($isProduct) {
                try {
                    $product = $this->productRepository->getById((int) $entity->getId());
                    $replacement = (string) $product->getData($tag);
                } catch (NoSuchEntityException $e) {
                    $replacement = '';
                }
            } else {
                $replacement = $this->resolveCategoryTag($tag, $entity);
            }

            $template = str_replace($matches[0][$i], $replacement, $template);
        }

        return $template;
    }

    /**
     * Resolve category-specific shortcodes like [CL1], [CL2], etc.
     *
     * @param string $tag Tag placeholder to resolve
     * @param AbstractModel $category Category entity
     * @return string
     */
    private function resolveCategoryTag(string $tag, AbstractModel $category): string
    {
        $path = $category->getPath() ?? '';
        $pathArray = explode('/', $path);

        if (in_array($tag, self::CATEGORY_TAGS, true)) {
            $index = (int) substr($tag, -1) + 1;

            if (isset($pathArray[$index])) {
                try {
                    $parentCategory = $this->categoryRepository->get((int) $pathArray[$index]);
                    return $parentCategory->getName() ?? '';
                } catch (NoSuchEntityException $e) {
                    return '';
                }
            }
        }

        return (string) $category->getData($tag);
    }
}
