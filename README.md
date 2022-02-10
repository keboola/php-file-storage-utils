# File storage utils [![Build Status](https://dev.azure.com/keboola-dev/php-file-storage-utils/_apis/build/status/keboola.php-file-storage-utils?branchName=master)](https://dev.azure.com/keboola-dev/php-file-storage-utils/_build/latest?definitionId=12&branchName=master) [![Maintainability](https://api.codeclimate.com/v1/badges/fe983803eb7d71a87a34/maintainability)](https://codeclimate.com/github/keboola/php-file-storage-utils/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/fe983803eb7d71a87a34/test_coverage)](https://codeclimate.com/github/keboola/php-file-storage-utils/test_coverage)

PHP utils around Azure blob storage and Amazon S3

## Installation

    composer require keboola/php-file-storage-utils

## Usage

### Azure Blob storage

#### Retry middleware

```php
$blobClient = BlobRestProxy::createBlobService(...);
$blobClient->pushMiddleware(Keboola\FileStorage\Abs\RetryMiddlewareFactory::create(
        (optional) RetryMiddlewareFactory::DEFAULT_NUMBER_OF_RETRIES,
        (optional) RetryMiddlewareFactory::DEFAULT_RETRY_INTERVAL,
        (optional) RetryMiddlewareFactory::EXPONENTIAL_INTERVAL_ACCUMULATION
));
```

#### Best practice

use ClientFactory to preset client and middlewares

```php
\Keboola\FileStorage\Abs\ClientFactory::createClientFromConnectionString(
        string $connectionString,
        ?LoggerInterface $logger = null
);
```

## Development

Run tests with:

    docker-compose run --rm testsXX

where XX is PHP version (71 - 74), e.g.:

    docker-compose run --rm tests71

### Resources Setup

#### Azure blob storage

    export PHP_FS_UTILS_RG=testing-php-file-storage-utils

Create a resource group:

	az group create --name $PHP_FS_UTILS_RG --location "northeurope"

Deploy the storage account

	az group deployment create --resource-group $PHP_FS_UTILS_RG --template-file arm-template.json

command will output `ABS_ACCOUNT_NAME`, `ABS_ACCOUNT_KEY` which has to be added to `.env` file
