# tester-php

A simple one-file php test harness.

This is a very lightweight test harness that can be easily installed
with composer, or you can just copy the tester.php file into your
own project.


# Let's see an example test.

The test is in file eq.test.php.

```
<?php

namespace tester;

class eq extends test_base {

    public function runtests() {
        $this->test1();
    }

    protected function test1() {

        $this->eq( 1, "1.00", 'one' );
        $this->eq( 2, "2.00", 'two' );
    }

}
```

## And now let's execute it.

```
$ ../tester.php 
Running tests in eq...
[pass] 1 == 1.00  |  one
[pass] 2 == 2.00  |  two


2 tests passed.
0 tests failed.
```


# Usage

```
   tester.php [testfilename]
   
   By default, all files matching *.test.php in current directory will be run.
```


# Installation and Running.


## Library install

Normally you would install tester-php into your own project using a composer
require in your project's composer.json, eg:

```
    "require": {
        "dan-da/tester-php"
    }
```

Then run composer install.

tester.php is then available at <yourproject>/vendor/bin/tester.php.

## Standalone install

```
 git clone https://github.com/dan-da/hd-wallet-derive
 cd hd-wallet-derive
 php -r "readfile('https://getcomposer.org/installer');" | php
 php composer.phar install
```


### Run tests.
```
$ cd tests
$ ../tester.php
```

# Todos

* add more example test cases
* add proper help/usage.
