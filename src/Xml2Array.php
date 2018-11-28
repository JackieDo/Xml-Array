<?php namespace Jackiedo\XmlArray;

use DOMDocument;
use DOMException;
use DOMNode;
use SimpleXMLElement;

/**
 * Xml2Array: A class to convert XML to an array in PHP
 * Takes a DOMDocument object or SimpleXMLElement object or an XML string as input.
 *
 * Usage:
 *       $array = Xml2Array::convert($xml)->toArray();
 *       $array = Xml2Array::convert($xml, ['useNamespaces' => true])->toArray();
 *       $json  = Xml2Array::convert($xml)->toJson();
 *
 * @package xml-array
 * @author Jackie Do <anhvudo@gmail.com>
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class Xml2Array
{
    /**
     * The name of the XML attribute that indicates a namespace definition
     */
    const ATTRIBUTE_NAMESPACE = 'xmlns';

    /**
     * The string that separates the namespace attribute from the prefix for the namespace
     */
    const ATTRIBUTE_NAMESPACE_SEPARATOR = ':';

    /**
     * The configuration of the current instance
     *
     * @var array
     */
    protected $config = [];

    /**
     * The working XML document
     *
     * @var DOMDocument
     */
    protected $xml = null;

    /**
     * The working list of XML namespaces
     *
     * @var array
     */
    protected $namespaces = [];

    /**
     * The result of this conversion
     *
     * @var array
     */
    protected $array = [];

    /**
     * Constructor
     *
     * @param array $config The configuration to use for this instance
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * Set configuration for converter
     *
     * @param  array  $config The configuration to use for conversion
     *
     * @return $this
     */
    public function setConfig(array $config = [])
    {
        $defaultConfig = [
            'version'          => '1.0',
            'encoding'         => 'UTF-8',
            'attributesKey'    => '@attributes',
            'cdataKey'         => '@cdata',
            'valueKey'         => '@value',
            'namespacesOnRoot' => true
        ];

        $this->config = array_merge($defaultConfig, $config);

        return $this;
    }

    /**
     * Return configuration of converter
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Convert an XML DOMDocument or XML string to an array
     * A static facade for ease of use and backwards compatibility
     *
     * @param  DOMDocument|SimpleXMLElement|string $xml      The XML to convert to an array
     * @param  array                               $config   The configuration to use for the conversion
     *
     * @return array  An array representation of the input XML
     */
    public static function convert($xml, array $config = [])
    {
        $instance = new static($config);

        return $instance->convertFrom($xml);
    }

    /**
     * Convert input XML to an array
     *
     * @param  DOMDocument|SimpleXMLElement|string $inputXml The XML to convert to an array
     *
     * @return array  An array representation of the input XML
     */
    public function convertFrom($inputXml)
    {
        $this->loadXml($inputXml);

        // Convert the XML to an array, starting with the root node
        $rootNode     = $this->xml->documentElement;
        $rootValue    = $this->parseNode($rootNode);
        $rootNodeName = $rootNode->nodeName;

        $this->array[$rootNodeName] = $rootValue;

        // Add namespacing information to the root node
        if (!empty($this->namespaces) && $this->config['namespacesOnRoot']) {
            if (!isset($this->array[$rootNodeName][$this->config['attributesKey']])) {
                $this->array[$rootNodeName][$this->config['attributesKey']] = [];
            }

            foreach ($this->namespaces as $uri => $prefix) {
                if ($prefix) {
                    $prefix = self::ATTRIBUTE_NAMESPACE_SEPARATOR . $prefix;
                }

                $this->array[$rootNodeName][$this->config['attributesKey']][self::ATTRIBUTE_NAMESPACE . $prefix] = $uri;
            }
        }

        return $this;
    }

    /**
     * Export result as array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->array;
    }

    /**
     * Get result as json string
     *
     * @param  integer $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->array, $options);
    }

    /**
     * Load input into DOMDocument
     *
     * @param  DOMDocument|SimpleXMLElement|string $inputXml The XML to convert to an array
     *
     * @throws DOMException
     *
     * @return void
     */
    protected function loadXml($inputXml)
    {
        $this->xml = new DOMDocument($this->config['version'], $this->config['encoding']);

        if (is_string($inputXml)) {
            $parse = @$this->xml->loadXML($inputXml);

            if ($parse === false) {
                throw new DOMException('Error parsing XML string, input is not a well-formed XML string.');
            }
        } elseif ($inputXml instanceof SimpleXMLElement) {
            $this->xml->loadXML($inputXml->asXML());
        } elseif ($inputXml instanceof DOMDocument) {
            $this->xml = $inputXml;
        } else {
            throw new DOMException('The input XML must be one of types DOMDocument, SimpleXMLElement or well-formed XML string.');
        }
    }

    /**
     * Parse an XML DOMNode
     *
     * @param  DOMNode $node A single XML DOMNode
     *
     * @return mixed
     */
    protected function parseNode(DOMNode $node)
    {
        $output = [];
        $output = $this->collectNodeNamespaces($node, $output);

        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
                $output[$this->config['cdataKey']] = $this->normalizeTextContent($node->textContent);
                break;

            case XML_TEXT_NODE:
                $output = $this->normalizeTextContent($node->textContent);
                break;

            case XML_ELEMENT_NODE:
                $output = $this->parseChildNodes($node, $output);
                $output = $this->normalizeNodeValues($output);
                $output = $this->collectAttributes($node, $output);
                break;
        }

        return $output;
    }

    /**
     * Parse child nodes of DOMNode
     *
     * @param  DOMNode $node
     * @param  mixed   $output
     *
     * @return mxied
     */
    protected function parseChildNodes(DOMNode $node, $output)
    {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_CDATA_SECTION_NODE) {
                if (!is_array($output)) {
                    if (!empty($output)) {
                        $output = [$this->config['valueKey'] => $output];
                    } else {
                        $output = [];
                    }
                }

                $output[$this->config['cdataKey']] = $this->normalizeTextContent($child->textContent);
            } else {
                $value = $this->parseNode($child);

                if ($child->nodeType == XML_TEXT_NODE) {
                    if ($value != '') {
                        if (!empty($output)) {
                            $output[$this->config['valueKey']] = $value;
                        } else {
                            $output = $value;
                        }
                    }
                } elseif ($child->nodeType !== XML_COMMENT_NODE) {
                    $nodeName = $child->nodeName;

                    if (!isset($output[$nodeName])) {
                        $output[$nodeName] = [];
                    }

                    $output[$nodeName][] = $value;
                }
            }
        }

        return $output;
    }

    /**
     * Clean text content of text node
     *
     * @param  string $textContent
     *
     * @return string
     */
    protected function normalizeTextContent($textContent)
    {
        return trim(preg_replace([
            '/\n+\s+/',
            '/\r+\s+/',
            '/\n+\t+/',
            '/\r+\t+/'
        ], ' ', $textContent));
    }

    /**
     * Normalize values of node
     *
     * @param  mixed $values
     *
     * @return mixed
     */
    protected function normalizeNodeValues($values)
    {
        if (is_array($values)) {
            // if only one node of its kind, assign it directly instead if array($value);
            foreach ($values as $key => $value) {
                if (is_array($value) && count($value) === 1) {
                    $keyName = array_keys($value)[0];

                    if (is_numeric($keyName)) {
                        $values[$key] = $value[$keyName];
                    }
                }
            }

            if (empty($values)) {
                $values = '';
            }
        }

        return $values;
    }

    /**
     * Parse DOMNode to get its attributes
     *
     * @param  DOMNode $node
     * @param  mixed   $output
     *
     * @return mixed
     */
    protected function collectAttributes(DOMNode $node, $output)
    {
        if (!$node->attributes->length) {
            return $output;
        }

        $attributes = [];
        $namespaces = [];

        foreach ($node->attributes as $attributeName => $attributeNode) {
            $attributeName              = $attributeNode->nodeName;
            $attributes[$attributeName] = (string) $attributeNode->value;

            if ($attributeNode->namespaceURI) {
                $nsUri    = $attributeNode->namespaceURI;
                $nsPrefix = $attributeNode->lookupPrefix($nsUri);

                $namespaces = $this->collectNamespaces($attributeNode);
            }
        }

        // if its a leaf node, store the value in @value instead of directly it.
        if (!is_array($output)) {
            if (!empty($output)) {
                $output = [$this->config['valueKey'] => $output];
            } else {
                $output = [];
            }
        }

        foreach (array_merge($attributes, $namespaces) as $key => $value) {
            $output[$this->config['attributesKey']][$key] = $value;
        }

        return $output;
    }

    /**
     * Collect namespaces for special DOMNode
     *
     * @param  DOMNode $node
     * @param  array   $output
     *
     * @return array
     */
    protected function collectNodeNamespaces(DOMNode $node, array $output)
    {
        $namespaces = $this->collectNamespaces($node);

        if (!empty($namespaces)) {
            $output[$this->config['attributesKey']] = $namespaces;
        }

        return $output;
    }

    /**
     * Get the namespace of the supplied node, and add it to the list of known namespaces for this document
     *
     * @param  DOMNode $node
     *
     * @return mixed
     */
    protected function collectNamespaces(DOMNode $node)
    {
        $namespaces = [];

        if ($node->namespaceURI) {
            $nsUri    = $node->namespaceURI;
            $nsPrefix = $node->lookupPrefix($nsUri);

            if (!array_key_exists($nsUri, $this->namespaces)) {
                $this->namespaces[$nsUri] = $nsPrefix;

                if (!$this->config['namespacesOnRoot']) {
                    if ($nsPrefix) {
                        $nsPrefix = self::ATTRIBUTE_NAMESPACE_SEPARATOR . $nsPrefix;
                    }

                    $namespaces[self::ATTRIBUTE_NAMESPACE . $nsPrefix] = $nsUri;
                }
            }
        }

        return $namespaces;
    }
}
