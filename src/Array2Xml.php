<?php

namespace Jackiedo\XmlArray;

use DOMDocument;
use DOMElement;
use DOMException;

/**
 * A class to convert array in PHP to well-formed XML.
 *
 * @see https://github.com/JackieDo/Xml-Array/blob/master/README.md Documentation.
 *
 * @author Jackie Do <anhvudo@gmail.com>
 * @license MIT
 */
class Array2Xml
{
    /**
     * The configuration of the conversion.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The working XML document.
     *
     * @var DOMDocument
     */
    protected $xml;

    /**
     * Constructor.
     *
     * @param array $config The configuration to use for this instance
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);

        $this->xml = new DOMDocument($this->config['version'], $this->config['encoding']);
    }

    /**
     * Set configuration for converter.
     *
     * @param array $config The configuration to use for conversion
     *
     * @return $this
     */
    public function setConfig(array $config = [])
    {
        $defaultConfig = [
            'version'       => '1.0',
            'encoding'      => 'UTF-8',
            'attributesKey' => '@attributes',
            'cdataKey'      => '@cdata',
            'valueKey'      => '@value',
            'rootElement'   => null,
        ];

        $this->config = array_merge($defaultConfig, $config);

        return $this;
    }

    /**
     * Return configuration of converter.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Convert an array to an XML document
     * A static facade for ease of use and backwards compatibility.
     *
     * @param array $array  The input array
     * @param array $config The configuration to use for the conversion
     *
     * @return $this
     */
    public static function convert(array $array = [], array $config = [])
    {
        $instance = new static($config);

        return $instance->convertFrom($array);
    }

    /**
     * Convert an array to an XML document.
     *
     * @param array $array The input array
     *
     * @throws DOMException
     *
     * @return $this
     */
    public function convertFrom(array $array = [])
    {
        if (isset($this->config['rootElement'])) {
            $rootElement = $this->config['rootElement'];
        } elseif (1 == count($array)) {
            $rootElement = array_keys($array)[0];
            $array       = $array[$rootElement];
        } else {
            throw new DOMException('XML documents are allowed only one root element. Wrap your elements in a key or set the `rootElement` parameter in the configuration.');
        }

        $this->xml->appendChild($this->buildNode($rootElement, $array));

        return $this;
    }

    /**
     * Return as XML.
     *
     * @param $prettyOutput Format output for DOMDocument
     *
     * @return string
     */
    public function toXml($prettyOutput = false)
    {
        $this->xml->formatOutput = (bool) $prettyOutput;

        return $this->xml->saveXML();
    }

    /**
     * Return as DOM object.
     *
     * @return DOMDocument
     */
    public function toDom()
    {
        return $this->xml;
    }

    /**
     * Build XML node.
     *
     * @param string $nodeName The name of the node that the data will be stored under
     * @param mixed  $data     The value to be build
     *
     * @throws DOMException
     *
     * @return DOMElement The XML representation of the input data
     */
    protected function buildNode($nodeName, $data)
    {
        if (!$this->isValidTagName($nodeName)) {
            throw new DOMException('Invalid character in the tag name being generated: ' . $nodeName);
        }

        $node = $this->xml->createElement($nodeName);

        if (is_array($data)) {
            $this->parseArray($node, $data);
        } else {
            $node->appendChild($this->xml->createTextNode($this->normalizeValues($data)));
        }

        return $node;
    }

    /**
     * Parse array to build node.
     *
     * @param DOMElement $node
     * @param array      $array
     *
     * @throws DOMException
     *
     * @return void
     */
    protected function parseArray(DOMElement $node, array $array)
    {
        // get the attributes first.;
        $array = $this->parseAttributes($node, $array);

        // get value stored in @value
        $array = $this->parseValue($node, $array);

        // get value stored in @cdata
        $array = $this->parseCdata($node, $array);

        // recurse to build child nodes for this node
        foreach ($array as $key => $value) {
            if (!$this->isValidTagName($key)) {
                throw new DOMException('Invalid character in the tag name being generated: ' . $key);
            }

            if (is_array($value) && is_numeric(key($value))) {
                // MORE THAN ONE NODE OF ITS KIND;
                // if the new array is numeric index, means it is array of nodes of the same kind
                // it should follow the parent key name
                foreach ($value as $v) {
                    $node->appendChild($this->buildNode($key, $v));
                }
            } else {
                // ONLY ONE NODE OF ITS KIND
                $node->appendChild($this->buildNode($key, $value));
            }

            unset($array[$key]);
        }
    }

    /**
     * Build attributes of node.
     *
     * @param DOMElement $node
     * @param array      $array
     *
     * @throws DOMException
     *
     * @return array
     */
    protected function parseAttributes(DOMElement $node, array $array)
    {
        $attributesKey = $this->config['attributesKey'];

        if (array_key_exists($attributesKey, $array) && is_array($array[$attributesKey])) {
            foreach ($array[$attributesKey] as $key => $value) {
                if (!$this->isValidTagName($key)) {
                    throw new DOMException('Invalid character in the attribute name being generated: ' . $key);
                }

                $node->setAttribute($key, $this->normalizeValues($value));
            }

            unset($array[$attributesKey]);
        }

        return $array;
    }

    /**
     * Build value of node.
     *
     * @param DOMElement $node
     * @param array      $array
     *
     * @return array
     */
    protected function parseValue(DOMElement $node, array $array)
    {
        $valueKey = $this->config['valueKey'];

        if (array_key_exists($valueKey, $array)) {
            $node->appendChild($this->xml->createTextNode($this->normalizeValues($array[$valueKey])));

            unset($array[$valueKey]);
        }

        return $array;
    }

    /**
     * Build CDATA of node.
     *
     * @param DOMElement $node
     * @param array      $array
     *
     * @return array
     */
    protected function parseCdata(DOMElement $node, array $array)
    {
        $cdataKey = $this->config['cdataKey'];

        if (array_key_exists($cdataKey, $array)) {
            $node->appendChild($this->xml->createCDATASection($this->normalizeValues($array[$cdataKey])));

            unset($array[$cdataKey]);
        }

        return $array;
    }

    /**
     * Get string representation of values.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function normalizeValues($value)
    {
        $value = true  === $value ? 'true' : $value;
        $value = false === $value ? 'false' : $value;
        $value = null  === $value ? '' : $value;

        return (string) $value;
    }

    /**
     * Check if the tag name or attribute name contains illegal characters.
     *
     * @see: http://www.w3.org/TR/xml/#sec-common-syn
     *
     * @param  string
     * @param mixed $tag
     *
     * @return bool
     */
    protected function isValidTagName($tag)
    {
        $pattern = '/^[a-zA-Z_][\w\:\-\.]*$/';

        return preg_match($pattern, $tag, $matches) && $matches[0] == $tag && ':' != substr($tag, -1, 1);
    }
}
