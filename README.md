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

// execute the batch to fill $me and $picture with the real value
$facebook->processBatch();

// use the results
var_dump($me);
var_dump($picture);
```

Batch requests containing multiple methods
------------------------------------------
```sh
curl \
    -F 'access_token=…' \
    -F 'batch=[{ "method":"POST","relative_url":"me/feed","body":"message=Test status update&link=https://developers.facebook.com/"},{ "method":"GET","relative_url":"me/feed?limit=1"}]' \
    https://graph.facebook.com
```

The second parameter makes it easy to alternate methods.
```php
$result1 = &$facebook->batchApi('me/feed', 'POST', [
    'message' => 'Test status update',
    'link' => 'https://developers.facebook.com/',
]);
$result2 = &$facebook->batchApi('me/feed', 'GET', [
    'limit' => 1,
]);
$facebook->processBatch();
```

Specifying dependencies between operations in the request
---------------------------------------------------------
```sh
curl \
   -F 'access_token=...' \
   -F 'batch=[{ "method":"GET","name":"get-friends","relative_url":"me/friends?limit=5",},{"method":"GET","relative_url":"?ids={result=get-friends:$.data.*.id}"}]' \
   https://graph.facebook.com/
```
A batch request can be named with the fourth parameter for later use.
```php
$result1 = &$facebook->batchApi('me/friends', 'GET', [
    'limit' => 5,
], [
    'name' => 'get-friends',
]);
$result2 = &$facebook->batchApi('/', 'GET', [
    'ids' => '{result=get-friends:$.data.*.id}',
]);
$facebook->processBatch();
```

Uploading binary data
---------------------
```sh
curl 
     -F 'access_token=…' \
     -F 'batch=[{"method":"POST","relative_url":"me/photos","body":"message=My cat photo","attached_files":"file1"},{"method":"POST","relative_url":"me/photos","body":"message=My dog photo","attached_files":"file2"},]' \
     -F 'file1=@cat.gif' \
     -F 'file2=@dog.jpg' \
    https://graph.facebook.com
```
The method attachFile can be used to attach a file. The correspondance between the file name and the batch parameter is made internally.
```php
$result1 = &$facebook->batchApi('me/photos', 'POST', [
    'message' => 'My cat photo',
], [
    'attached_files' => $facebook->attachFile('@cat.gif'),
]);
$result2 = &$facebook->batchApi('me/photos', 'POST', [
    'message' => 'My dog photo',
], [
    'attached_files' => $facebook->attachFile('@dog.jpg'),
]);
$facebook->processBatch();
```
