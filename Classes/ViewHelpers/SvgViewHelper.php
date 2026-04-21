<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\ViewHelpers;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Font Awesome SVG ViewHelper.
 *
 * Usage examples:
 * <t3oodle:svg path="Icons/clock-regular.svg" />
 */
final class SvgViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * @var array<string, string>
     */
    protected static $cache = [];

    public function initializeArguments(): void
    {
        $this->registerArgument('path', 'string', 'SVG path, relative to Resources/Public/', true);
        $this->registerArgument('size', 'string', 'SVG size in pixel', false, '');
        $this->registerArgument('class', 'string', 'Optional class name(s)', false, '');
        $this->registerArgument('color', 'string', 'Optional color', false, '');
        $this->registerArgument('title', 'string', 'Optional title', false, '');
    }

    /**
     * Return array element by key.
     *
     * @throws \RuntimeException
     */
    public function render(): string
    {
        $request = $this->renderingContext->getRequest();
        $extensionName = $request->getControllerExtensionName();
        $uri = 'EXT:' . GeneralUtility::camelCaseToLowerCaseUnderscored($extensionName) . '/Resources/Public/' .
            $this->arguments['path'];
        $path = GeneralUtility::getFileAbsFileName($uri);

        if (!file_exists($path)) {
            throw new \RuntimeException(
                'Given SVG file "' . $this->arguments['path'] . '" not found!',
                1727787280
            );
        }

        // Prepare view helper arguments
        $size = (string)$this->arguments['size'];
        $color = (string)$this->arguments['color'];
        $title = (string)$this->arguments['title'];
        $class = 'svg-icon';
        if (!empty($this->arguments['class'])) {
            $class .= ' ' . $this->arguments['class'];
        }

        // If the same icon is requested a second time, use a reference to symbol instead
        if (array_key_exists($path, self::$cache)) {
            $id = self::$cache[$path];

            return self::buildSvgSymbolReference($id, $size, $class, $color, $title);
        }

        // Create and cache symbol
        return self::createSvgSymbol($path, $size, $class, $color, $title);
    }

    protected static function buildSvgSymbolReference(
        string $id,
        string $size,
        string $class,
        string $color,
        string $title
    ): string {
        if ($size !== '' && $size !== '0') {
            $size = ' width="' . $size . '" height="' . $size . '" ';
        }
        if ($color !== '' && $color !== '0') {
            $color = ' fill="' . $color . '"';
        }
        if ($title !== '' && $title !== '0') {
            $title = '<title>' . $title . '</title>';
        }

        return '<svg' . $size . $color . ' class="' . $class . '">'
            . $title . '<use xlink:href="#' . $id . '"></use>' .
            '</svg>';
    }

    protected static function createSvgSymbol(
        string $path,
        string $size,
        string $class,
        string $color,
        string $title
    ): string {
        $svgContents = file_get_contents($path);
        if ($svgContents === false) {
            throw new \InvalidArgumentException(
                'Given SVG file "' . $path . '" not found!',
                1776954293
            );
        }
        $svgDocument = new \DOMDocument();
        $loadedDocument = $svgDocument->loadXML($svgContents);

        if (!$loadedDocument instanceof \DOMDocument) {
            throw new \InvalidArgumentException(
                'Given SVG file "' . $path . '" not parseable!',
                1776954336
            );
        }

        // Used for caching and symbol identifier
        $id = 'svg-' . str_replace(['/', '_', ' ', '.'], '-', basename($path));

        // Create the symbol
        $symbolDocument = new \DOMDocument();
        $symbol = $symbolDocument->createElement('symbol');
        $symbol->setAttribute('id', $id);
        $symbol->setAttribute('viewBox', $loadedDocument->documentElement->getAttribute('viewBox'));
        $symbolDocument->appendChild($symbol);

        // Get paths of font awesome SVG
        foreach ($loadedDocument->documentElement->childNodes as $svgpath) {
            $iconPathsFragment = $symbolDocument->createDocumentFragment();
            $iconPathsFragment->appendXML($loadedDocument->saveXML($svgpath));
            $symbol->appendChild($iconPathsFragment);
        }

        // Prepare symbol output
        $result = '<svg class="d-none">';
        foreach ($symbolDocument->childNodes as $childNode) {
            $result .= $symbolDocument->saveXML($childNode);
        }
        $result .= '</svg>';

        // Directly append a reference to this symbol (which is never displayed)
        $result .= self::buildSvgSymbolReference($id, (string)$size, $class, $color, $title);

        // Add cache item and return the output
        self::$cache[$path] = $id;

        return $result;
    }
}
