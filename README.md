facebook-batch-api
==================

The goal is to use the [Facebook SDK](https://github.com/facebook/facebook-php-sdk) via [batch requests](https://developers.facebook.com/docs/reference/api/batch/) without having to complexify the code with the pre-processing and post-processing phases.

The trick is to use [reference](http://www.php.net/manual/en/language.references.php) to return a **temporary placeholder** and to replace the placeholder with the real result when needed.

Usage :

```php
// use the api as usual, please note the &
$me = &$facebook->batchApi('/me');
$picture = &$facebook->batchApi('/me/picture', 'GET', [
    'type' => 'large',
    'return_ssl_resources' => 1,
]);

// fill the placeholders
$facebook->processBatch();

// use the results
var_dump($me);
var_dump($picture);
```
