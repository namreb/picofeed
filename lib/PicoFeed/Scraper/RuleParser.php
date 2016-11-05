<?php

namespace PicoFeed\Scraper;

use DOMXPath;
use PicoFeed\Parser\XmlParser;

/**
 * Rule Parser.
 *
 * @author  Frederic Guillot
 */
class RuleParser implements ParserInterface
{
    private $dom;
    private $xpath;
    private $rules = array();

    /**
     * Constructor.
     *
     * @param string $html
     * @param array  $rules
     */
    public function __construct($html, array $rules)
    {
        $this->rules = $rules;
        $this->dom = XmlParser::getHtmlDocument('<?xml version="1.0" encoding="UTF-8">'.$html);
        $this->xpath = new DOMXPath($this->dom);
    }

    /**
     * Get the relevant content with predefined rules.
     *
     * @return string
     */
    public function execute()
    {
        $this->stripTags();

        return $this->findContent();
    }

    /**
     * Remove HTML tags.
     */
    public function stripTags()
    {
        if (isset($this->rules['strip']) && is_array($this->rules['strip'])) {
            foreach ($this->rules['strip'] as $pattern) {
                $nodes = $this->xpath->query($pattern);

                if ($nodes !== false && $nodes->length > 0) {
                    foreach ($nodes as $node) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }
        }
    }

    /**
     * Fetch content based on Xpath rules.
     */
    public function findContent()
    {
        $content = '';

        if (isset($this->rules['body']) && is_array($this->rules['body'])) {
            foreach ($this->rules['body'] as $pattern) {
                $nodes = $this->xpath->query($pattern);

                if ($nodes !== false && $nodes->length > 0) {
                    foreach ($nodes as $node) {
                        $content .= $this->dom->saveXML($node);
                    }
                }
            }
        }

        return $content;
    }

    /**
     * Fetch next link based on Xpath rules.
     */
    public function findNextLink()
    {
        $content = '';

        if (isset($this->rules['next_page']) && is_array($this->rules['next_page'])) {
            foreach ($this->rules['next_page'] as $pattern) {

                //echo $pattern;
                //echo "hallo welt<br />";
                $nodes = $this->xpath->query($pattern);
                //var_dump($nodes);

                if ($nodes !== false && $nodes->length > 0) {
                    foreach ($nodes as $node) {
                        $content .= $this->dom->saveXML($node);
                    }
                }
            }
        }

        $link = $this->extractLink($content);
        return $link;
    }

    private function extractLink($a){
        $pattern = '/href="(.*?)"/';
        preg_match($pattern, $a, $url);
        return $url[1];
    }
}
