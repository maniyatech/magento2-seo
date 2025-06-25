<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_Seo
 */

declare(strict_types=1);

namespace ManiyaTech\Seo\Plugin;

use Magento\Catalog\Controller\Category\View as CategoryView;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Registry;
use ManiyaTech\Seo\Helper\Data as SeoHelper;
use Magento\Catalog\Model\Category;

class CategoryMetaPlugin
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var SeoHelper
     */
    protected $seoHelper;

    /**
     * CategoryMetaPlugin Constructor
     *
     * @param Registry $registry
     * @param SeoHelper $seoHelper
     */
    public function __construct(Registry $registry, SeoHelper $seoHelper)
    {
        $this->registry = $registry;
        $this->seoHelper = $seoHelper;
    }

    /**
     * Plugin afterExecute method for Category View controller.
     *
     * @param CategoryView $subject
     * @param ResultInterface $result
     * @return ResultInterface
     */
    public function afterExecute(CategoryView $subject, ResultInterface $result): ResultInterface
    {
        if (!$result instanceof Page) {
            return $result;
        }

        $category = $this->registry->registry('current_category');
        if (!$category instanceof Category || !$category->getId()) {
            return $result;
        }

        if (!$this->seoHelper->isModuleEnabled()) {
            return $result;
        }

        $level = (int) $category->getLevel();

        if (!$this->seoHelper->isCategoryMetaEnabled($level)) {
            return $result;
        }

        $title       = $this->seoHelper->getCategoryMeta((string) $level, 'meta_title', $category);
        $keywords    = $this->seoHelper->getCategoryMeta((string) $level, 'meta_keyword', $category);
        $description = $this->seoHelper->getCategoryMeta((string) $level, 'meta_description', $category);

        $pageConfig = $result->getConfig();
        if ($title) {
            $pageConfig->getTitle()->set($title);
        }
        if ($keywords) {
            $pageConfig->setKeywords($keywords);
        }
        if ($description) {
            $pageConfig->setDescription($description);
        }

        return $result;
    }
}
