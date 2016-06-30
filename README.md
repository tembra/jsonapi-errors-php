[![License](https://img.shields.io/packagist/l/tembra/jsonapi-errors-php.svg?style=flat-square)](LICENSE)
[![Version](https://img.shields.io/packagist/v/tembra/jsonapi-errors-php.svg?style=flat-square)](https://packagist.org/packages/tembra/jsonapi-errors-php)
[![Total Installs](https://img.shields.io/packagist/dt/tembra/jsonapi-errors-php.svg?style=flat-square)](https://packagist.org/packages/tembra/jsonapi-errors-php)
[![StyleCI](https://styleci.io/repos/62092362/shield)](https://styleci.io/repos/62092362)

## Description 

<a href="http://jsonapi.org/" target="_blank"><img src="http://jsonapi.org/images/jsonapi.png" alt="JSON API logo" title="JSON API" align="right" width="415" height="130" /></a>

This framework agnostic package implements a simple and efficient way to throw or respond errors in JSON API specification **version v1.0** as described in [JSON API Errors](http://jsonapi.org/format/#errors). It also helps creating documentation for these errors on your application. It is based on 3rd party package [`neomerx/json-api`](https://github.com/neomerx/json-api) that fully implements the [JSON API Format](http://jsonapi.org/format/).

It greatly simplifies the error processing with high code quality.

Still framework agnostic, you can easily integrate it with [Laravel/Lumen](https://laravel.com) and [Dingo API](https://github.com/dingo/api).

## Milestone to v1.0

+ [x] Makes JSON API Errors as simple as calling a function
+ [x] Standardize the errors
+ [x] Provide most common error functions for HTTP Status Codes
+ [x] Throw an exception or return the JSON string
+ [x] Override JSON API Error Objects members
+ [ ] Support for Localization
+ [ ] Generate Documentation for Application Error Codes
+ [ ] Build PHPUnit tests

## Sample usage

Assuming you don't want a specific class to standardize the errors and also don't want the documentation or localization, you can use as simple as this:
```php
echo MyJsonApiErrors::badRequest([
  827 => [
    'title' => 'Another Error',
    'detail' => 'Detailed error description'
  ]
], false);
```
will output **as string**
```json
{
  "errors": [
    {
      "status": "400",
      "code": "827",
      "title": "Another Error",
      "detail": "Detailed error description"
    }
  ]
}
```

The first parameter is an associative array where `key` is the error `code` in JSON API compliant format and `value` is another associative array where `key/value` pairs are some others members that JSON API Error Objects may have.

The second parameter `false` is to define whether a `JsonApiException` should be thrown or only the JSON string should be returned.

**For more advanced usage please check out the [Wiki](https://github.com/tembra/jsonapi-errors-php/wiki)**.

## Questions?

Do not hesitate to contact me on tdt@mytdt.com.br or post an [issue](https://github.com/tembra/jsonapi-errors-php/issues).

## License

BSD 3-Clause. Please see [License File](LICENSE) for more information.