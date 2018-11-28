<?php

use Jackiedo\XmlArray\Xml2Array;
use PHPUnit\Framework\TestCase;

class Xml2ArrayTest extends TestCase
{
    /**
     * Store expected result for full test
     *
     * @var string
     */
    protected $fulltest_expected_result = [
        "root_node" => [
            "tag" => "Example tag",
            "attribute_tag" => [
                "@value" => "Another tag with attributes",
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
                "@value" => "Hello",
                "@cdata" => "This is another CDATA section",
                "section" => [
                    [
                        "@value" => "Section number 1",
                        "@attributes" => [
                            "id" => "sec_1"
                        ]
                    ],
                    [
                        "@value" => "Section number 2",
                        "@attributes" => [
                            "id" => "sec_2"
                        ]
                    ],
                    [
                        "@value" => "Section number 3",
                        "@attributes" => [
                            "id" => "sec_3"
                        ]
                    ]
                ]
            ],
            "example:with_namespace" => [
                "@attributes" => [
                    "xmlns:example" => "http://example.com"
                ],
                "example:sub" => "Content"
            ],
        ]
    ];

    /**
     * Convert to array from an XML string containing only one empty node
     *
     * @test
     *
     * @return void
     */
    public function convert_to_array_from_an_xml_string_containing_only_one_empty_node()
    {
        $string = '<root />';

        $this->assertEquals([
            'root' => ''
        ], Xml2Array::convert($string)->toArray());
    }

    /**
     * Convert to array from an XML string containing one node with empty value
     *
     * @test
     *
     * @return void
     */
    public function convert_to_array_from_an_xml_string_containing_one_node_with_empty_value()
    {
        $string = '<root></root>';

        $this->assertEquals([
            'root' => ''
        ], Xml2Array::convert($string)->toArray());
    }

    /**
     * Convert to array from XML string containing one node that whose value is on one line
     *
     * @test
     *
     * @return void
     */
    public function convert_to_array_from_xml_string_containing_one_node_that_whose_value_is_on_one_line()
    {
        $string = '<root>Welcome to Xml2Array Converter</root>';

        $this->assertEquals([
            'root' => 'Welcome to Xml2Array Converter'
        ], Xml2Array::convert($string)->toArray());
    }

    /**
     * Convert to array from XML string containing one node that whose value is on multilines
     *
     * @test
     *
     * @return void
     */
    public function convert_to_array_from_xml_string_containing_one_node_that_whose_value_is_on_multilines()
    {
        $string = '<root>

            Welcome to

            Xml2Array Converter
        </root>';

        $this->assertEquals([
            'root' => "Welcome to Xml2Array Converter"
        ], Xml2Array::convert($string)->toArray());
    }

    /**
     * Convert to array from XML string containing one node that whose value is Cdata Section
     *
     * @test
     *
     * @return void
     */
    public function convert_to_array_from_xml_string_containing_one_node_that_whose_value_is_cdata_section()
    {
        $string = '<root><![CDATA[ This is CDATA section ]]></root>';

        $this->assertEquals([
            'root' => [
                '@cdata' => 'This is CDATA section'
            ]
        ], Xml2Array::convert($string)->toArray());
    }

    /**
     * Convert to array from XML string that root node has sub node
     *
     * @test
     *
     * @return void
     */
    public function convert_to_array_from_xml_string_that_root_node_has_sub_node()
    {
        $string = '<root>
            <subnode_1>Node value</subnode_1>
            <subnode_2>Node value</subnode_2>
        </root>';

        $this->assertEquals([
            'root' => [
                'subnode_1' => 'Node value',
                'subnode_2' => 'Node value'
            ]
        ], Xml2Array::convert($string)->toArray());
    }

    /**
     * Convert to array from XML string that node only has attributes
     *
     * @test
     *
     * @return void
     */
    public function convert_to_array_from_xml_string_that_node_only_has_attributes()
    {
        $string = '<root>
            <subnode_1 description="Subnode #1"></subnode_1>
            <subnode_2 description="Subnode #2"></subnode_2>
        </root>';

        $this->assertEquals([
            'root' => [
                'subnode_1' => [
                    '@attributes' => [
                        'description' => 'Subnode #1'
                    ]
                ],
                'subnode_2' => [
                    '@attributes' => [
                        'description' => 'Subnode #2'
                    ]
                ]
            ]
        ], Xml2Array::convert($string)->toArray());
    }

