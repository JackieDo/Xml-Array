# Xml-Array

[![Run tests](https://github.com/JackieDo/Xml-Array/actions/workflows/run-tests.yml/badge.svg)](https://github.com/JackieDo/Xml-Array/actions/workflows/run-tests.yml)
[![Build Status](https://api.travis-ci.org/JackieDo/Xml-Array.svg?branch=master)](https://travis-ci.org/JackieDo/Xml-Array)
[![Total Downloads](https://poser.pugx.org/jackiedo/xml-array/downloads)](https://packagist.org/packages/jackiedo/xml-array)
[![Latest Stable Version](https://poser.pugx.org/jackiedo/xml-array/v/stable)](https://packagist.org/packages/jackiedo/xml-array)
[![License](https://poser.pugx.org/jackiedo/xml-array/license)](https://packagist.org/packages/jackiedo/xml-array)

The conversion between xml and array becomes easier than ever. This package provides some very simple classes to convert XML to array and back.

# Features of this package
* Convert an XML object (DOMDocument, SimpleXMLElement) or well-formed XML string to an associative array or Json string.
* Convert an associative array to well-formed XML string or DOMDocument.
* Support parsing and building attributes, Cdata sections and namespaces of XML in conversion process.

# Overview
Look at one of the following sessions to learn more about this package.

- [Xml-Array](#xml-array)
- [Features of this package](#features-of-this-package)
- [Overview](#overview)
  - [Installation](#installation)
  - [Basic usage](#basic-usage)
    - [Convert XML to array](#convert-xml-to-array)
    - [Convert XML to Json](#convert-xml-to-json)
    - [Convert array to XML](#convert-array-to-xml)
    - [Convert array to DOM](#convert-array-to-dom)
  - [Advanced usage](#advanced-usage)
    - [Set configuration](#set-configuration)
      - [Method 1](#method-1)
      - [Method 2](#method-2)
      - [Method 3](#method-3)
    - [Get configuration](#get-configuration)
    - [Default configuration](#default-configuration)
      - [For Xml2Array](#for-xml2array)
      - [For Array2Xml](#for-array2xml)
- [License](#license)

## Installation
You can install this package through [Composer](https://getcomposer.org).

```shell
$ composer require jackiedo/xml-array
```

## Basic usage

### Convert XML to array

**Syntax**:

```
array Xml2Array::convert(DOMDocument|SimpleXMLElement|string $inputXML)->toArray();
```

> **Note:** The input XML can be one of types DOMDocument object, SimpleXMLElement object or well-formed XML string.

**Example 1** - _(Convert from XML string)_:

```php
use Jackiedo\XmlArray\Xml2Array;
...

$xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <root_node>
        <tag>Example tag</tag>

        <attribute_tag description="This is a tag with attribute">Another tag with attributes</attribute_tag>

        <cdata_section><![CDATA[ This is CDATA section ]]></cdata_section>

        <tag_with_subtag>
            <sub_tag>Sub tag 1</sub_tag>
            <sub_tag>Sub tag 2</sub_tag>
        </tag_with_subtag>

        <mixed_section>
            Hello

            <![CDATA[ This is another CDATA section ]]>

            <section id="sec_1">Section number 1</section>
            <section id="sec_2">Section number 2</section>
            <section id="sec_3">Section number 3</section>
        </mixed_section>

        <example:with_namespace xmlns:example="http://example.com">
            <example:sub>Content</example:sub>
        </example:with_namespace>
    </root_node>';

$array = Xml2Array::convert($xmlString)->toArray();
```

After running this piece of code `$array` will contain:

```php
$array = [
    "root_node" => [
        "tag"           => "Example tag",
        "attribute_tag" => [
            "@value"      => "Another tag with attributes",
            "@attributes" => [
                "description" => "This is a tag with attribute"
            ]
        ],
        "cdata_section" => [
            "@cdata" => "This is CDATA section"
        ],
        "tag_with_subtag" => [
            "sub_tag" => ["Sub tag 1", "Sub tag 2"]
        ],
        "mixed_section" => [
            "@value"  => "Hello",
            "@cdata"  => "This is another CDATA section",
            "section" => [
                [
                    "@value"      => "Section number 1",
                    "@attributes" => [
                        "id" => "sec_1"
                    ]
                ],
                [
                    "@value"      => "Section number 2",
                    "@attributes" => [
                        "id" => "sec_2"
                    ]
                ],
                [
                    "@value"      => "Section number 3",
                    "@attributes" => [
                        "id" => "sec_3"
                    ]
                ]
            ]
        ],
        "example:with_namespace" => [
            "example:sub" => "Content"
        ],
        "@attributes" => [
            "xmlns:example" => "http://example.com"
        ]
    ]
]
```

**Example 2** - _(Convert form XML object, such as SimpleXMLElement)_:

```php
use Jackiedo\XmlArray\Xml2Array;
...

$xmlObject = simplexml_load_file('https://www.vietcombank.com.vn/ExchangeRates/ExrateXML.aspx');
$array     = Xml2Array::convert($xmlObject)->toArray();
```

The result of above code is:

```php
$array = [
    "ExrateList" => [
        "DateTime" => "11/26/2018 1:56:20 PM",
        "Exrate" => [
            [
                "@attributes" => [
                    "CurrencyCode" => "AUD",
                    "CurrencyName" => "AUST.DOLLAR",
                    "Buy" => "16724.09",
                    "Transfer" => "16825.04",
                    "Sell" => "17008.7"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "CAD",
                    "CurrencyName" => "CANADIAN DOLLAR",
                    "Buy" => "17412.21",
                    "Transfer" => "17570.34",
                    "Sell" => "17762.14"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "CHF",
                    "CurrencyName" => "SWISS FRANCE",
                    "Buy" => "23074.67",
                    "Transfer" => "23237.33",
                    "Sell" => "23538.02"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "DKK",
                    "CurrencyName" => "DANISH KRONE",
                    "Buy" => "0",
                    "Transfer" => "3493.19",
                    "Sell" => "3602.67"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "EUR",
                    "CurrencyName" => "EURO",
                    "Buy" => "26264.39",
                    "Transfer" => "26343.42",
                    "Sell" => "26736.61"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "GBP",
                    "CurrencyName" => "BRITISH POUND",
                    "Buy" => "29562.43",
                    "Transfer" => "29770.83",
                    "Sell" => "30035.68"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "HKD",
                    "CurrencyName" => "HONGKONG DOLLAR",
                    "Buy" => "2939.91",
                    "Transfer" => "2960.63",
                    "Sell" => "3004.95"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "INR",
                    "CurrencyName" => "INDIAN RUPEE",
                    "Buy" => "0",
                    "Transfer" => "331.15",
                    "Sell" => "344.15"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "JPY",
                    "CurrencyName" => "JAPANESE YEN",
                    "Buy" => "200.38",
                    "Transfer" => "202.4",
                    "Sell" => "207.05"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "KRW",
                    "CurrencyName" => "SOUTH KOREAN WON",
                    "Buy" => "19.07",
                    "Transfer" => "20.07",
                    "Sell" => "21.33"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "KWD",
                    "CurrencyName" => "KUWAITI DINAR",
                    "Buy" => "0",
                    "Transfer" => "76615.44",
                    "Sell" => "79621.23"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "MYR",
                    "CurrencyName" => "MALAYSIAN RINGGIT",
                    "Buy" => "0",
                    "Transfer" => "5532.17",
                    "Sell" => "5603.76"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "NOK",
                    "CurrencyName" => "NORWEGIAN KRONER",
                    "Buy" => "0",
                    "Transfer" => "2674.72",
                    "Sell" => "2758.55"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "RUB",
                    "CurrencyName" => "RUSSIAN RUBLE",
                    "Buy" => "0",
                    "Transfer" => "349.9",
                    "Sell" => "389.89"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "SAR",
                    "CurrencyName" => "SAUDI RIAL",
                    "Buy" => "0",
                    "Transfer" => "6206.27",
                    "Sell" => "6449.75"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "SEK",
                    "CurrencyName" => "SWEDISH KRONA",
                    "Buy" => "0",
                    "Transfer" => "2536.35",
                    "Sell" => "2600.19"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "SGD",
                    "CurrencyName" => "SINGAPORE DOLLAR",
                    "Buy" => "16775.66",
                    "Transfer" => "16893.92",
                    "Sell" => "17078.33"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "THB",
                    "CurrencyName" => "THAI BAHT",
                    "Buy" => "691.64",
                    "Transfer" => "691.64",
                    "Sell" => "720.49"
                ]
            ],
            [
                "@attributes" => [
                    "CurrencyCode" => "USD",
                    "CurrencyName" => "US DOLLAR",
                    "Buy" => "23295",
                    "Transfer" => "23295",
                    "Sell" => "23385"
                ]
            ]
        ],
        "Source" => "Joint Stock Commercial Bank for Foreign Trade of Vietnam - Vietcombank"
    ]
];
```

### Convert XML to Json

**Syntax**:

```
string Xml2Array::convert(DOMDocument|SimpleXMLElement|string $inputXML)->toJson([int $options = 0]);
```

**Example 3**:

```php
$jsonString = Xml2Array::convert($xmlString)->toJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
```

### Convert array to XML

**Syntax**:

```
string Array2Xml::convert(array $array)->toXml([bool $prettyOutput = false]);
```

**Example 4**:

```php
use Jackiedo\XmlArray\Array2Xml;
...

// We will use array from the result of first example as input for this example
$xmlString = Array2Xml::convert($array)->toXml(true);
```

### Convert array to DOM

**Syntax**:

```
DOMDocument Array2Xml::convert(array $array)->toDom();
```

**Example 5**:

```php
$domObject = Array2Xml::convert($array)->toDom();
```

## Advanced usage

### Set configuration
You can set configuration for conversion process with one of following methods:

#### Method 1

```php
...
$config = [
    'valueKey' => '@text',
    'cdataKey' => '@cdata-section'
];

$array = Xml2Array::convert($inputXml, $config)->toArray();
...

// And for backward processing
$xml = Array2Xml::convert($inputArray, $config)->toXml();
```

> **Note**: Configuration is an array of parameters. For more details, see section [Default configuration](#default-configuration).

#### Method 2

```php
$converter = new Xml2Array($config);
$array     = $converter->convertFrom($inputXml)->toArray();
```

#### Method 3

```php
$converter = new Xml2Array;
$array     = $converter->setConfig($config)->convertFrom($inputXml)->toArray();
```

### Get configuration
If you implemented the conversion process using methods 2 and 3, you can get configuration of the conversion with method:

```php
$config = $converter->getConfig();
```

### Default configuration

#### For Xml2Array

```php
$defaultConfig = [
    'version'          => '1.0',         // Version of XML document
    'encoding'         => 'UTF-8',       // Encoding of XML document
    'attributesKey'    => '@attributes', // The key name use for storing attributes of node
    'cdataKey'         => '@cdata',      // The key name use for storing value of Cdata Section in node
    'valueKey'         => '@value',      // The key name use for storing text content of node
    'namespacesOnRoot' => true           // Collapse all the namespaces on the root node, otherwise it will put in the nodes for which the namespace first appeared.
];
```

#### For Array2Xml

```php
$defaultConfig = [
    'version'       => '1.0',         // Version of XML document
    'encoding'      => 'UTF-8',       // Encoding of XML document
    'attributesKey' => '@attributes', // The key name use for storing attributes of node
    'cdataKey'      => '@cdata',      // The key name use for storing value of Cdata Section in node
    'valueKey'      => '@value',      // The key name use for storing text content of node
    'rootElement'   => null,          // The name of root node will be create automatically in process of conversion
];
```

# License
[MIT](LICENSE) © Jackie Do
