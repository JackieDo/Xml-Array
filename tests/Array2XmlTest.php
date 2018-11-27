<?php

use Jackiedo\XmlArray\Array2Xml;
use PHPUnit\Framework\TestCase;

class Array2XmlTest extends TestCase
{
    /**
     * Input for test
     *
     * @var array
     */
    protected $input_array = [
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
     * Expected xml string
     *
     * @var string
     */
    protected $expected_xml_string = '<?xml version="1.0" encoding="UTF-8"?><root_node><tag>Example tag</tag><attribute_tag description="This is a tag with attribute">Another tag with attributes</attribute_tag><cdata_section><![CDATA[This is CDATA section]]></cdata_section><tag_with_subtag><sub_tag>Sub tag 1</sub_tag><sub_tag>Sub tag 2</sub_tag></tag_with_subtag><mixed_section>Hello<![CDATA[This is another CDATA section]]><section id="sec_1">Section number 1</section><section id="sec_2">Section number 2</section><section id="sec_3">Section number 3</section></mixed_section><example:with_namespace xmlns:example="http://example.com"><example:sub>Content</example:sub></example:with_namespace></root_node>';

    /**
     * Convert array to xml string
     *
     * @test
     *
     * @return void
     */
    public function convert_array_to_xml_string()
    {
        $dom = new DOMDocument;
        $dom->loadXML($this->expected_xml_string);

        $this->assertSame($dom->saveXML(), Array2Xml::convert($this->input_array)->toXml());
    }

    /**
     * Convert array to dom
     *
     * @test
     *
     * @return void
     */
    public function convert_array_to_dom()
    {
        $dom = new DOMDocument;
        $dom->loadXML($this->expected_xml_string);

        $this->assertSame($dom->saveXML(), Array2Xml::convert($this->input_array)->toDom()->saveXML());
    }
}
