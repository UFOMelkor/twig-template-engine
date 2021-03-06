<?php

/*
 * This file is part of the memio/twig-template-engine package.
 *
 * (c) Loïc Faugeron <faugeron.loic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Memio\TwigTemplateEngine\TwigExtension;

use Memio\Model\FullyQualifiedName;
use Memio\Model\Type as ModelType;
use Memio\TwigTemplateEngine\TwigExtension\Line\Line;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class Whitespace extends Twig_Extension
{
    /**
     * @var Line
     */
    private $line;

    /**
     * @param Line $line
     */
    public function __construct(Line $line)
    {
        $this->line = $line;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('needs_line_after', array($this->line, 'needsLineAfter')),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('align', array($this, 'align')),
            new Twig_SimpleFilter('indent', array($this, 'indent')),
        );
    }

    /**
     * @param string $current
     * @param array  $collection
     *
     * @return string
     */
    public function align($current, $collection)
    {
        $elementLength = strlen($current);
        $longestElement = $elementLength;
        foreach ($collection as $element) {
            if ('Memio\Model\Phpdoc\ParameterTag' === get_class($element)) {
                $type = $element->getType();
                $modelType = new ModelType($element->getType());
                if ($modelType->isObject()) {
                    $fullyQualifiedName = new FullyQualifiedName($type);
                    $type = $fullyQualifiedName->getName();
                }
                $longestElement = max($longestElement, strlen($type));
            }
        }

        return $current.str_repeat(' ', $longestElement - $elementLength);
    }

    /**
     * @param string $text
     * @param int    $level
     * @param string $type
     *
     * @return string
     */
    public function indent($text, $level = 1, $type = 'code')
    {
        $lines = explode("\n", $text);
        $indentedLines = array();
        if ('code' === $type) {
            foreach ($lines as $line) {
                $indentedLines[] = '    '.$line;
            }
        }
        if ('phpdoc' === $type) {
            foreach ($lines as $line) {
                $indent = ' *';
                if (!empty($line)) {
                    $indent .= ' ';
                }
                $indentedLines[] = $indent.$line;
            }
        }

        return implode("\n", $indentedLines);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'whitespace';
    }
}
