# Pimcore API documentation bundle

[![Latest Version on Packagist](https://img.shields.io/packagist/v/valantic/pimcore-api-documentation.svg?style=flat-square)](https://packagist.org/packages/valantic/pimcore-api-documentation)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![PHP Checks](https://github.com/valantic/pimcore-api-documentation/actions/workflows/phpstan.yml/badge.svg)](https://github.com/valantic/pimcore-api-documentation/actions/workflows/phpstan.yml)

This package is developed by [valantic CEC Schweiz](https://www.valantic.com/en/services/digital-business/) and is under active development.

Bundle is used for generating API documentation based on API controllers.

## Requirements

- Pimcore >= 11.0.0

## Installation

1. `composer require valantic/pimcore-api-documentation`
2. Add `ValanticPimcoreApiDocumentationBundle` to `config/bundles.php`


## Usage

```php
class ProductController implements \Valantic\PimcoreApiDocumentationBundle\Http\Controller\ApiControllerInterface
{
    use \Valantic\PimcoreApiDocumentationBundle\Controller\ApiControllerTrait;

    #[Route(path: '/product', name: 'rest_api_product_create', methods: Request::METHOD_POST)]
    public function create(ProductCreateRequest $request): ProductCreateResponse|\Valantic\PimcoreApiDocumentationBundle\Http\Response\BadRequestResponse
    {
        $errors = $this->validateRequest($request);

        if (count($errors) !== 0) {
            return new \Valantic\PimcoreApiDocumentationBundle\Http\Response\BadRequestResponse($errors);
        }

        return new ProductCreateResponse(/* ... */);
    }
}

use Symfony\Component\Validator\Constraints as Assert;

class ProductCreateRequest implements \Valantic\PimcoreApiDocumentationBundle\Http\Request\Contracts\HasJsonPayload
{
    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\NotBlank]
    public ?string $description = null;
}

class ProductCreateResponse implements \Valantic\PimcoreApiDocumentationBundle\Http\Response\ApiResponseInterface
{
    public static function status(): int
    {
        return \Symfony\Component\HttpFoundation\Response::HTTP_CREATED;
    }

    public static function getDtoClass(): string|false
    {
        return ProductCreateDto::class;
    }
}

class ProductCreateDto
{
    public function __construct(
        public ?int $id,
    ) {}
}
```

## License

In order to comply with [Pimcore's updated licensing policy](https://pimcore.com/en/resources/blog/breaking-free-pimcore-says-goodbye-to-gpl-and-enters-a-new-era-with-pocl), this bundle is (now) published under the GPLv3 license for compatibility Pimcore Platform Version 2024.4 and will be re-licensed under the POCL license as soon as it is compatible with Pimcore Platform Version 2025.1.

If you have any questiosn regarding licensing, please reach out to us at [info@cec.valantic.ch](mailto:info@cec.valantic.ch).
