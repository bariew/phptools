
PHP tools for web development.
===================

1. FileModel - for extracting and saving data to file.
```
    $model = new \bariew\phptools\FileModel("/path/to/file.php");
    var_dump($model->data); // array(... file content);
    $model->set('myKey', 'myValue'); // now we have our value in file content array;
    $model->set(['my', 'multidimensional', 'key'], ['myValue']); // putting nested key into depth of file content array.

```