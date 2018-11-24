# Xml Array
[![Total Downloads](https://poser.pugx.org/jackiedo/xml-array/downloads)](https://packagist.org/packages/jackiedo/xml-array)
[![Latest Stable Version](https://poser.pugx.org/jackiedo/xml-array/v/stable)](https://packagist.org/packages/jackiedo/xml-array)
[![Latest Unstable Version](https://poser.pugx.org/jackiedo/xml-array/v/unstable)](https://packagist.org/packages/jackiedo/xml-array)
[![License](https://poser.pugx.org/jackiedo/xml-array/license)](https://packagist.org/packages/jackiedo/xml-array)

The conversion between xml and array becomes easier than ever. This package provides some very simple classes to convert XML to array and back.

# Features of this package
* Convert an XML object (DOMDocument, SimpleXMLElement) or well-formed XML string to an associative array or Json string.
* Convert an associative array to well-formed XML string or DOMDocument.
* Support parsing and building attributes, Cdata sections and namespaces of XML in conversion process.

# Overview
Look at one of the following sessions to learn more about this package.

* [Installation](#installation)
* [Basic usage](#basic-usage)
    - [Convert XML to array](#convert-xml-to-array)
    - [Convert XML to Json](#convert-xml-to-json)
    - [Convert array to XML](#convert-array-to-xml)
    - [Convert array to DOM](#convert-array-to-dom)
* [Advanced usage](#advanced-usage)
    - [Set configuration](#set-configuration)
    - [Get configuration](#get-configuration)
    - [Default configuration](#default-configuration)
* [License](#license)

## Installation
You can install this package through [Composer](https://getcomposer.org).

```shell
composer require jackiedo/xml-array
```

## Basic usage

### Convert XML to array

###### Syntax:

```
array Xml2Array::convert(DOMDocument|SimpleXMLElement|string $inputXML)->toArray();
```

> **Note:** The input XML can be one of types DOMDocument object, SimpleXMLElement object or well-formed XML string.

###### Example:

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

### Convert XML to Json

###### Syntax:

```
string Xml2Array::convert(DOMDocument|SimpleXMLElement|string $inputXML)->toJson([int $options = 0]);
```

###### Example:

```php
$jsonString = Xml2Array::convert($xmlString)->toJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
```

### Convert array to XML

###### Syntax:

```
string Array2Xml::convert(array $array)->toXml([bool $prettyOutput = false]);
```

###### Example:

```php
use Jackiedo\XmlArray\Array2Xml;
...

// We will use array from the result of above example as input for this example
$xmlString = Array2Xml::convert($array)->toXml(true);
```

### Convert array to DOM

###### Syntax:

```
DOMDocument Array2Xml::convert(array $array)->toDom();
```

###### Example:

```php
$domObject = Array2Xml::convert($array)->toDom();
```

## Advanced usage

### Set configuration
You can set configuration for conversion process with one of following methods:

###### Method 1:

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

###### Method 2:

```php
$converter = new Xml2Array($config);
$array     = $converter->convertFrom($inputXml)->toArray();
```

###### Method 3:

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

###### For Xml2Array

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

###### For Array2Xml

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

## License
[MIT](LICENSE) © Jackie Do
