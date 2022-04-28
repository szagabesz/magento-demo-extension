# magento-demo-extension

### Installation via Composer

Add the snippets below to your Magento 2.3.x or 2.4.x platform's composer.json

1. Point to the Git repository
```json
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:szagabesz/magento-demo-extension.git"
        }
    ],
```

2. Require the main branch
```json
    "require": {
        "szagabesz/module-demo": "dev-main"
    },
```
3. run `composer update szagabesz/module-demo`
4. run `bin/magento setup:upgrade`
