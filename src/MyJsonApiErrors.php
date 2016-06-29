<?php

namespace Mytdt\JsonApi\Errors;

use Exception;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Exceptions\ErrorCollection;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use ReflectionClass;
use ReflectionMethod;

class MyJsonApiErrors
{
    /**
     * The error array to be overridden with the app error codes.
     *
     * @var array
     */
    protected static $errors = [];

    /**
     * The collection to add one or more errors.
     *
     * @var \Neomerx\JsonApi\Exceptions\ErrorCollection
     */
    protected static $errorsCollection;

    /**
     * The HTTP Status Code for the error response.
     *
     * @var int
     */
    protected static $statusCode;

    /**
     * Returns a 400 bad request error with app error codes.
     *
     * @param int|array $codes
     * @param bool      $throw
     *
     * @throws \Neomerx\JsonApi\Exceptions\JsonApiException
     *
     * @return null|string
     */
    public static function badRequest($codes, $throw = true)
    {
        return self::error($codes, 400, $throw);
    }

    /**
     * Returns a 401 unauthorized error with app error codes.
     *
     * @param int|array $codes
     * @param bool      $throw
     *
     * @throws \Neomerx\JsonApi\Exceptions\JsonApiException
     *
     * @return null|string
     */
    public static function unauthorized($codes, $throw = true)
    {
        return self::error($codes, 401, $throw);
    }

    /**
     * Returns a 403 forbidden error with app error codes.
     *
     * @param int|array $codes
     * @param bool      $throw
     *
     * @throws \Neomerx\JsonApi\Exceptions\JsonApiException
     *
     * @return null|string
     */
    public static function forbidden($codes, $throw = true)
    {
        return self::error($codes, 403, $throw);
    }

    /**
     * Returns a 404 not found error with app error codes.
     *
     * @param int|array $codes
     * @param bool      $throw
     *
     * @throws \Neomerx\JsonApi\Exceptions\JsonApiException
     *
     * @return null|string
     */
    public static function notFound($codes, $throw = true)
    {
        return self::error($codes, 404, $throw);
    }

    /**
     * Returns a 405 method not allowed error with app error codes.
     *
     * @param int|array $codes
     * @param bool      $throw
     *
     * @throws \Neomerx\JsonApi\Exceptions\JsonApiException
     *
     * @return null|string
     */
    public static function methodNotAllowed($codes, $throw = true)
    {
        return self::error($codes, 405, $throw);
    }

    /**
     * Returns a 500 internal server error with app error codes.
     *
     * @param int|array $codes
     * @param bool      $throw
     *
     * @throws \Neomerx\JsonApi\Exceptions\JsonApiException
     *
     * @return null|string
     */
    public static function internal($codes, $throw = true)
    {
        return self::error($codes, 500, $throw);
    }

    /**
     * Returns a generic error (default is 500) with app error codes.
     *
     * @param int|array $codes
     * @param int       $statusCode
     * @param bool      $throw
     *
     * @throws \Neomerx\JsonApi\Exceptions\JsonApiException
     *
     * @return null|string
     */
    public static function error($codes, $statusCode = 500, $throw = true)
    {
        self::$statusCode = $statusCode;
        self::morph($codes);
        if ($throw) {
            self::throwError();
        } else {
            return self::jsonError();
        }
    }

    /**
     * Throws an exception with JSON API Errors compliant format.
     *
     * @throws \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    private static function throwError()
    {
        throw new JsonApiException(self::$errorsCollection, self::$statusCode);
    }

    /**
     * Returns the errors encoded in a JSON string format.
     *
     * @return string
     */
    private static function jsonError()
    {
        $encoder = Encoder::instance();

        return $encoder->encodeErrors(self::$errorsCollection);
    }

    /**
     * Morphs codes int|array into an ErrorCollection.
     *
     * @param int|array $codes
     *
     * @throws \Exception
     */
    private static function morph($codes)
    {
        if (is_int($codes)) {
            $codes = [$codes];
        } elseif (!is_array($codes)) {
            throw new Exception('Invalid code format given to MyJsonApiErrors.');
        }
        self::$errorsCollection = new ErrorCollection();
        foreach ($codes as $key => $value) {
            if (is_int($value)) {
                self::addError($value);
            } elseif (is_array($value)) {
                $title = !array_key_exists('title', $value) ? null : $value['title'];
                $detail = !array_key_exists('detail', $value) ? null : $value['detail'];
                $method = !array_key_exists('method', $value) ? null : $value['method'];
                $parameter = !array_key_exists('parameter', $value) ? null : $value['parameter'];
                $idx = !array_key_exists('id', $value) ? null : $value['id'];
                $links = !array_key_exists('links', $value) ? null : $value['links'];
                $meta = !array_key_exists('meta', $value) ? null : $value['meta'];
                self::addError($key, $title, $detail, $method, $parameter, $idx, $links, $meta);
            } else {
                throw new Exception('Invalid array code format given to MyJsonApiErrors.');
            }
        }
    }

