## Sie4Ito5

The PHP Sie4I SDK and Sie5 conversion package

Following the Sie4I /Sie5 formats as prescribed at the [Sie] formats [page]

__*Sie4Ito5*__ supports convert/transform of accounting data from / to
- Sie4I : file / string / Dto / array / json
- SieEntry : XML / string / Dto


#### Usage

First, load a Sie4IDto instance (if not using it as input)

```php
<?php
namespace Kigkonsult\Sie4Ito5;

// Parse a Sie4I file/string into a Sie4IDto instance
$Sie4IDto = Sie4I::sie4IFileString2Sie4IDto( $Sie4I_string );
$Sie4IDto = Sie4I::sie4IFileString2Sie4IDto( $Sie4I_file );

// Transform a (HTTP, $_REQUEST) input array to a Sie4IDto instance
$Sie4IDto = Sie4I::array2Sie4IDto( $sie4I_array );

// Transform an input json string to a Sie4IDto instance
$Sie4IDto = Sie4I::json2Sie4IDto( $Sie4I_json );

// Convert a SieEntry (Sie5) instance into a Sie4IDto instance
$Sie4IDto = Sie4I::sieEntry2Sie4IDto( $sieEntry );

// Transform a SieEntry (Sie5) XML into a Sie4IDto instance
$sie4IDto = Sie4I::sieEntryXML2Sie4IDto( $sieEntryXML );

// Transform a SieEntry (Sie5) XML file into Sie4IDto instance
$sie4IDto = Sie4I::sieEntryfile2Sie4IDto( $sieEntryFile );

```

Second, validate the Sie4IDto instance

```php
<?php
namespace Kigkonsult\Sie4Ito5;

// Assert mandatory sie4IDto properties
Sie4IValidator::assertSie4IDto( $sie4IDto );
```

Last, process the output:

```php
<?php
namespace Kigkonsult\Sie4Ito5;

// Write the Sie4IDto instance to a Sie4I string
$sie4IString = Sie4I::sie4IDto2String( $sie4IDto );

// Write the Sie4IDto instance to a Sie4I file
Sie4I::sie4IDto2File( $sie4IDto, $outputfile );

// Transform the Sie4IDto instance to an array
$sie4IArray  = Sie4I::sie4IDto2Array( $sie4IDto );

// Transform the Sie4IDto instance to a json string
$sie4Ijson   = Sie4I::sie4IDto2Json( $sie4IDto );

// Convert the Sie4IDto instance to a SieEntry (Sie5) instance
$sieEntry    = Sie4I::sie4IDto2SieEntry( $sie4IDto );

// Transform the Sie4IDto instance to a SieEntry (Sie5) XML string
$sieEntryXML = Sie4I::sie4IDto2SieEntryXml( $sie4IDto );

```

#### Info

__*Sie4Ito5*__ 
- require PHP7+
- uses kigkonsult\\[SieSdk] for Sie5 SieEntry and parse/write XML parts.
- the Sie4I input/output string/file uses PHP CP437, IBM PC 8-bitars extended ASCII (Codepage 437),
all other PHP inbounding encoding (UTF-8)
- usefull constants are found in the Sie4IInterface
- for the Sie4I - Sie4IDto - array mapping scheme, review mapping.txt.<br>
  For array(/json) format, review top of src/Api/Array2Sie4IDto.php file


To set up Sie4Ito5 as a network service (using REST APIs, as a microservice etc), [Comet] is to recommend.


###### Sie4I / SieEntry comments

Note för GEN
* if _datum_ is missing, date 'now' is used
* if _sign_ is missing, (#PROGRAM) _programnamn_ is used

PROSA. FTYP, ADRESS, RAR, TAXAR and KPTYP are skipped.

SRU and UNDERDIM are skipped.

Note för VER
* if _verdatum_ is missing, date 'now' is used
* if _regdatum_ is missing, _verdatum_ is used
* if _sign_ is missing, GEN _sign_ is used

Note för TRANS
* only support for _dimensionsnummer_ and _objektnummer_ in the _objektlista_<br>
    i.e. no support for _hierarkiska dimensioner_

RTRANS and BTRANS are skipped

Sie4I dates has format _YYYYmmdd_, SieEntry _YYYY-MM-DDThh:mm:ssZ_

The (Sie4I) KSUMMA checksum is experimental.

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
Due to Sie4 and Sie5 disparity, tests will have (acceptable) breaks.
However, the output is still valid.

Test contributions, Sie4i-/SieEntry-files, are welcome!


#### Sponsorship
Donation using [paypal.me/kigkonsult] are appreciated.
For invoice, please [e-mail]</a>.


#### Support

For __*Sie4Ito5*__ support, please use [Github]/issues.

For Sie5 ([XSD]) issues, go to [Sie] homepage.


#### License

This project is licensed under the LGPLv3 License


[Composer]:https://getcomposer.org/
[Comet]:https://github.com/gotzmann/comet
[DsigSdk]:https://github.com/iCalcreator/dsigsdk
[e-mail]:mailto:ical@kigkonsult.se
[Github]:https://github.com/iCalcreator/sie4ito5/issues
[SieSdk]:https://github.com/iCalcreator/SieSdk
[page]:https://sie.se/format/
[paypal.me/kigkonsult]:https://paypal.me/kigkonsult
[Sie]:http://www.sie.se
[XSD]:http://www.sie.se/sie5.xsd

[comment]: # (This file is part of Sie4Ito5, The PHP Sie4I SDK and Sie5 conversion package. Copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence LGPLv3)