    /**
     * Convert to array from XML string that node has value and attributes
     *
     * @test
     *
     * @return void
     */
    public function convert_to_array_from_xml_string_that_node_has_value_and_attributes()
    {
        $string = '<root>
            <subnode_1 description="Subnode #1">Node value</subnode_1>
            <subnode_2 description="Subnode #2">Node value</subnode_2>
        </root>';

        $this->assertEquals([
            'root' => [
                'subnode_1' => [
                    '@value' => 'Node value',
                    '@attributes' => [
                        'description' => 'Subnode #1'
                    ]
                ],
                'subnode_2' => [
                    '@value' => 'Node value',
                    '@attributes' => [
                        'description' => 'Subnode #2'
                    ]
                ]
            ]
        ], Xml2Array::convert($string)->toArray());
    }

    /**
     * Convert to array from XML string containing has namespaces
     *
     * @test
     *
     * @return void
     */
    public function convert_to_array_from_xml_string_containing_has_namespaces()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
        <root_node>
            <example:node_with_namespace xmlns:example="http://example.com">
                <example:sub example:description="An attribute with namespace">Content</example:sub>
            </example:node_with_namespace>
        </root_node>';

        $this->assertEquals([
            'root_node' => [
                'example:node_with_namespace' => [
                    'example:sub' => [
                        '@value' => 'Content',
                        '@attributes' => [
                            'example:description' => 'An attribute with namespace'
                        ]
                    ]
                ],
                '@attributes' => [
                    'xmlns:example' => 'http://example.com'
                ]
            ]
        ], Xml2Array::convert($string)->toArray());
    }

    /**
     * Convert to array from XML string with special config
     *
     * @test
     *
     * @return void
     */
    public function convert_to_array_from_xml_string_with_special_config()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
        <root_node>
            <sub_node description="An attribute">Content</sub_node>
        </root_node>';

        $this->assertEquals([
            'root_node' => [
                'sub_node' => [
                    '#text' => 'Content',
                    '#attributes' => [
                        'description' => 'An attribute'
                    ]
                ]
            ]
        ], Xml2Array::convert($string, [
            'valueKey' => '#text',
            'attributesKey' => '#attributes'
        ])->toArray());
    }

    /**
     * Convert from XML string to array with all cases
     *
     * @test
     */
    public function convert_from_xml_string_to_array_with_all_cases()
    {
        $string = file_get_contents(__DIR__ . '/resources/example.xml');

        $result = Xml2Array::convert($string, [
            'namespacesOnRoot' => false
        ])->toArray();

        $this->assertEquals($this->fulltest_expected_result, $result);
    }

    /**
     * Convert from XML object to array with all cases
     *
     * @test
     */
    public function convert_from_xml_object_to_array()
    {
        $xmlObject = simplexml_load_file(__DIR__ . '/resources/example.xml');

        $result = Xml2Array::convert($xmlObject, [
            'namespacesOnRoot' => false
        ])->toArray();

        $this->assertEquals($this->fulltest_expected_result, $result);
    }

    /**
     * Convert from DOM object to array with all cases
     *
     * @test
     */
    public function convert_from_dom_object_to_array()
    {
        $domObject = new DOMDocument;
        $domObject->load(__DIR__ . '/resources/example.xml');

        $result = Xml2Array::convert($domObject, [
            'namespacesOnRoot' => false
        ])->toArray();

        $this->assertEquals($this->fulltest_expected_result, $result);
    }

    /**
     * Throw DOMException when input XML string is not well-formed XML
     *
     * @test
     *
     * @return void
     */
    public function throw_dom_exception_when_input_xml_string_not_well_formed()
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Error parsing XML string, input is not a well-formed XML string.');

        Xml2Array::convert('123');
    }

    /**
     * Throw DOMException when invalid input
     *
     * @test
     *
     * @return void
     */
    public function throw_dom_exception_when_invalid_input()
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('The input XML must be one of types DOMDocument, SimpleXMLElement or well-formed XML string.');

        Xml2Array::convert([]);
    }

    /**
     * Convert from XML string to Json
     *
     * @test
     */
    public function convert_from_xml_string_to_json()
    {
        $string = file_get_contents(__DIR__ . '/resources/example.xml');
        $result = Xml2Array::convert($string)->toJson();

        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/resources/example.json', $result);
    }
}
