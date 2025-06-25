# ManiyaTech SEO module for Magento 2

The ManiyaTech Seo module for Magento 2 allows store administrators to configure and dynamically apply meta title, meta keywords, and meta description templates to both product and category pages. These templates can include attribute placeholders (e.g., [name], [price], [description]) that get replaced with real values during page rendering. This improves SEO consistency, reduces manual effort, and ensures optimized metadata across the catalog.

## How to install ManiyaTech_Seo module

### Composer Installation

Run the following command in Magento 2 root directory to install ManiyaTech_Seo module via composer.

#### Install

```
composer require maniyatech/magento2-seo
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
```

#### Update

```
composer update maniyatech/magento2-seo
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
```

Run below command if your store is in the production mode:

```
php bin/magento setup:di:compile
```

### Manual Installation

If you prefer to install this module manually, kindly follow the steps described below - 

- Download the latest version [here](https://github.com/maniyatech/magento2-seo/archive/refs/heads/main.zip) 
- Create a folder path like this `app/code/ManiyaTech/Seo` and extract the `main.zip` file into it.
- Navigate to Magento root directory and execute the below commands.

```
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
```