    /**
     * Adds new Error object to the collection.
     *
     * @param string     $code
     * @param string     $title
     * @param string     $detail
     * @param string     $method
     * @param string     $parameter
     * @param int|string $idx
     * @param array      $links
     * @param array      $meta
     *
     * @throws \Exception
     */
    private static function addError($code, $title = null, $detail = null, $method = null, $parameter = null, $idx = null, array $links = null, array $meta = null)
    {
        $error = self::checkError($code);
        $requiredTitle = count($error) == 0 ? false : true;
        $title = $title ? $title : self::checkErrorData($error, 'title', $requiredTitle);
        $detail = $detail ? $detail : self::checkErrorData($error, 'detail');
        $method = $method ? $method : self::checkErrorData($error, 'method');
        $parameter = $parameter ? $parameter : self::checkErrorData($error, 'parameter');
        $idx = $idx ? $idx : self::checkErrorData($error, 'id');
        $links = $links ? $links : self::checkErrorData($error, 'links');
        $meta = $meta ? $meta : self::checkErrorData($error, 'meta');

        $links = self::checkLinks($links);
        $meta = self::checkMeta($meta);

        if (!$method) {
            $error = new Error($idx, $links, self::$statusCode, $code, $title, $detail, null, $meta);
            self::$errorsCollection->add($error);
        } else {
            $method = 'add'.ucfirst($method).'Error';
            $reflection = new ReflectionClass(self::$errorsCollection);
            if ($reflection->hasMethod($method)) {
                if ($reflection->getMethod($method)->getNumberOfParameters() == 8) {
                    if ($parameter) {
                        self::$errorsCollection->$method($parameter, $title, $detail, self::$statusCode, $idx, $links, $code, $meta);
                    } else {
                        throw new Exception('Invalid property format errors given to MyJsonApiErrors. The method provided expects a parameter.');
                    }
                } else {
                    self::$errorsCollection->$method($title, $detail, self::$statusCode, $idx, $links, $code, $meta);
                }
            } else {
                $allMethods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                $availableMethods = [];
                foreach ($allMethods as $method) {
                    if (substr($method->name, 0, 3) == 'add' && substr($method->name, -5) == 'Error') {
                        $availableMethods[] = lcfirst(substr($method->name, 3, strlen($method->name) - 8));
                    }
                }
                $availableMethods = implode(', ', $availableMethods);
                throw new Exception('Invalid property format errors given to MyJsonApiErrors. Invalid method provided. Available methods: '.$availableMethods.'.');
            }
        }
    }

    /**
     * Checks whether code given exists on error array.
     *
     * @param int $code
     *
     * @return array
     */
    private static function checkError($code)
    {
        if (!array_key_exists($code, static::$errors)) {
            return [];
        }

        return static::$errors[$code];
    }

    /**
     * Checks whether specified property exists on error code array.
     *
     * @param array  $error
     * @param string $property
     * @param bool   $required
     *
     * @throws \Exception
     *
     * @return string
     */
    private static function checkErrorData(array $error, $property, $required = false)
    {
        if (!array_key_exists($property, $error)) {
            if ($required) {
                if (count($error) == 0) {
                    throw new Exception('Invalid error code given to MyJsonApiErrors. No '.$property.' was found.');
                } else {
                    throw new Exception('Invalid property format errors given to MyJsonApiErrors. No '.$property.' was provided.');
                }
            }

            return;
        }

        return $error[$property];
    }

    /**
     * Checks whether links was provided and whether it is on valid format.
     *
     * @param array $links
     *
     * @throws \Exception
     *
     * @return null|\Neomerx\JsonApi\Document\Link
     */
    private static function checkLink(array $links = null)
    {
        if (!$links) {
            return;
        }
        $linksHref = !array_key_exists('href', $links) ? null : $links['href'];
        $linksMeta = !array_key_exists('meta', $links) ? null : $links['meta'];
        if ($linksMeta) {
            if (!is_array($linksMeta)) {
                throw new Exception('Invalid links meta given to MyJsonApiErrors.');
            } else {
                if (!self::isObject($linksMeta)) {
                    throw new Exception('Invalid links meta given to MyJsonApiErrors. Not an array representing an object.');
                }
            }
        }

        return !$linksHref ? null : new Link($linksHref, $linksMeta);
    }

    /**
     * Checks whether meta was provided and whether it is on valid format.
     *
     * @param array $meta
     *
     * @throws \Exception
     *
     * @return null|array
     */
    private static function checkMeta(array $meta = null)
    {
        if (!$meta) {
            return;
        }
        if (!self::isObject($meta)) {
            throw new Exception('Invalid meta given to MyJsonApiErrors. Not an array representing an object.');
        }

        return $meta;
    }

    /**
     * Checks whether an array is in the JSON object format.
     *
     * @param array $object
     *
     * @return bool
     */
    private static function isObject(array $object)
    {
        foreach ($object as $key => $value) {
            if (!is_string($key)) {
                return false;
            }
        }

        return true;
    }
}
