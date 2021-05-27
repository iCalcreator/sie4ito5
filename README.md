## Sie4Ito5

- convert [Sie] Sie4I to Sie5 (SieEntry) import format and Sie5 to Sie4I

#### Usage

Parse Sie4I fileName/string to Sie 5 XML import string

```php
<?php
namespace Kigkonsult\Sie4Ito5;

$xmlString = Sie4I::factory()->sie4Ito5Xml( $sie4Isource );
// ...
```


Parse array Sie4I fileNames/strings to array Sie 5 XML import strings

```php
<?php
namespace Kigkonsult\Sie4Ito5;

$sie4Iparser  = Sie4Iparser::factory();
$output       = [];

foreach( $sie4Isources as $sie4Isource ) {
    $output[] = $sie4Iparser->parse4I( $sie4Isource );
} // end foreach
// ...
```

Parse Sie 5 XML import file/string to Sie4I string/file 

```php
<?php
namespace Kigkonsult\Sie4Ito5;

$sie4I = new Sie4I();

// parse to string
$sie4Istring1 = $sie4I->sie5XmlFileTo4I( $sie5EntryXMLfile );
// ...

// parse to file
$sie4Istring2 = $sie4I->sie5XmlStringTo4I( $sie5EntryXMLstring, $sie4IfileName );
// ...
```


#### Info

Sie4Ito5 require PHP7+.

Sie4Ito5 uses kigkonsult\\[SieSdk] for SieEntry parse/write XML.

To set up Sie4Ito5 as a network service (using REST APIs, as a microservice etc), [Comet] is to recommend.

###### Sie4I parse comments

Note för GEN
 * if _sign_ is missing, (#PROGRAM) _programnamn_ is used

SRU and UNDERDIM are skipped

Note för VER
 * if _regdatum_ is missing, _verdatum_ is used
 * if _sign_ is missing, (GEN) _sign_ is used

Note för TRANS
* only support for _dimensionsnummer_ and _objektnummer_ in the _objektlista_<br>
    i.e. no support for _hierarkiska dimensioner_

RTRANS and BTRANS are skipped

###### Write comments

 * Sie4I creation date has format _YYYYmmdd_,<br>SieEntry _YYYY-MM-DDThh:mm:ssZ_

 * The (Sie5 to Sie4I) KSUMMA checksum is experimental

#### Installation

[Composer], from the Command Line:

``` php
composer require kigkonsult/sie4ito5
```

[Composer], in your `composer.json`:

``` json
{
    "require": {
        "kigkonsult/sie4ito5": "dev-master"
    }
}
```

[Composer], acquire access
``` php
namespace Kigkonsult\Sie4Ito5;
...
include 'vendor/autoload.php';
```


Otherwise , download and acquire..

``` php
namespace Kigkonsult\Sie4Ito5;
...
include 'pathToSource/sie5sdk/autoload.php';
```

Run tests
```
cd pathToSource/Sie4Ito5
vendor/bin/phpunit
```
Due to Sie4 and Sie5 disparity, tests will break.<br>However, the output is valid.

#### Sponsorship
Donation using [paypal.me/kigkonsult] are appreciated.
For invoice, please [e-mail]</a>.


#### Support

For support, please use [Github]/issues.

For Sie [XSD] issues, go to [Sie] homepage. 


#### License

This project is licensed under the LGPLv3 License


[Composer]:https://getcomposer.org/
[Comet]:https://github.com/gotzmann/comet
[DsigSdk]:https://github.com/iCalcreator/dsigsdk
[e-mail]:mailto:ical@kigkonsult.se
[Github]:https://github.com/iCalcreator/sie4ito5/issues
[SieSdk]:https://github.com/iCalcreator/SieSdk
[paypal.me/kigkonsult]:https://paypal.me/kigkonsult
[Sie]:http://www.sie.se
[XSD]:http://www.sie.se/sie5.xsd
