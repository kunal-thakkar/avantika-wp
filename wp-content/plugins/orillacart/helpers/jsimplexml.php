<?php

/*
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class JSimpleXML extends BObject {

    /**
     * The XML parser
     *
     * @var resource
     */
    var $_parser = null;

    /**
     * The XML document
     *
     * @var string
     */
    var $_xml = '';

    /**
     * Document element
     *
     * @var object
     */
    var $document = null;

    /**
     * Current object depth
     *
     * @var array
     */
    var $_stack = array();

    /**
     * Constructor.
     *
     */
    public function __construct($options = null) {
        if (!function_exists('xml_parser_create')) {
            // TODO throw warning
            return false;
        }

        // Create the parser resource and make sure both versions of PHP autodetect the format.
        $this->_parser = xml_parser_create('');

        // Check parser resource
        xml_set_object($this->_parser, $this);
        xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, 0);
        if (is_array($options)) {
            foreach ($options as $option => $value) {
                xml_parser_set_option($this->_parser, $option, $value);
            }
        }

        // Set the handlers
        xml_set_element_handler($this->_parser, '_startElement', '_endElement');
        xml_set_character_data_handler($this->_parser, '_characterData');
    }

    /**
     * Interprets a string of XML into an object
     *
     * This function will take the well-formed xml string data and return an object of class
     * JSimpleXMLElement with properties containing the data held within the xml document.
     * If any errors occur, it returns FALSE.
     *
     * @param string  Well-formed xml string data
     * @param string  currently ignored
     * 
     * @return object JSimpleXMLElement
     */
    function loadString($string, $classname = null) {
        $this->_parse($string);
        return true;
    }

    /**
     * Interprets an XML file into an object
     *
     * This function will convert the well-formed XML document in the file specified by filename
     * to an object  of class JSimpleXMLElement. If any errors occur during file access or
     * interpretation, the function returns FALSE.
     *
     * @param string  Path to xml file containing a well-formed XML document
     * @param string  currently ignored
     * @return boolean True if successful, false if file empty
     */
    function loadFile($path, $classname = null) {
        //Check to see of the path exists
        if (!file_exists($path)) {
            return false;
        }

        //Get the XML document loaded into a variable
        $xml = trim(file_get_contents($path));
        if ($xml == '') {
            return false;
        } else {
            $this->_parse($xml);
            return true;
        }
    }

    /**
     * Get a JSimpleXMLElement object from a DOM node.
     *
     * This function takes a node of a DOM  document and makes it into a JSimpleXML node.
     * This new object can then be used as a native JSimpleXML element. If any errors occur,
     * it returns FALSE.
     *
     * @param string    DOM  document
     * @param string    currently ignored
     * @return object   JSimpleXMLElement
     */
    function importDOM($node, $classname = null) {
        return false;
    }

    /**
     * Get the parser
     *
     * @return resource XML parser resource handle
     */
    public function getParser() {
        return $this->_parser;
    }

    /**
     * Set the parser
     *
     * @param resource  XML parser resource handle
     */
    public function setParser($parser) {
        $this->_parser = $parser;
    }

    /**
     * Start parsing an XML document
     *
     * Parses an XML document. The handlers for the configured events are called as many times as necessary.
     *
     * @param $xml  string  data to parse
     */
    protected function _parse($data = '') {
        //Error handling
        if (!xml_parse($this->_parser, $data)) {
            $this->_handleError(
                    xml_get_error_code($this->_parser), xml_get_current_line_number($this->_parser), xml_get_current_column_number($this->_parser)
            );
        }

        //Free the parser
        xml_parser_free($this->_parser);
    }

    /**
     * Handles an XML parsing error
     *
     * @param int $code XML Error Code
     * @param int $line Line on which the error happened
     * @param int $col Column on which the error happened
     */
    protected function _handleError($code, $line, $col) {
        throw new Exception('XML Parsing Error at ' . $line . ':' . $col . '. Error ' . $code . ': ' . xml_error_string($code));
    }

    /**
     * Gets the reference to the current direct parent
     *
     * @return object
     */
    protected function _getStackLocation() {
        $return = '';
        foreach ($this->_stack as $stack) {
            $return .= $stack . '->';
        }

        return rtrim($return, '->');
    }

    /**
     * Handler function for the start of a tag
     *
     * @param resource $parser
     * @param string $name
     * @param array $attrs
     */
    protected function _startElement($parser, $name, $attrs = array()) {
        //  Check to see if tag is root-level
        $count = count($this->_stack);
        if ($count == 0) {
            // If so, set the document as the current tag
            $classname = get_class($this) . 'Element';
            $this->document = new $classname($name, $attrs);

            // And start out the stack with the document tag
            $this->_stack = array('document');
        }
        // If it isn't root level, use the stack to find the parent
        else {
            // Get the name which points to the current direct parent, relative to $this
            $parent = $this->_getStackLocation();

            // Add the child
            eval('$this->' . $parent . '->addChild($name, $attrs, ' . $count . ');');

            // Update the stack
            eval('$this->_stack[] = $name.\'[\'.(count($this->' . $parent . '->' . $name . ') - 1).\']\';');
        }
    }

    /**
     * Handler function for the end of a tag
     *
     * @param resource $parser
     * @param string $name
     */
    protected function _endElement($parser, $name) {
        //Update stack by removing the end value from it as the parent
        array_pop($this->_stack);
    }

    /**
     * Handler function for the character data within a tag
     *
     * @param resource $parser
     * @param string $data
     */
    protected function _characterData($parser, $data) {
        // Get the reference to the current parent object
        $tag = $this->_getStackLocation();

        // Assign data to it
        eval('$this->' . $tag . '->_data .= $data;');
    }

}

