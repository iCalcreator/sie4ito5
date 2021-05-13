## Sie4Ito5

- convert [Sie] Sie4I to Sie5 (SieEntry) import format and v.v 

#### Usage

Parse Sie4I file to Sie 5 import string

```php
<?php
namespace Kigkonsult\Sie4Ito5;
use Kigkonsult\Sie5Sdk\XMLWrite\Sie5Writer;

$sieEntry  = Sie4I::factory()->parse4I( <SIE4I.SI file> );

$xmlString = Sie5Writer::factory()->write( $sieEntry );

```

Parse Sie 5 import file to Sie4I (file) string 

```php
<?php
namespace Kigkonsult\Sie4Ito5;
use Kigkonsult\Sie5Sdk\SieParse\Sie5Parser;

$sieEntry    = Sie5Parser::factory()->parseXmlFromFile( <Sie5Entry file> );

$sie4Istring = Sie4I::factory()->write4I( $sieEntry );

```

#### Info

Sie4Ito5 require PHP7+.

Sie4Ito5 uses kigkonsult\\[SieSdk] for SieEntry parse/write XML.


###### Parse comments

Note för #GEN
 * if 'sign' is missing, '#PROGRAM programnamn' is used

\#SRU and \#UNDERDIM are skipped

Note för #VER
 * if 'regdatum' is missing, 'verdatum' is used
 * if 'sign' is missing, '#GEN sign' is used
 * When parsing Sie4I and empty #VER::serie (JournalTypeEntry::id) and/or
     \#VER::vernr (JournalEntryTypeEntry::id),
     \#VER may come in any order within 'verdatum'

Note för \#TRANS
* only support for 'dimensionsnummer och objektnummer' in the 'objektlista'
     i.e. no support for 'hierarkiska dimensioner'

\#RTRANS and \#BTRANS are skipped

\#KSUMMA ignored at parse and, for now, write

###### Write comments

 * Sie4I file creation date has format 'YYYYmmdd', SieEntry 'YYYY-MM-DDThh:mm:ssZ'



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

#### Sponsorship
Donation using [paypal.me/kigkonsult] are appreciated.
For invoice, please [e-mail]</a>.


#### Support

For support, please use [Github]/issues.

For Sie [XSD] issues, go to [Sie] homepage. 


#### License

This project is licensed under the LGPLv3 License


[Composer]:https://getcomposer.org/
[DsigSdk]:https://github.com/iCalcreator/dsigsdk
[e-mail]:mailto:ical@kigkonsult.se
[Github]:https://github.com/iCalcreator/sie4ito5/issues
[SieSdk]:https://github.com/iCalcreator/SieSdk
[paypal.me/kigkonsult]:https://paypal.me/kigkonsult
[Sie]:http://www.sie.se
[XSD]:http://www.sie.se/sie5.xsd
