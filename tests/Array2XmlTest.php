<?php

use Jackiedo\XmlArray\Array2Xml;
use Jackiedo\XmlArray\Tests\Traits\AdaptivePhpUnit;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class Array2XmlTest extends TestCase
{
    use AdaptivePhpUnit;

    /**
     * Input for test.
     *
     * @var array
     */
    protected $input_array = [
        'root_node' => [
            'tag'           => 'Example tag',
            'attribute_tag' => [
                '@value'      => 'Another tag with attributes',
                '@attributes' => [
                    'description' => 'This is a tag with attribute',
                ],
            ],
            'cdata_section' => [
                '@cdata' => 'This is CDATA section',
            ],
            'tag_with_subtag' => [
                'sub_tag' => ['Sub tag 1', 'Sub tag 2'],
            ],
            'mixed_section' => [
                '@value'  => 'Hello',
                '@cdata'  => 'This is another CDATA section',
                'section' => [
                    [
                        '@value'      => 'Section number 1',
                        '@attributes' => [
                            'id' => 'sec_1',
                        ],
                    ],
                    [
                        '@value'      => 'Section number 2',
                        '@attributes' => [
                            'id' => 'sec_2',
                        ],
                    ],
                    [
                        '@value'      => 'Section number 3',
                        '@attributes' => [
                            'id' => 'sec_3',
                        ],
                    ],
                ],
            ],
            'example:with_namespace' => [
                '@attributes' => [
                    'xmlns:example' => 'http://example.com',
                ],
                'example:sub' => 'Content',
            ],
        ],
    ];

    /**
     * Throw DOMException when there are more than one root node.
     *
     * @testdox Throw DOMException when there are more than one root node.
     * @test
     */
    public function throw_dom_exception_when_there_are_more_than_one_root_node()
    {
        $this->expectExceptionAndMessage(
            DOMException::class,
            'XML documents are allowed only one root element. Wrap your elements in a key or set the `rootElement` parameter in the configuration.'
        );

        Array2Xml::convert([
            'root'         => 'content',
            'another_root' => 'Another content',
        ]);
    }

    /**
     * Throw DOMException when node name is invalid.
     *
     * @testdox Throw DOMException when node name is invalid.
     * @test
     */
    public function throw_dom_exception_when_node_name_is_invalid()
    {
        $this->expectExceptionAndMessage(
            DOMException::class,
            'Invalid character in the tag name being generated: 0'
        );

        Array2Xml::convert(['content']);
    }

    /**
     * Throw DOMException when attribute name is invalid.
     *
     * @testdox Throw DOMException when attribute name is invalid.
     * @test
     */
    public function throw_dom_exception_when_attaribute_name_is_invalid()
    {
        $this->expectExceptionAndMessage(
            DOMException::class,
            'Invalid character in the attribute name being generated: invalid attribute'
        );

        Array2Xml::convert([
            'root' => [
                'sub' => [
                    '@attributes' => [
                        'invalid attribute' => 'Attribute value',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Convert array to XML string.
     *
     * @testdox Convert array to XML string.
     * @test
     */
    public function convert_array_to_xml_string()
    {
        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/resources/example.xml', Array2Xml::convert($this->input_array)->toXml());
    }

    /**
     * Convert array to DOM.
     *
     * @testdox Convert array to DOM.
     * @test
     */
    public function convert_array_to_dom()
    {
        $dom = new DOMDocument;
        $dom->loadXML(file_get_contents(__DIR__ . '/resources/example.xml'));

        $this->assertEquals($dom, Array2Xml::convert($this->input_array)->toDom());
    }
}