class JSimpleXMLElement extends BObject {

    /**
     * Array with the attributes of this XML element
     *
     * @var array
     */
    var $_attributes = array();

    /**
     * The name of the element
     *
     * @var string
     */
    var $_name = '';

    /**
     * The data the element contains
     *
     * @var string
     */
    var $_data = '';

    /**
     * Array of references to the objects of all direct children of this XML object
     *
     * @var array
     */
    var $_children = array();

    /**
     * The level of this XML element
     *
     * @var int
     */
    var $_level = 0;

    /**
     * Constructor, sets up all the default values
     *
     * @param string $name
     * @param array $attrs
     * @param int $parents
     * 
     * @return JSimpleXMLElement
     */
    function __construct($name, $attrs = array(), $level = 0) {
        //Make the keys of the attr array lower case, and store the value
        $this->_attributes = array_change_key_case($attrs, CASE_LOWER);

        //Make the name lower case and store the value
        $this->_name = strtolower($name);

        //Set the level
        $this->_level = $level;
    }

    /**
     * Get the name of the element
     *
     * @return string
     */
    public function name() {
        return $this->_name;
    }

    /**
     * Get the an attribute of the element
     *
     * @param string $attribute The name of the attribute
     *
     * @return mixed If an attribute is given will return the attribute if it exist.
     *              If no attribute is given will return the complete attributes array
     */
    public function attributes($attribute = null) {
        if (!isset($attribute)) {
            return $this->_attributes;
        }

        return isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : null;
    }

    /**
     * Get the data of the element
     *
     * @return string
     */
    public function data() {
        return $this->_data;
    }

    /**
     * Set the data of the element
     *
     * @param   string $data
     * @return string
     */
    public function setData($data) {
        $this->_data = $data;
    }

    /**
     * Get the children of the element
     *
     * @return array
     */
    public function children() {
        return $this->_children;
    }

    /**
     * Get the level of the element
     *
     * @return int
     */
    public function level() {
        return $this->_level;
    }

    /**
     * Adds an attribute to the element
     *
     * @param string $name
     * @param array  $attrs
     */
    function addAttribute($name, $value) {
        // Add the attribute to the element, override if it already exists
        $this->_attributes[$name] = $value;
    }

