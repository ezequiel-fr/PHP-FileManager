## Usage of the PHP-FileManager
In the lines bellow, you will see how the function of this package works. But not all functions are here (only importants and those almost finished).
 - [Get started](#specify-the-ressource)
 - [Indexing files](#indexing-files)
 - [Removing directory](#removing-a-folder)

### Specify the ressource
You have two methods, by specify it the new object or with the `setDirectory` function.
```php
# When creating the object
$query = new FileManager\FileManager("../path");

# Or with the setDirectory function.
$query->setDirectory('/');
```

### Indexing files
One of the most important file of the package, `Methods\Scan`. \
To index files from a folder, you can just run this function
```php
# You will declare the path before
$result = $query->scan();

# Otherwise, you can specify it
$result = $query->scan(bool, "/foo/bar")
```

Also so the *`bool`* value must be a boolean (0/1, true/false) not just the attribute *`bool`* like above.
 - `true` : the function will index files recursively.
 - `false` : the function will only return the resource found in the specified directory.

Don't forget that using recursivity or indexing files of a directory can be longer than usual.

### Removing a folder
Removing a folder using the `Methods\Destroy`. \
After specify the directory you will remove, just add this line to run the destroy execution.
```php
$query->destroy();
```
