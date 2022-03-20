<?php

namespace Jackiedo\XmlArray;

use DOMDocument;
use DOMElement;
use DOMException;
use Exception;

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

        if (array_key_exists('standalone', $this->config) && is_bool($xmlStandalone = $this->config['standalone'])) {
            if (property_exists($this->xml, 'xmlStandalone')) {
                $this->xml->xmlStandalone = $xmlStandalone;
            } else {
                $this->xml->standalone = $xmlStandalone;
            }
        }
    }

    /**
     * Set configuration for converter.
     *
     * @param array $config The configuration to use for conversion
     *
     * @throws Exception
     *
     * @return $this
     */
    public function setConfig(array $config = [])
    {
        $defaultConfig = [
            'version'       => '1.0',
            'encoding'      => 'UTF-8',
            'standalone'    => null,
            'rootElement'   => null,
            'attributesKey' => '@attributes',
            'cdataKey'      => '@cdata',
            'valueKey'      => '@value',
            'keyFixer'      => true,
        ];

        if (array_key_exists('keyFixer', $config)) {
            $keyFixer = $config['keyFixer'];

            if (!is_bool($keyFixer) && !is_string($keyFixer) && !is_numeric($keyFixer) && !is_callable($keyFixer)) {
                throw new Exception('Invalid the `keyFixer` setting. Only accept `bool|string|numeric|callable` type.');
            }
        }

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
        $this->ensureValidTagName($nodeName, 'tag');

        $node = $this->xml->createElement($nodeName);

        if (is_array($data)) {
            $this->parseArray($node, $data);
        } else {
            $node->appendChild($this->xml->createTextNode($this->normalizeValue($data)));
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
            $key = $this->normalizeKey($key);

            $this->ensureValidTagName($key, 'tag');

            if ($this->isSequentialArray($value)) {
                // MORE THAN ONE NODE OF ITS KIND;
                // if the new array is sequential array, means it is array of nodes of the same kind
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
                $key = $this->normalizeKey($key);

                $this->ensureValidTagName($key, 'attribute');

                $node->setAttribute($key, $this->normalizeValue($value));
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
            $node->appendChild($this->xml->createTextNode($this->normalizeValue($array[$valueKey])));

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
            $node->appendChild($this->xml->createCDATASection($this->normalizeValue($array[$cdataKey])));

            unset($array[$cdataKey]);
        }

        return $array;
    }

    /**
     * Normalize invalid characters in the key name.
     *
     * @param string $key The key name need to fix
     *
     * @return string
     */
    protected function normalizeKey($key)
    {
        $keyFixer = $this->config['keyFixer'];

        if (is_bool($keyFixer)) {
            if (!$keyFixer) {
                return $key;
            }

            $keyFixer = '_';
        }

        if (is_string($keyFixer) || is_numeric($keyFixer)) {
            return preg_replace(['/[^\w\:\-\.]/', '/^[^a-zA-Z_]/', '/\:+$/'], [$keyFixer, '_${0}', $keyFixer], $key);
        }

        return call_user_func_array($keyFixer, [$key]);
    }

    /**
     * Get string representation of values.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function normalizeValue($value)
    {
        $value = true  === $value ? 'true' : $value;
        $value = false === $value ? 'false' : $value;
        $value = null  === $value ? '' : $value;

        return (string) $value;
    }

    /**
     * Throw DOMException if a string is invalid for well-formed name.
     *
     * @param string $name
     * @param string $type
     *
     * @return void
     */
    protected function ensureValidTagName($name, $type)
    {
        if (!$this->isValidName($name)) {
            throw new DOMException('Invalid character in the ' . $type . ' name being generated. Failed at [' . $name . '].');
        }
    }

    /**
     * Checks if the key name contains valid characters to be the well-formed tag or attribute name.
     *
     * A valid key is one that conforms to the pattern /^[a-zA-Z_][\w\:\-\.]*$(?<!\:)/
     *
     * @see: http://www.w3.org/TR/xml/#sec-common-syn
     *
     * @param  string
     * @param mixed $name
     *
     * @return bool
     */
    protected function isValidName($name)
    {
        $pattern = '/^([a-zA-Z_][\w\-\.]*\:)?[a-zA-Z_][\w\-\.]*$(?<!\:)/';

        return 1 === preg_match($pattern, $name, $matches);
    }

    /**
     * Determine if the input is a sequential array.
     *
     * @param mixed $array
     *
     * @return bool
     */
    protected function isSequentialArray($array)
    {
        if (!is_array($array)) {
            return false;
        }

        $totalCount = count($array);

        if ($totalCount <= 0) {
            return true;
        }

        return array_keys($array) === range(0, $totalCount - 1);
    }
}