    /**
     * Removes an attribute from the element
     *
     * @param string $name
     */
    function removeAttribute($name) {
        unset($this->_attributes[$name]);
    }

    /**
     * Adds a direct child to the element
     *
     * @param string    $name
     * @param array     $attrs
     * @param int       $level
     * 
     * @return JSimpleXMLElement    The added child object
     */
    function addChild($name, $attrs = array(), $level = null) {
        //If there is no array already set for the tag name being added,
        //create an empty array for it
        if (!isset($this->$name)) {
            $this->$name = array();
        }

        // set the level if not already specified
        if ($level == null) {
            $level = ($this->_level + 1);
        }

        //Create the child object itself
        $classname = get_class($this);
        $child = new $classname($name, $attrs, $level);

        //Add the reference of it to the end of an array member named for the elements name
        $this->{$name}[] = &$child;

        //Add the reference to the children array member
        $this->_children[] = &$child;

        //return the new child
        return $child;
    }

    function removeChild(&$child) {
        $name = $child->name();
        for ($i = 0, $n = count($this->_children); $i < $n; $i++) {
            if ($this->_children[$i] == $child) {
                unset($this->_children[$i]);
            }
        }
        for ($i = 0, $n = count($this->{$name}); $i < $n; $i++) {
            if ($this->{$name}[$i] == $child) {
                unset($this->{$name}[$i]);
            }
        }
        $this->_children = array_values($this->_children);
        $this->{$name} = array_values($this->{$name});
        unset($child);
    }

    /**
     * Get an element in the document by / separated path
     *
     * @param   string  $path   The / separated path to the element
     * @return  object  JSimpleXMLElement
     */
    function getElementByPath($path) {
        $tmp = &$this;
        $parts = explode('/', trim($path, '/'));

        foreach ($parts as $node) {
            $found = false;
            foreach ($tmp->_children as $child) {
                if (strtoupper($child->_name) == strtoupper($node)) {
                    $tmp = &$child;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                break;
            }
        }

        if ($found) {
            return $tmp;
        }

        return false;
    }

    /**
     * Traverses the tree calling the $callback(JSimpleXMLElement
     * $this, mixed $args=array()) function with each JSimpleXMLElement.
     *
     * @param string $callback function name
     * @param array $args
     */
    function map($callback, $args = array()) {
        $callback($this, $args);
        // Map to all children
        if ($n = count($this->_children)) {
            for ($i = 0; $i < $n; $i++) {
                $this->_children[$i]->map($callback, $args);
            }
        }
    }

    /**
     * Return a well-formed XML string based on SimpleXML element
     *
     * @return string
     */
    function toString($whitespace = true) {
        // Start a new line, indent by the number indicated in $this->level, add a <, and add the name of the tag
        if ($whitespace) {
            $out = "\n" . str_repeat("\t", $this->_level) . '<' . $this->_name;
        } else {
            $out = '<' . $this->_name;
        }

        // For each attribute, add attr="value"
        foreach ($this->_attributes as $attr => $value) {
            $out .= ' ' . $attr . '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
        }

        // If there are no children and it contains no data, end it off with a />
        if (empty($this->_children) && empty($this->_data)) {
            $out .= " />";
        }
        // Otherwise...
        else {
            // If there are children
            if (!empty($this->_children)) {
                // Close off the start tag
                $out .= '>';

                // For each child, call the asXML function (this will ensure that all children are added recursively)
                foreach ($this->_children as $child)
                    $out .= $child->toString($whitespace);

                // Add the newline and indentation to go along with the close tag
                if ($whitespace) {
                    $out .= "\n" . str_repeat("\t", $this->_level);
                }
            }

            // If there is data, close off the start tag and add the data
            elseif (!empty($this->_data))
                $out .= '>' . htmlspecialchars($this->_data, ENT_COMPAT, 'UTF-8');

            // Add the end tag
            $out .= '</' . $this->_name . '>';
        }

        //Return the final output
        return $out;
    }

}
