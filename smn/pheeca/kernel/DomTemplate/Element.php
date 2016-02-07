<?php
namespace smn\pheeca\kernel\DomTemplate;

use smn\pheeca\kernel\DomTemplate\Query;
use smn\pheeca\kernel\DomTemplate\Collect;

class Element {

    protected $_elementObject;
    protected $_parentElement;
    protected $_doc;

    /**
     * 
     * @param type $name
     * @param type $value
     * @param type $namespaceURI
     * @param type $parentDocument
     * @param type $children
     * @param type $attributes
     * @param type $classes
     * @param type $styles
     * @return \self
     */
    public static function createElement($name, $value = null, $namespaceURI = null, $parentDocument = null, $children = null, $attributes = null, $classes = null, $styles = null) {
        return new self($name, $value, $namespaceURI, $parentDocument, $children, $attributes, $classes, $styles);
    }

    /**
     * 
     * @param type $element
     * @return \render_Element
     */
    public static function createRenderElement($element) {
        $self = new static($element);
        return $self;
    }

    /**
     * 
     * @global type $renderedPage
     * @param type $name
     * @param type $value
     * @param type $namespaceURI
     * @param DOMDocument $parentDocument
     * @param type $children
     * @param type $attributes
     * @param type $classes
     * @param string $styles
     * @return \render_Element
     */
    public function __construct($name, $value = null, $namespaceURI = null, $parentDocument = null, $children = null, $attributes = null, $classes = null, $styles = null) {

        // se non c'è un elemento padre, creo il documento e lo associo all'elemento
        // altriment il lo prelevo
        // parentDocument null | DomElement | DomDocument | render_Element | String | DomNodeList
        // se name è un domelement, creo una classe con già l'elelmento e i parametri fondamentali e mi fermo. 
        // equivale ad una new render_Element con tutti i parametri

        if ($name instanceof self) {
            $element = $name->getElement();
            $name = $element;
        }

        if ($name instanceof \DOMElement) {
            $this->setElement($name);
            $this->setDocument($name->ownerDocument);
            $this->setParent($name->parentNode);
            return $this;
        }

        global $renderedPage;

        if (!is_null($parentDocument)) {
            if ($parentDocument instanceof \DOMElement) {
                // se è un domelement, il doc diventa il documento dell'elemento inviato
                // il parent diventa il padre
                $this->_doc = $parentDocument->ownerDocument;
                $this->_parentElement = $parentDocument;

                $this->_elementObject = $this->getDocument()->createElement($name, $value);
                $this->_parentElement->appendChild($this->_elementObject);
            }
            if ($parentDocument instanceof \DOMDocument) {
                $this->_doc = $parentDocument;

                $this->_elementObject = $this->getDocument()->createElement($name, $value);
                $this->_doc->appendChild($this->_elementObject);
            }
            if ($parentDocument instanceof self) {
                $this->_doc = $parentDocument->getDocument();
                $this->_parentElement = $parentDocument->getElement();

                $this->_elementObject = $this->getDocument()->createElement($name, $value);
                $this->_parentElement->appendChild($this->_elementObject);
            }

            if (is_string($parentDocument)) {
                // se è una stringa, la trasformo in xpath per il documento generale
                $csstoxpath = Query::css2xpath($parentDocument);
                $xPathClass = new \DOMXPath($renderedPage);

                $nodeList = $xPathClass->query($csstoxpath);
                if ($nodeList->length == 0) {
                    return; // capire cosa fare
                }
                $element = $nodeList->item(0); // solo al primo elemento
                //$selfClass = new render_Element($name, $value, $namespaceURI, $element, $children, $attributes, $classes, $styles);
                $this->_doc = $element->ownerDocument;
                $this->_parentElement = $element;
                if (!is_null($namespaceURI)) {
                    $this->_elementObject = $this->getDocument()->createElementNS($namespaceURI, $name, $value);
                    $this->_parentElement->appendChild($this->_elementObject);
                } else {
                    $this->_elementObject = $this->getDocument()->createElement($name, $value);
                    $this->_parentElement->appendChild($this->_elementObject);
                }
            }
        } else {
            //$docImplementation = new DOMImplementation();
            //$dtd = $docImplementation->createDocumentType('html'); // html5
            //$doc = $docImplementation->createDocument(null, 'html', $dtd); // html 5
            //$this->_doc = $doc;
            $this->_doc = $renderedPage;
            $this->_parentElement = $renderedPage->documentElement; // se il parent non esiste, gli do il primo elemento del documento creato
            $this->_elementObject = $this->getDocument()->createElement($name, $value);
            $this->_parentElement->appendChild($this->_elementObject);
        }

        $this->setValue($value);


        if (!is_null($children)) {
            if (is_array($children)) {
                foreach ($children as $child) {
                    $this->addChildren($child);
                }
            } else {
                $this->addChildren($children);
            }
        }

        // aggiungo gli attributi
        if (is_array($attributes)) {
            foreach ($attributes as $attrName => $attrValue) {
                $this->setAttribute($attrName, $attrValue);
            }
        }

        // aggiungo le classi
        if (!is_null($classes)) {
            if (is_array($classes)) {
                $class = implode(' ', $classes);
            } else {
                $class = $classes;
            }
            $this->setAttribute('class', $class);
        }

        // aggiungo gli stili
        if (!is_null($styles)) {
            if (is_array($styles)) {
                $_styles = array();
                foreach ($styles as $styleName => $styleValue) {
                    $styles[] = $styleName . ': ' . $styleValue;
                }
                $styles = implode(';', $styles);
                $this->setAttribute('styles', $styles);
            } else {
                $this->setAttribute('styles', $styles);
            }
        }

        return $this;
    }

