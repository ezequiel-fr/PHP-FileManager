# FileManager
![Licence](https://badgen.net/badge/licence/unliscence/blue?icon=github)
![Licence](https://badgen.net/badge/PHP/8.0.0/green?icon=github)
<!-- ![Licence](https://badgen.net/badge/Downloads//blue?icon=github) -->

FileManager is an open-source library that has many features related to file management.
For example, scanning folders, deleting, copying, ... \
It manages files as well as folders.²

This PHP dependence is made by [Ezequiel Dev](https://github.com/TheRedMineTheRedMine). \
All credits and rights are retained. Thus, although the code is open-source, we disclaim any warranty or liability for any particular misuse.

## Features
* Index files
    * Recursively if specified
* Remove folder recurisvely

## Getting started with code

### In command prompt
To run the code of this package, you can just type this line of code in you Command Prompt (CMD).
```cmd
run.bat
```
Once you tap this command, the server will run at 7000.
You can run the code on going to the [https://127.0.0.1:7000/](https://127.0.0.1:7000/)

### With debugger
If you want to run the code thanks to the debugger (Command Prompt, VS Code PHP Debugger, PHPStorm, ...) don't forgot to only run the index.php file.

## Using *FileManager*

### Import files
To use **FileManager** into you PHP page, you will first need to import the `autoload.php` file like bellow.
```php
require __DIR__.'/FileManager/autoload.php';
```

### Using classes
The class you will use the most is `\FileManager\FileManager`.\
To use it you just have to create a new instance like that.
```php
// Using the class directly
$query = new \FileManager\FileManager();


// Using the namespace and class
use \FileManager\FileManager;

$query = new FileManager();
```

### And after ?
You have two simple options :
 - Use the functions of the `FileManager` class by intuition.
 - Read the [documentation](USAGE.md) about how to use the different functions.