    // verifica se esiste un valore in $data
    // se non esiste, è falso
    // se esiste
    //   verifica se value è null, se value è null restituisce true
    // se value esiste, verifica se $data->name == $value, e restituisce true o false a seconda
    // alla fine verifica solo se esiste, e se passo value se il valore corrisponde
    private function _isValue(&$data, $name, $value = '') {
        if (!isset($data->$name)) {
            return false; // se non esiste la chiave nell'array, restituisco false
        } else if (!is_null($value)) {
            // altrimenti se non è $vaue non è null verifico se il valore è uguale al valore che ho nell'array
            if ($data->$name == $value) {
                return true;
            }
            return false;
        }
        return true;
    }

    // restituisce vero o falso solo se $name è settato
    private function _is(&$data, $name) {
        if (isset($data->$name)) {
            return true;
        }
        return false;
    }

    // impostare attributo
    // cancellare attributo
    // verifica se ha attributo
    // verifica se l'attributo è uguale ad

    public function setAttribute($name, $value = '', $override = true) {
        if ($override == true) {
            $this->_elementObject->setAttribute($name, $value);
        } else if (!$this->hasAttribute($name)) {
            $this->_elementObject->setAttribute($name, $value);
        }
        return $this;
    }

    public function setAttributes($attributes = array()) {
        foreach ($attributes as $attrName => $attrValue) {
            $this->setAttribute($attrName, $attrValue);
        }
    }

    public function hasAttribute($name) {
        return $this->_elementObject->hasAttribute($name);
    }

    public function getAttribute($name) {
        return $this->_elementObject->getAttribute($name);
    }

    // cancella un valore solo se è uguale a $value. se $value è null cancella a prescindere
    public function delAttribute($name) {
        if ($this->_elementObject->hasAttribute($name)) {
            $this->_elementObject->removeAttribute($name);
        }
        return $this;
    }

    public function destroyElement() {
        $this->_parentElement->removeChild($this->_elementObject);
        //$this->_doc->removeChild($this->_elementObject);
    }

    public function addChildren($child) {
        if (is_array($child)) {
            foreach ($child as $element) {
                if ($element instanceof \DOMElement) {
                    $this->_elementObject->appendChild($element);
                }
                if ($element instanceof self) {
                    $this->_elementObject->appendChild($element->getElement());
                }
            }
        } else {

            if (($child instanceof \DOMElement) || ($child instanceof \DOMText) || ($child instanceof \DOMCdataSection)) {
                $this->_elementObject->appendChild($child);
            }
            if ($child instanceof self) {
                $this->_elementObject->appendChild($child->getElement());
            }
        }
        return $this;
    }

    public function insertAfter($child) {
        if (!is_array($child)) {
            $p = $child;
            $child = array($p);
        }
        foreach ($child as $element) {
            if ($element instanceof self) {
                $element = $element->getElement();
            }
            $parent = $this->getParent();
            $parent->insertBefore($element, $this->getElement()->nextSibling);
        }
    }

    public function insertBefore($child, $after_value = false) {
        if (!is_array($child)) {
            $p = $child;
            $child = array($p);
        }
        foreach ($child as $element) {
            if ($element instanceof self) {
                $element = $element->getElement();
            }
            $parent = $this->getParent();
            if ($after_value == false) {
                $parent->insertBefore($element, $this->getElement());
            } else {
                $parent->insertBefore($element, $this->getElement()->previousSibling);
            }
        }
    }

    /**
     * 
     * @return DOMElement
     */
    public function getParent() {
        return $this->_parentElement;
    }

    /**
     * 
     * @return \render_Collect
     */
    public function getChildren() {
        $children = $this->_elementObject->childNodes;
        $i = 0;
        $elements = array();
        while ($i < $children->length) {
            if ($children->item($i) instanceof \DOMElement) {
                $elements[] = $children->item($i);
            }
            $i++;
        }
        return new Collect($elements);
    }

    /**
     * 
     * @return \render_Collect
     */
    public function getAllChildren() {

        $pathofelement = $this->_elementObject->getNodePath();
        $xpath = new \DOMXPath($this->_doc);
        $i = 0;
        $elements = array();
        $lists = $xpath->query($pathofelement . '//*');
        while ($i < $lists->length) {
            if ($lists->item($i) instanceof \DOMElement) {
                $elements[] = $lists->item($i);
            }
            $i++;
        }
        return new render_Collect($elements);
    }

    public function setParent($parent) {
        $this->_parentElement = $parent;
        return $this;
    }

    /**
     * 
     * @return DOMElement
     */
    public function getElement() {
        return $this->_elementObject;
    }

    public function setElement($element) {
        $this->_elementObject = $element;
        return $this;
    }

    public function setValue($value = null) {
        if (!is_null($value)) {
            $this->_elementObject->nodeValue = $value;
            //$this->_elementObject->appendChild(new DOMText($value));
        }
    }

    public function getDocument() {
        return $this->_doc;
    }

    public function setDocument($document) {
        $this->_doc = $document;
        return $this;
    }

    public function setId($id) {
        $this->_elementObject->setAttribute('id', $id);
        return $this;
    }

    public function getId() {
        return $this->_elementObject->getAttribute('id');
    }

    public function setName($name) {
        $this->_elementObject->setAttribute('name', $name);
        return $this;
    }

    public function getName() {
        return $this->_elementObject->getAttribute('name');
    }

    public function getTagName() {
        return $this->_elementObject->nodeName;
    }

    public function getValue() {
        return $this->_elementObject->nodeValue;
    }

    public function addClass($name) {
        $classes = $this->_elementObject->getAttribute('class');
        $explode = explode(' ', $classes);
        $explode[] = $name;
        $this->_elementObject->setAttribute('class', trim(implode(' ', $explode)));
        return $this;
    }

    public function delClass($name) {
        $classes = $this->_elementObject->getAttribute('class');
        $explode = explode(' ', $classes);
        if (!array_search($name, $explode) === false) {
            $key = array_search($name, $explode);
            unset($explode[$key]);
        }
        $this->_elementObject->setAttribute('class', trim(implode(' ', $explode)));
        return $this;
    }

    public function addStyle($name, $value = '') {
        $styles = $this->getAttribute('style');
        $_s = array();
        if ($styles) {
            $explode = explode(';', $styles);
            foreach ($explode as $style) {
                $_ex = explode(':', $style);
                $styleName = $_ex[0];
                $styleValue = $_ex[1];
                $_s[] = $styleName . ': ' . trim($styleValue);
            }
            $_s[] = $name . ': ' . trim($value);
        } else {
            $_s[] = $name . ': ' . trim($value);
        }
        $this->_elementObject->setAttribute('style', implode(';', $_s));
    }

    public function delStyle($name) {
        $styles = $this->getAttribute('style');
        $explode = explode(';', $styles);
        $_s = array();
        if ($styles) {
            foreach ($explode as $style) {
                $_ex = explode(':', $style);
                $styleName = $_ex[0];
                $styleValue = $_ex[1];
                if ($name != $styleName) {
                    $_s[] = $styleName . ': ' . trim($styleValue);
                }
            }
            $this->_elementObject->setAttribute('style', implode(':', $_s));
        } else {
            $this->_elementObject->delAttribute('style');
        }
        return $this;
    }

    public function __toString() {
        $document = $this->getDocument();
        //$document->formatOutput = !MINIFY_PAGE; // se MINIFY_PAGE è configurato a true, formatOutput diventa false, e viceversa
        return $document->saveHTML();
    }

    public function addComment($text = '') {
        $comment = new \DOMComment($text);
        $this->_elementObject->appendChild($comment);
    }

    public static function createElementFromText($text = null) {

        if (is_null($text)) {
            return;
        }
        global $renderedPage;
        $return = false;

        $document = DOMDocument::loadHTML($text);
        $document->removeChild($document->doctype);

        $nodeList = $document->documentElement->firstChild->childNodes; // remove html e body tag

        if ($nodeList->length == 0) {
            return false;
        }
        if ($nodeList->length == 1) {
            $newNode = $renderedPage->importNode($nodeList->item(0), true);
            return new render_Element($newNode);
        }
        if ($nodeList->length > 1) {
            $return = array();
            $i = 0;
            while ($i < $nodeList->length) {
                $element = $nodeList->item($i);
                $newNode = $renderedPage->importNode($element, true); // change document 
                $return[] = new render_Element($newNode);
                $i++;
            }
        }
        return $return;
    }

}
