<?php
/**
 * Generated stub declarations for WP-CLI
 * @see https://wp-cli.org/
 * @see https://github.com/php-stubs/wp-cli-stubs
 */

namespace Gettext;

/**
 * Interface used by all translators.
 */
interface TranslatorInterface
{
    /**
     * Register this translator as global, to use with the gettext functions __(), p__(), etc.
     * Returns the previous translator if exists.
     *
     * @return TranslatorInterface|null
     */
    public function register();
    /**
     * Noop, marks the string for translation but returns it unchanged.
     *
     * @param string $original
     *
     * @return string
     */
    public function noop($original);
    /**
     * Gets a translation using the original string.
     *
     * @param string $original
     *
     * @return string
     */
    public function gettext($original);
    /**
     * Gets a translation checking the plural form.
     *
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    public function ngettext($original, $plural, $value);
    /**
     * Gets a translation checking the domain and the plural form.
     *
     * @param string $domain
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    public function dngettext($domain, $original, $plural, $value);
    /**
     * Gets a translation checking the context and the plural form.
     *
     * @param string $context
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    public function npgettext($context, $original, $plural, $value);
    /**
     * Gets a translation checking the context.
     *
     * @param string $context
     * @param string $original
     *
     * @return string
     */
    public function pgettext($context, $original);
    /**
     * Gets a translation checking the domain.
     *
     * @param string $domain
     * @param string $original
     *
     * @return string
     */
    public function dgettext($domain, $original);
    /**
     * Gets a translation checking the domain and context.
     *
     * @param string $domain
     * @param string $context
     * @param string $original
     *
     * @return string
     */
    public function dpgettext($domain, $context, $original);
    /**
     * Gets a translation checking the domain, the context and the plural form.
     *
     * @param string $domain
     * @param string $context
     * @param string $original
     * @param string $plural
     * @param string $value
     */
    public function dnpgettext($domain, $context, $original, $plural, $value);
}
abstract class BaseTranslator implements \Gettext\TranslatorInterface
{
    /** @var TranslatorInterface */
    public static $current;
    /**
     * @see TranslatorInterface
     */
    public function noop($original)
    {
    }
    /**
     * @see TranslatorInterface
     */
    public function register()
    {
    }
    /**
     * Include the gettext functions
     */
    public static function includeFunctions()
    {
    }
}
namespace Gettext\Extractors;

interface ExtractorInterface
{
    /**
     * Extract the translations from a file.
     *
     * @param array|string $file         A path of a file or files
     * @param Translations $translations The translations instance to append the new translations.
     * @param array        $options
     */
    public static function fromFile($file, \Gettext\Translations $translations, array $options = []);
    /**
     * Parses a string and append the translations found in the Translations instance.
     *
     * @param string       $string
     * @param Translations $translations
     * @param array        $options
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = []);
}
abstract class Extractor implements \Gettext\Extractors\ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public static function fromFile($file, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Checks and returns all files.
     *
     * @param string|array $file The file/s
     *
     * @return array The file paths
     */
    protected static function getFiles($file)
    {
    }
    /**
     * Reads and returns the content of a file.
     *
     * @param string $file
     *
     * @return string
     */
    protected static function readFile($file)
    {
    }
}
/**
 * Class to get gettext strings from blade.php files returning arrays.
 */
class Blade extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
}
namespace Gettext\Utils;

/**
 * Trait to provide the functionality of extracting headers.
 */
trait HeadersExtractorTrait
{
    /**
     * Add the headers found to the translations instance.
     *
     * @param string       $headers
     * @param Translations $translations
     *
     * @return array
     */
    protected static function extractHeaders($headers, \Gettext\Translations $translations)
    {
    }
    /**
     * Checks if it is a header definition line. Useful for distguishing between header definitions
     * and possible continuations of a header entry.
     *
     * @param string $line Line to parse
     *
     * @return bool
     */
    protected static function isHeaderDefinition($line)
    {
    }
    /**
     * Normalize a string.
     *
     * @param string $value
     *
     * @return string
     */
    public static function convertString($value)
    {
    }
}
/*
 * Trait to provide the functionality of read/write csv.
 */
trait CsvTrait
{
    protected static $csvEscapeChar;
    /**
     * Check whether support the escape_char argument to fgetcsv/fputcsv or not
     *
     * @return bool
     */
    protected static function supportsCsvEscapeChar()
    {
    }
    /**
     * @param resource $handle
     * @param array $options
     *
     * @return array
     */
    protected static function fgetcsv($handle, $options)
    {
    }
    /**
     * @param resource $handle
     * @param array $fields
     * @param array $options
     *
     * @return bool|int
     */
    protected static function fputcsv($handle, $fields, $options)
    {
    }
}
namespace Gettext\Extractors;

/**
 * Class to get gettext strings from csv.
 */
class Csv extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    use \Gettext\Utils\HeadersExtractorTrait;
    use \Gettext\Utils\CsvTrait;
    public static $options = ['delimiter' => ",", 'enclosure' => '"', 'escape_char' => "\\"];
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
}
/**
 * Class to get gettext strings from csv.
 */
class CsvDictionary extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    use \Gettext\Utils\HeadersExtractorTrait;
    use \Gettext\Utils\CsvTrait;
    public static $options = ['delimiter' => ",", 'enclosure' => '"', 'escape_char' => "\\"];
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
}
interface ExtractorMultiInterface
{
    /**
     * Parses a string and append the translations found in the Translations instance.
     * Allows scanning for multiple domains at a time (each Translation has to have a different domain)
     *
     * @param string $string
     * @param Translations[] $translations
     * @param array $options
     */
    public static function fromStringMultiple($string, array $translations, array $options = []);
    /**
     * Parses a string and append the translations found in the Translations instance.
     * Allows scanning for multiple domains at a time (each Translation has to have a different domain)
     *
     * @param $file
     * @param Translations[] $translations
     * @param array $options
     */
    public static function fromFileMultiple($file, array $translations, array $options = []);
}
/**
 * Class to get gettext strings from json files.
 */
class Jed extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Handle an array of translations and append to the Translations instance.
     *
     * @param array        $content
     * @param Translations $translations
     */
    public static function extract(array $content, \Gettext\Translations $translations)
    {
    }
}
/**
 * Class to get gettext strings from javascript files.
 */
class JsCode extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface, \Gettext\Extractors\ExtractorMultiInterface
{
    public static $options = ['constants' => [], 'functions' => ['gettext' => 'gettext', '__' => 'gettext', 'ngettext' => 'ngettext', 'n__' => 'ngettext', 'pgettext' => 'pgettext', 'p__' => 'pgettext', 'dgettext' => 'dgettext', 'd__' => 'dgettext', 'dngettext' => 'dngettext', 'dn__' => 'dngettext', 'dpgettext' => 'dpgettext', 'dp__' => 'dpgettext', 'npgettext' => 'npgettext', 'np__' => 'npgettext', 'dnpgettext' => 'dnpgettext', 'dnp__' => 'dnpgettext', 'noop' => 'noop', 'noop__' => 'noop']];
    protected static $functionsScannerClass = 'Gettext\Utils\JsFunctionsScanner';
    /**
     * @inheritdoc
     * @throws Exception
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * @inheritDoc
     * @throws Exception
     */
    public static function fromStringMultiple($string, array $translations, array $options = [])
    {
    }
    /**
     * @inheritDoc
     * @throws Exception
     */
    public static function fromFileMultiple($file, array $translations, array $options = [])
    {
    }
}
namespace Gettext\Utils;

/**
 * Trait to provide the functionality of extracting headers.
 */
trait HeadersGeneratorTrait
{
    /**
     * Returns the headers as a string.
     *
     * @param Translations $translations
     *
     * @return string
     */
    protected static function generateHeaders(\Gettext\Translations $translations)
    {
    }
}
/**
 * Trait used by all generators that exports the translations to multidimensional arrays
 * (context => [original => [translation, plural1, pluraln...]]).
 */
trait MultidimensionalArrayTrait
{
    use \Gettext\Utils\HeadersGeneratorTrait;
    use \Gettext\Utils\HeadersExtractorTrait;
    /**
     * Returns a multidimensional array.
     *
     * @param Translations $translations
     * @param bool         $includeHeaders
     * @param bool         $forceArray
     *
     * @return array
     */
    protected static function toArray(\Gettext\Translations $translations, $includeHeaders, $forceArray = false)
    {
    }
    /**
     * Extract the entries from a multidimensional array.
     *
     * @param array        $messages
     * @param Translations $translations
     */
    protected static function fromArray(array $messages, \Gettext\Translations $translations)
    {
    }
}
namespace Gettext\Extractors;

/**
 * Class to get gettext strings from json.
 */
class Json extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    use \Gettext\Utils\MultidimensionalArrayTrait;
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
}
namespace Gettext\Utils;

/**
 * Trait used by all generators that exports the translations to plain dictionary (original => singular-translation).
 */
trait DictionaryTrait
{
    use \Gettext\Utils\HeadersGeneratorTrait;
    use \Gettext\Utils\HeadersExtractorTrait;
    /**
     * Returns a plain dictionary with the format [original => translation].
     *
     * @param Translations $translations
     * @param bool         $includeHeaders
     *
     * @return array
     */
    protected static function toArray(\Gettext\Translations $translations, $includeHeaders)
    {
    }
    /**
     * Extract the entries from a dictionary.
     *
     * @param array        $messages
     * @param Translations $translations
     */
    protected static function fromArray(array $messages, \Gettext\Translations $translations)
    {
    }
}
namespace Gettext\Extractors;

/**
 * Class to get gettext strings from plain json.
 */
class JsonDictionary extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    use \Gettext\Utils\DictionaryTrait;
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
}
/**
 * Class to get gettext strings from .mo files.
 */
class Mo extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    const MAGIC1 = -1794895138;
    const MAGIC2 = -569244523;
    const MAGIC3 = 2500072158;
    protected static $stringReaderClass = 'Gettext\Utils\StringReader';
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * @param StringReader $stream
     * @param string       $byteOrder
     */
    protected static function readInt(\Gettext\Utils\StringReader $stream, $byteOrder)
    {
    }
    /**
     * @param StringReader $stream
     * @param string       $byteOrder
     * @param int          $count
     */
    protected static function readIntArray(\Gettext\Utils\StringReader $stream, $byteOrder, $count)
    {
    }
}
/**
 * Class to get gettext strings from php files returning arrays.
 */
class PhpArray extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    use \Gettext\Utils\MultidimensionalArrayTrait;
    /**
     * {@inheritdoc}
     */
    public static function fromFile($file, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
}
/**
 * Class to get gettext strings from php files returning arrays.
 */
class PhpCode extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface, \Gettext\Extractors\ExtractorMultiInterface
{
    public static $options = [
        // - false: to not extract comments
        // - empty string: to extract all comments
        // - non-empty string: to extract comments that start with that string
        // - array with strings to extract comments format.
        'extractComments' => false,
        'constants' => [],
        'functions' => ['gettext' => 'gettext', '__' => 'gettext', 'ngettext' => 'ngettext', 'n__' => 'ngettext', 'pgettext' => 'pgettext', 'p__' => 'pgettext', 'dgettext' => 'dgettext', 'd__' => 'dgettext', 'dngettext' => 'dngettext', 'dn__' => 'dngettext', 'dpgettext' => 'dpgettext', 'dp__' => 'dpgettext', 'npgettext' => 'npgettext', 'np__' => 'npgettext', 'dnpgettext' => 'dnpgettext', 'dnp__' => 'dnpgettext', 'noop' => 'noop', 'noop__' => 'noop'],
    ];
    protected static $functionsScannerClass = 'Gettext\Utils\PhpFunctionsScanner';
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * @inheritDoc
     * @throws Exception
     */
    public static function fromStringMultiple($string, array $translations, array $options = [])
    {
    }
    /**
     * @inheritDoc
     */
    public static function fromFileMultiple($file, array $translations, array $options = [])
    {
    }
    /**
     * Decodes a T_CONSTANT_ENCAPSED_STRING string.
     *
     * @param string $value
     *
     * @return string
     */
    public static function convertString($value)
    {
    }
    /**
     * @param $dec
     * @return string|null
     * @see http://php.net/manual/en/function.chr.php#118804
     */
    protected static function unicodeChar($dec)
    {
    }
}
/**
 * Class to get gettext strings from php files returning arrays.
 */
class Po extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    use \Gettext\Utils\HeadersExtractorTrait;
    /**
     * Parses a .po file and append the translations found in the Translations instance.
     *
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Gets one string from multiline strings.
     *
     * @param string $line
     * @param array  $lines
     * @param int    &$i
     *
     * @return string
     */
    protected static function fixMultiLines($line, array $lines, &$i)
    {
    }
    /**
     * Convert a string from its PO representation.
     *
     * @param string $value
     *
     * @return string
     */
    public static function convertString($value)
    {
    }
}
/**
 * Class to get gettext strings from twig files returning arrays.
 */
class Twig extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    public static $options = ['extractComments' => 'notes:', 'twig' => null];
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Returns a Twig instance.
     *
     * @return Twig_Environment
     */
    protected static function createTwig()
    {
    }
}
/**
 * Class to get gettext strings from VueJS template files.
 */
class VueJs extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface, \Gettext\Extractors\ExtractorMultiInterface
{
    public static $options = ['constants' => [], 'functions' => ['gettext' => 'gettext', '__' => 'gettext', 'ngettext' => 'ngettext', 'n__' => 'ngettext', 'pgettext' => 'pgettext', 'p__' => 'pgettext', 'dgettext' => 'dgettext', 'd__' => 'dgettext', 'dngettext' => 'dngettext', 'dn__' => 'dngettext', 'dpgettext' => 'dpgettext', 'dp__' => 'dpgettext', 'npgettext' => 'npgettext', 'np__' => 'npgettext', 'dnpgettext' => 'dnpgettext', 'dnp__' => 'dnpgettext', 'noop' => 'noop', 'noop__' => 'noop']];
    protected static $functionsScannerClass = 'Gettext\Utils\JsFunctionsScanner';
    /**
     * @inheritDoc
     * @throws Exception
     */
    public static function fromFileMultiple($file, array $translations, array $options = [])
    {
    }
    /**
     * @inheritdoc
     * @throws Exception
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * @inheritDoc
     * @throws Exception
     */
    public static function fromStringMultiple($string, array $translations, array $options = [])
    {
    }
    /**
     * Extracts script tag contents using regex instead of DOM operations.
     * If we parse using DOM, some contents may change, for example, tags within strings will be stripped
     *
     * @param $string
     * @return bool|string
     */
    protected static function extractScriptTag($string)
    {
    }
    /**
     * @param string $html
     * @return DOMDocument
     */
    protected static function convertHtmlToDom($html)
    {
    }
    /**
     * Extract translations from script part
     *
     * @param string $scriptContents Only script tag contents, not the whole template
     * @param Translations|Translations[] $translations One or multiple domain Translation objects
     * @param array $options
     * @param int $lineOffset Number of lines the script is offset in the vue template file
     * @throws Exception
     */
    protected static function getScriptTranslationsFromString($scriptContents, $translations, array $options = [], $lineOffset = 0)
    {
    }
    /**
     * Parse template to extract all translations (element content and dynamic element attributes)
     *
     * @param DOMNode $dom
     * @param Translations|Translations[] $translations One or multiple domain Translation objects
     * @param array $options
     * @param int $lineOffset Line number where the template part starts in the vue file
     * @throws Exception
     */
    protected static function getTemplateTranslations(\DOMNode $dom, $translations, array $options, $lineOffset = 0)
    {
    }
    /**
     * @param array $options
     * @param DOMNode $dom
     * @param Translations|Translations[] $translations
     */
    protected static function getTagTranslations(array $options, \DOMNode $dom, $translations)
    {
    }
    /**
     * Extract JS expressions from element attribute bindings (excluding text within elements)
     * For example: <span :title="__('extract this')"> skip element content </span>
     *
     * @param array $options
     * @param DOMNode $dom
     * @return string JS code
     */
    protected static function getTemplateAttributeFakeJs(array $options, \DOMNode $dom)
    {
    }
    /**
     * Loop DOM element recursively and parse out all dynamic vue attributes which are basically JS expressions
     *
     * @param array $attributePrefixes List of attribute prefixes we parse as JS (may contain translations)
     * @param DOMNode $dom
     * @param array $expressionByLine [lineNumber => [jsExpression, ..], ..]
     * @return array [lineNumber => [jsExpression, ..], ..]
     */
    protected static function getVueAttributeExpressions(array $attributePrefixes, \DOMNode $dom, array &$expressionByLine = [])
    {
    }
    /**
     * Check if this attribute name should be parsed for translations
     *
     * @param string $attributeName
     * @param string[] $attributePrefixes
     * @return bool
     */
    protected static function isAttributeMatching($attributeName, $attributePrefixes)
    {
    }
    /**
     * Extract JS expressions from within template elements (excluding attributes)
     * For example: <span :title="skip attributes"> {{__("extract element content")}} </span>
     *
     * @param DOMNode $dom
     * @return string JS code
     */
    protected static function getTemplateFakeJs(\DOMNode $dom)
    {
    }
    /**
     * Match JS expressions in a template line
     *
     * @param string $line
     * @return string[]
     */
    protected static function parseOneTemplateLine($line)
    {
    }
}
/**
 * Class to get gettext strings from xliff format.
 */
class Xliff extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    public static $options = ['unitid_as_id' => false];
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
}
/**
 * Class to get gettext strings from yaml.
 */
class Yaml extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    use \Gettext\Utils\MultidimensionalArrayTrait;
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
}
/**
 * Class to get gettext strings from yaml.
 */
class YamlDictionary extends \Gettext\Extractors\Extractor implements \Gettext\Extractors\ExtractorInterface
{
    use \Gettext\Utils\DictionaryTrait;
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, \Gettext\Translations $translations, array $options = [])
    {
    }
}
namespace Gettext\Generators;

interface GeneratorInterface
{
    /**
     * Saves the translations in a file.
     *
     * @param Translations $translations
     * @param string       $file
     * @param array        $options
     *
     * @return bool
     */
    public static function toFile(\Gettext\Translations $translations, $file, array $options = []);
    /**
     * Generates a string with the translations ready to save in a file.
     *
     * @param Translations $translations
     * @param array        $options
     *
     * @return string
     */
    public static function toString(\Gettext\Translations $translations, array $options = []);
}
abstract class Generator implements \Gettext\Generators\GeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public static function toFile(\Gettext\Translations $translations, $file, array $options = [])
    {
    }
}
/**
 * Class to export translations to csv.
 */
class Csv extends \Gettext\Generators\Generator implements \Gettext\Generators\GeneratorInterface
{
    use \Gettext\Utils\HeadersGeneratorTrait;
    use \Gettext\Utils\CsvTrait;
    public static $options = ['includeHeaders' => false, 'delimiter' => ",", 'enclosure' => '"', 'escape_char' => "\\"];
    /**
     * {@parentDoc}.
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
}
class CsvDictionary extends \Gettext\Generators\Generator implements \Gettext\Generators\GeneratorInterface
{
    use \Gettext\Utils\DictionaryTrait;
    use \Gettext\Utils\CsvTrait;
    public static $options = ['includeHeaders' => false, 'delimiter' => ",", 'enclosure' => '"', 'escape_char' => "\\"];
    /**
     * {@parentDoc}.
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
}
class Jed extends \Gettext\Generators\Generator implements \Gettext\Generators\GeneratorInterface
{
    public static $options = ['json' => 0];
    /**
     * {@parentDoc}.
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Generates an array with all translations.
     *
     * @param Translations $translations
     *
     * @return array
     */
    protected static function buildMessages(\Gettext\Translations $translations)
    {
    }
}
class Json extends \Gettext\Generators\Generator implements \Gettext\Generators\GeneratorInterface
{
    use \Gettext\Utils\MultidimensionalArrayTrait;
    public static $options = ['json' => 0, 'includeHeaders' => false];
    /**
     * {@inheritdoc}
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
}
class JsonDictionary extends \Gettext\Generators\Generator implements \Gettext\Generators\GeneratorInterface
{
    use \Gettext\Utils\DictionaryTrait;
    public static $options = ['json' => 0, 'includeHeaders' => false];
    /**
     * {@parentDoc}.
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
}
class Mo extends \Gettext\Generators\Generator implements \Gettext\Generators\GeneratorInterface
{
    use \Gettext\Utils\HeadersGeneratorTrait;
    public static $options = ['includeHeaders' => true];
    /**
     * {@parentDoc}.
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
}
class PhpArray extends \Gettext\Generators\Generator implements \Gettext\Generators\GeneratorInterface
{
    use \Gettext\Utils\MultidimensionalArrayTrait;
    public static $options = ['includeHeaders' => true];
    /**
     * {@inheritdoc}
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Generates an array with the translations.
     *
     * @param Translations $translations
     * @param array        $options
     *
     * @return array
     */
    public static function generate(\Gettext\Translations $translations, array $options = [])
    {
    }
}
class Po extends \Gettext\Generators\Generator implements \Gettext\Generators\GeneratorInterface
{
    public static $options = ['noLocation' => false];
    /**
     * {@parentDoc}.
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Escapes and adds double quotes to a string.
     *
     * @param string $string
     *
     * @return string
     */
    protected static function multilineQuote($string)
    {
    }
    /**
     * Add one or more lines depending whether the string is multiline or not.
     *
     * @param array  &$lines
     * @param string $name
     * @param string $value
     */
    protected static function addLines(array &$lines, $name, $value)
    {
    }
    /**
     * Convert a string to its PO representation.
     *
     * @param string $value
     *
     * @return string
     */
    public static function convertString($value)
    {
    }
}
class Xliff extends \Gettext\Generators\Generator implements \Gettext\Generators\GeneratorInterface
{
    const UNIT_ID_REGEXP = '/^XLIFF_UNIT_ID: (.*)$/';
    /**
     * {@inheritdoc}
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
    protected static function createTextNode(\DOMDocument $dom, $name, $string)
    {
    }
    /**
     * Gets the translation's unit ID, if one is available.
     *
     * @param Translation $translation
     *
     * @return string|null
     */
    public static function getUnitID(\Gettext\Translation $translation)
    {
    }
}
class Yaml extends \Gettext\Generators\Generator implements \Gettext\Generators\GeneratorInterface
{
    use \Gettext\Utils\MultidimensionalArrayTrait;
    public static $options = ['includeHeaders' => false, 'indent' => 2, 'inline' => 4];
    /**
     * {@inheritdoc}
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
}
class YamlDictionary extends \Gettext\Generators\Generator implements \Gettext\Generators\GeneratorInterface
{
    use \Gettext\Utils\DictionaryTrait;
    public static $options = ['includeHeaders' => false, 'indent' => 2, 'inline' => 3];
    /**
     * {@inheritdoc}
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
}
namespace Gettext;

class GettextTranslator extends \Gettext\BaseTranslator implements \Gettext\TranslatorInterface
{
    /**
     * Constructor. Detects the current language using the environment variables.
     *
     * @param string $language
     */
    public function __construct($language = null)
    {
    }
    /**
     * Define the current locale.
     *
     * @param string   $language
     * @param int|null $category
     *
     * @return self
     */
    public function setLanguage($language, $category = null)
    {
    }
    /**
     * Loads a gettext domain.
     *
     * @param string $domain
     * @param string $path
     * @param bool   $default
     *
     * @return self
     */
    public function loadDomain($domain, $path = null, $default = true)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function gettext($original)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function ngettext($original, $plural, $value)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function dngettext($domain, $original, $plural, $value)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function npgettext($context, $original, $plural, $value)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function pgettext($context, $original)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function dgettext($domain, $original)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function dpgettext($domain, $context, $original)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function dnpgettext($domain, $context, $original, $plural, $value)
    {
    }
}
/**
 * Static class with merge contants.
 */
final class Merge
{
    const ADD = 1;
    const REMOVE = 2;
    const HEADERS_ADD = 4;
    const HEADERS_REMOVE = 8;
    const HEADERS_OVERRIDE = 16;
    const LANGUAGE_OVERRIDE = 32;
    const DOMAIN_OVERRIDE = 64;
    const TRANSLATION_OVERRIDE = 128;
    const COMMENTS_OURS = 256;
    const COMMENTS_THEIRS = 512;
    const EXTRACTED_COMMENTS_OURS = 1024;
    const EXTRACTED_COMMENTS_THEIRS = 2048;
    const FLAGS_OURS = 4096;
    const FLAGS_THEIRS = 8192;
    const REFERENCES_OURS = 16384;
    const REFERENCES_THEIRS = 32768;
    const DEFAULTS = 5;
    //1 + 4
    /**
     * Merge the flags of two translations.
     *
     * @param Translation $from
     * @param Translation $to
     * @param int         $options
     */
    public static function mergeFlags(\Gettext\Translation $from, \Gettext\Translation $to, $options = self::DEFAULTS)
    {
    }
    /**
     * Merge the extracted comments of two translations.
     *
     * @param Translation $from
     * @param Translation $to
     * @param int         $options
     */
    public static function mergeExtractedComments(\Gettext\Translation $from, \Gettext\Translation $to, $options = self::DEFAULTS)
    {
    }
    /**
     * Merge the comments of two translations.
     *
     * @param Translation $from
     * @param Translation $to
     * @param int         $options
     */
    public static function mergeComments(\Gettext\Translation $from, \Gettext\Translation $to, $options = self::DEFAULTS)
    {
    }
    /**
     * Merge the references of two translations.
     *
     * @param Translation $from
     * @param Translation $to
     * @param int         $options
     */
    public static function mergeReferences(\Gettext\Translation $from, \Gettext\Translation $to, $options = self::DEFAULTS)
    {
    }
    /**
     * Merge the translations of two translations.
     *
     * @param Translation $from
     * @param Translation $to
     * @param int         $options
     */
    public static function mergeTranslation(\Gettext\Translation $from, \Gettext\Translation $to, $options = self::DEFAULTS)
    {
    }
    /**
     * Merge the translations of two translations.
     *
     * @param Translations $from
     * @param Translations $to
     * @param int          $options
     */
    public static function mergeTranslations(\Gettext\Translations $from, \Gettext\Translations $to, $options = self::DEFAULTS)
    {
    }
    /**
     * Merge the headers of two translations.
     *
     * @param Translations $from
     * @param Translations $to
     * @param int          $options
     */
    public static function mergeHeaders(\Gettext\Translations $from, \Gettext\Translations $to, $options = self::DEFAULTS)
    {
    }
}
/**
 * Class to manage a translation string.
 */
class Translation
{
    protected $id;
    protected $context;
    protected $original;
    protected $translation = '';
    protected $plural;
    protected $pluralTranslation = [];
    protected $references = [];
    protected $comments = [];
    protected $extractedComments = [];
    protected $flags = [];
    protected $disabled = false;
    /**
     * Generates the id of a translation (context + glue + original).
     *
     * @param string $context
     * @param string $original
     *
     * @return string
     */
    public static function generateId($context, $original)
    {
    }
    /**
     * Create a new instance of a Translation object.
     *
     * This is a factory method that will work even when Translation is extended.
     *
     * @param string $context  The context of the translation
     * @param string $original The original string
     * @param string $plural   The original plural string
     * @return static New Translation instance
     */
    public static function create($context, $original, $plural = '')
    {
    }
    /**
     * Construct.
     *
     * @param string $context  The context of the translation
     * @param string $original The original string
     * @param string $plural   The original plural string
     */
    public function __construct($context, $original, $plural = '')
    {
    }
    /**
     * Clones this translation.
     *
     * @param null|string $context  Optional new context
     * @param null|string $original Optional new original
     *
     * @return Translation
     */
    public function getClone($context = null, $original = null)
    {
    }
    /**
     * Sets the id of this translation.
     * @warning The use of this function to set a custom ID will prevent
     *  Translations::find from matching this translation.
     *
     * @param string $id
     */
    public function setId($id)
    {
    }
    /**
     * Returns the id of this translation.
     *
     * @return string
     */
    public function getId()
    {
    }
    /**
     * Checks whether the translation matches with the arguments.
     *
     * @param string $context
     * @param string $original
     *
     * @return bool
     */
    public function is($context, $original = '')
    {
    }
    /**
     * Enable or disable the translation
     *
     * @param bool $disabled
     *
     * @return self
     */
    public function setDisabled($disabled)
    {
    }
    /**
     * Returns whether the translation is disabled
     *
     * @return bool
     */
    public function isDisabled()
    {
    }
    /**
     * Gets the original string.
     *
     * @return string
     */
    public function getOriginal()
    {
    }
    /**
     * Checks if the original string is empty or not.
     *
     * @return bool
     */
    public function hasOriginal()
    {
    }
    /**
     * Sets the translation string.
     *
     * @param string $translation
     *
     * @return self
     */
    public function setTranslation($translation)
    {
    }
    /**
     * Gets the translation string.
     *
     * @return string
     */
    public function getTranslation()
    {
    }
    /**
     * Checks if the translation string is empty or not.
     *
     * @return bool
     */
    public function hasTranslation()
    {
    }
    /**
     * Sets the plural translation string.
     *
     * @param string $plural
     *
     * @return self
     */
    public function setPlural($plural)
    {
    }
    /**
     * Gets the plural translation string.
     *
     * @return string
     */
    public function getPlural()
    {
    }
    /**
     * Checks if the plural translation string is empty or not.
     *
     * @return bool
     */
    public function hasPlural()
    {
    }
    /**
     * Set a new plural translation.
     *
     * @param array $plural
     *
     * @return self
     */
    public function setPluralTranslations(array $plural)
    {
    }
    /**
     * Gets all plural translations.
     *
     * @param int $size
     *
     * @return array
     */
    public function getPluralTranslations($size = null)
    {
    }
    /**
     * Checks if there are any plural translation.
     *
     * @param bool $checkContent
     *
     * @return bool
     */
    public function hasPluralTranslations($checkContent = false)
    {
    }
    /**
     * Removes all plural translations.
     *
     * @return self
     */
    public function deletePluralTranslation()
    {
    }
    /**
     * Gets the context of this translation.
     *
     * @return string
     */
    public function getContext()
    {
    }
    /**
     * Checks if the context is empty or not.
     *
     * @return bool
     */
    public function hasContext()
    {
    }
    /**
     * Adds a new reference for this translation.
     *
     * @param string   $filename The file path where the translation has been found
     * @param null|int $line     The line number where the translation has been found
     *
     * @return self
     */
    public function addReference($filename, $line = null)
    {
    }
    /**
     * Checks if the translation has any reference.
     *
     * @return bool
     */
    public function hasReferences()
    {
    }
    /**
     * Return all references for this translation.
     *
     * @return array
     */
    public function getReferences()
    {
    }
    /**
     * Removes all references.
     *
     * @return self
     */
    public function deleteReferences()
    {
    }
    /**
     * Adds a new comment for this translation.
     *
     * @param string $comment
     *
     * @return self
     */
    public function addComment($comment)
    {
    }
    /**
     * Checks if the translation has any comment.
     *
     * @return bool
     */
    public function hasComments()
    {
    }
    /**
     * Returns all comments for this translation.
     *
     * @return array
     */
    public function getComments()
    {
    }
    /**
     * Removes all comments.
     *
     * @return self
     */
    public function deleteComments()
    {
    }
    /**
     * Adds a new extracted comment for this translation.
     *
     * @param string $comment
     *
     * @return self
     */
    public function addExtractedComment($comment)
    {
    }
    /**
     * Checks if the translation has any extracted comment.
     *
     * @return bool
     */
    public function hasExtractedComments()
    {
    }
    /**
     * Returns all extracted comments for this translation.
     *
     * @return array
     */
    public function getExtractedComments()
    {
    }
    /**
     * Removes all extracted comments.
     *
     * @return self
     */
    public function deleteExtractedComments()
    {
    }
    /**
     * Adds a new flag for this translation.
     *
     * @param string $flag
     *
     * @return self
     */
    public function addFlag($flag)
    {
    }
    /**
     * Checks if the translation has any flag.
     *
     * @return bool
     */
    public function hasFlags()
    {
    }
    /**
     * Returns all extracted flags for this translation.
     *
     * @return array
     */
    public function getFlags()
    {
    }
    /**
     * Removes all flags.
     *
     * @return self
     */
    public function deleteFlags()
    {
    }
    /**
     * Merges this translation with other translation.
     *
     * @param Translation $translation The translation to merge with
     * @param int         $options
     *
     * @return self
     */
    public function mergeWith(\Gettext\Translation $translation, $options = \Gettext\Merge::DEFAULTS)
    {
    }
}
/**
 * Class to manage a collection of translations.
 *
 * @method static $this fromBladeFile(string $filename, array $options = [])
 * @method static $this fromBladeString(string $string, array $options = [])
 * @method $this addFromBladeFile(string $filename, array $options = [])
 * @method $this addFromBladeString(string $string, array $options = [])
 * @method static $this fromCsvFile(string $filename, array $options = [])
 * @method static $this fromCsvString(string $string, array $options = [])
 * @method $this addFromCsvFile(string $filename, array $options = [])
 * @method $this addFromCsvString(string $string, array $options = [])
 * @method bool toCsvFile(string $filename, array $options = [])
 * @method string toCsvString(array $options = [])
 * @method static $this fromCsvDictionaryFile(string $filename, array $options = [])
 * @method static $this fromCsvDictionaryString(string $string, array $options = [])
 * @method $this addFromCsvDictionaryFile(string $filename, array $options = [])
 * @method $this addFromCsvDictionaryString(string $string, array $options = [])
 * @method bool toCsvDictionaryFile(string $filename, array $options = [])
 * @method string toCsvDictionaryString(array $options = [])
 * @method static $this fromJedFile(string $filename, array $options = [])
 * @method static $this fromJedString(string $string, array $options = [])
 * @method $this addFromJedFile(string $filename, array $options = [])
 * @method $this addFromJedString(string $string, array $options = [])
 * @method bool toJedFile(string $filename, array $options = [])
 * @method string toJedString(array $options = [])
 * @method static $this fromJsCodeFile(string $filename, array $options = [])
 * @method static $this fromJsCodeString(string $string, array $options = [])
 * @method $this addFromJsCodeFile(string $filename, array $options = [])
 * @method $this addFromJsCodeString(string $string, array $options = [])
 * @method static $this fromJsonFile(string $filename, array $options = [])
 * @method static $this fromJsonString(string $string, array $options = [])
 * @method $this addFromJsonFile(string $filename, array $options = [])
 * @method $this addFromJsonString(string $string, array $options = [])
 * @method bool toJsonFile(string $filename, array $options = [])
 * @method string toJsonString(array $options = [])
 * @method static $this fromJsonDictionaryFile(string $filename, array $options = [])
 * @method static $this fromJsonDictionaryString(string $string, array $options = [])
 * @method $this addFromJsonDictionaryFile(string $filename, array $options = [])
 * @method $this addFromJsonDictionaryString(string $string, array $options = [])
 * @method bool toJsonDictionaryFile(string $filename, array $options = [])
 * @method string toJsonDictionaryString(array $options = [])
 * @method static $this fromMoFile(string $filename, array $options = [])
 * @method static $this fromMoString(string $string, array $options = [])
 * @method $this addFromMoFile(string $filename, array $options = [])
 * @method $this addFromMoString(string $string, array $options = [])
 * @method bool toMoFile(string $filename, array $options = [])
 * @method string toMoString(array $options = [])
 * @method static $this fromPhpArrayFile(string $filename, array $options = [])
 * @method static $this fromPhpArrayString(string $string, array $options = [])
 * @method $this addFromPhpArrayFile(string $filename, array $options = [])
 * @method $this addFromPhpArrayString(string $string, array $options = [])
 * @method bool toPhpArrayFile(string $filename, array $options = [])
 * @method string toPhpArrayString(array $options = [])
 * @method static $this fromPhpCodeFile(string $filename, array $options = [])
 * @method static $this fromPhpCodeString(string $string, array $options = [])
 * @method $this addFromPhpCodeFile(string $filename, array $options = [])
 * @method $this addFromPhpCodeString(string $string, array $options = [])
 * @method static $this fromPoFile(string $filename, array $options = [])
 * @method static $this fromPoString(string $string, array $options = [])
 * @method $this addFromPoFile(string $filename, array $options = [])
 * @method $this addFromPoString(string $string, array $options = [])
 * @method bool toPoFile(string $filename, array $options = [])
 * @method string toPoString(array $options = [])
 * @method static $this fromTwigFile(string $filename, array $options = [])
 * @method static $this fromTwigString(string $string, array $options = [])
 * @method $this addFromTwigFile(string $filename, array $options = [])
 * @method $this addFromTwigString(string $string, array $options = [])
 * @method static $this fromVueJsFile(string $filename, array $options = [])
 * @method static $this fromVueJsString(string $filename, array $options = [])
 * @method $this addFromVueJsFile(string $filename, array $options = [])
 * @method $this addFromVueJsString(string $filename, array $options = [])
 * @method static $this fromXliffFile(string $filename, array $options = [])
 * @method static $this fromXliffString(string $string, array $options = [])
 * @method $this addFromXliffFile(string $filename, array $options = [])
 * @method $this addFromXliffString(string $string, array $options = [])
 * @method bool toXliffFile(string $filename, array $options = [])
 * @method string toXliffString(array $options = [])
 * @method static $this fromYamlFile(string $filename, array $options = [])
 * @method static $this fromYamlString(string $string, array $options = [])
 * @method $this addFromYamlFile(string $filename, array $options = [])
 * @method $this addFromYamlString(string $string, array $options = [])
 * @method bool toYamlFile(string $filename, array $options = [])
 * @method string toYamlString(array $options = [])
 * @method static $this fromYamlDictionaryFile(string $filename, array $options = [])
 * @method static $this fromYamlDictionaryString(string $string, array $options = [])
 * @method $this addFromYamlDictionaryFile(string $filename, array $options = [])
 * @method $this addFromYamlDictionaryString(string $string, array $options = [])
 * @method bool toYamlDictionaryFile(string $filename, array $options = [])
 * @method string toYamlDictionaryString(array $options = [])
 */
class Translations extends \ArrayObject
{
    const HEADER_LANGUAGE = 'Language';
    const HEADER_PLURAL = 'Plural-Forms';
    const HEADER_DOMAIN = 'X-Domain';
    public static $options = ['defaultHeaders' => ['Project-Id-Version' => '', 'Report-Msgid-Bugs-To' => '', 'Last-Translator' => '', 'Language-Team' => '', 'MIME-Version' => '1.0', 'Content-Type' => 'text/plain; charset=UTF-8', 'Content-Transfer-Encoding' => '8bit'], 'headersSorting' => false, 'defaultDateHeaders' => ['POT-Creation-Date', 'PO-Revision-Date']];
    protected $headers;
    protected $translationClass;
    /**
     * @see ArrayObject::__construct()
     */
    public function __construct($input = [], $flags = 0, $iterator_class = 'ArrayIterator', $translationClass = 'Gettext\Translation')
    {
    }
    /**
     * Magic method to create new instances using extractors
     * For example: Translations::fromMoFile($filename, $options);.
     *
     * @return Translations
     */
    public static function __callStatic($name, $arguments)
    {
    }
    /**
     * Magic method to import/export the translations to a specific format
     * For example: $translations->toMoFile($filename, $options);
     * For example: $translations->addFromMoFile($filename, $options);.
     *
     * @return self|bool
     */
    public function __call($name, $arguments)
    {
    }
    /**
     * Magic method to clone each translation on clone the translations object.
     */
    public function __clone()
    {
    }
    /**
     * Control the new translations added.
     *
     * @param mixed       $index
     * @param Translation $value
     *
     * @throws InvalidArgumentException If the value is not an instance of Gettext\Translation
     *
     * @return Translation
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($index, $value)
    {
    }
    /**
     * Set the plural definition.
     *
     * @param int    $count
     * @param string $rule
     *
     * @return self
     */
    public function setPluralForms($count, $rule)
    {
    }
    /**
     * Returns the parsed plural definition.
     *
     * @param null|array [count, rule]
     */
    public function getPluralForms()
    {
    }
    /**
     * Set a new header.
     *
     * @param string $name
     * @param string $value
     *
     * @return self
     */
    public function setHeader($name, $value)
    {
    }
    /**
     * Returns a header value.
     *
     * @param string $name
     *
     * @return null|string
     */
    public function getHeader($name)
    {
    }
    /**
     * Returns all header for this translations (in alphabetic order).
     *
     * @return array
     */
    public function getHeaders()
    {
    }
    /**
     * Removes all headers.
     *
     * @return self
     */
    public function deleteHeaders()
    {
    }
    /**
     * Removes one header.
     *
     * @param string $name
     *
     * @return self
     */
    public function deleteHeader($name)
    {
    }
    /**
     * Returns the language value.
     *
     * @return string $language
     */
    public function getLanguage()
    {
    }
    /**
     * Sets the language and the plural forms.
     *
     * @param string $language
     *
     * @throws InvalidArgumentException if the language hasn't been recognized
     *
     * @return self
     */
    public function setLanguage($language)
    {
    }
    /**
     * Checks whether the language is empty or not.
     *
     * @return bool
     */
    public function hasLanguage()
    {
    }
    /**
     * Set a new domain for this translations.
     *
     * @param string $domain
     *
     * @return self
     */
    public function setDomain($domain)
    {
    }
    /**
     * Returns the domain.
     *
     * @return string
     */
    public function getDomain()
    {
    }
    /**
     * Checks whether the domain is empty or not.
     *
     * @return bool
     */
    public function hasDomain()
    {
    }
    /**
     * Search for a specific translation.
     *
     * @param string|Translation $context  The context of the translation or a translation instance
     * @param string             $original The original string
     * @warning Translations with custom identifiers (e.g. XLIFF unit IDs) cannot be found using this function.
     *
     * @return Translation|false
     */
    public function find($context, $original = '')
    {
    }
    /**
     * Count all elements translated
     *
     * @return integer
     */
    public function countTranslated()
    {
    }
    /**
     * Creates and insert/merges a new translation.
     *
     * @param string $context  The translation context
     * @param string $original The translation original string
     * @param string $plural   The translation original plural string
     *
     * @return Translation The translation created
     */
    public function insert($context, $original, $plural = '')
    {
    }
    /**
     * Merges this translations with other translations.
     *
     * @param Translations $translations The translations instance to merge with
     * @param int          $options
     *
     * @return self
     */
    public function mergeWith(\Gettext\Translations $translations, $options = \Gettext\Merge::DEFAULTS)
    {
    }
    /**
     * Create a new instance of a Translation object.
     *
     * @param string $context  The context of the translation
     * @param string $original The original string
     * @param string $plural   The original plural string
     * @return Translation New Translation instance
     */
    public function createNewTranslation($context, $original, $plural = '')
    {
    }
}
class Translator extends \Gettext\BaseTranslator implements \Gettext\TranslatorInterface
{
    protected $domain;
    protected $dictionary = [];
    protected $plurals = [];
    /**
     * Loads translation from a Translations instance, a file on an array.
     *
     * @param Translations|string|array $translations
     *
     * @return static
     */
    public function loadTranslations($translations)
    {
    }
    /**
     * Set the default domain.
     *
     * @param string $domain
     *
     * @return static
     */
    public function defaultDomain($domain)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function gettext($original)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function ngettext($original, $plural, $value)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function dngettext($domain, $original, $plural, $value)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function npgettext($context, $original, $plural, $value)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function pgettext($context, $original)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function dgettext($domain, $original)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function dpgettext($domain, $context, $original)
    {
    }
    /**
     * @see TranslatorInterface
     *
     * {@inheritdoc}
     */
    public function dnpgettext($domain, $context, $original, $plural, $value)
    {
    }
    /**
     * Set new translations to the dictionary.
     *
     * @param array $translations
     */
    protected function addTranslations(array $translations)
    {
    }
    /**
     * Search and returns a translation.
     *
     * @param string $domain
     * @param string $context
     * @param string $original
     *
     * @return string|false
     */
    protected function getTranslation($domain, $context, $original)
    {
    }
    /**
     * Executes the plural decision code given the number to decide which
     * plural version to take.
     *
     * @param string $domain
     * @param string $n
     * @param bool   $fallback set to true to get fallback plural index
     *
     * @return int
     */
    protected function getPluralIndex($domain, $n, $fallback)
    {
    }
    /**
     * This function will recursively wrap failure states in brackets if they contain a nested terse if.
     *
     * This because PHP can not handle nested terse if's unless they are wrapped in brackets.
     *
     * This code probably only works for the gettext plural decision codes.
     *
     * return ($n==1 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<10 || $n%100>=20) ? 1 : 2);
     * becomes
     * return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4 && ($n%100<10 || $n%100>=20) ? 1 : 2));
     *
     * @param string $code  the terse if string
     * @param bool   $inner If inner is true we wrap it in brackets
     *
     * @return string A formatted terse If that PHP can work with.
     */
    private static function fixTerseIfs($code, $inner = false)
    {
    }
}
namespace Gettext\Utils;

abstract class FunctionsScanner
{
    /**
     * Scan and returns the functions and the arguments.
     *
     * @param array $constants Constants used in the code to replace
     *
     * @return array
     */
    abstract public function getFunctions(array $constants = []);
    /**
     * Search for specific functions and create translations.
     *
     * You can pass multiple translation with different domains and value found will be sorted respectively.
     *
     * @param Translations|Translations[] $translations Multiple domain translations instances where to save the values
     * @param array $options The extractor options
     * @throws Exception
     */
    public function saveGettextFunctions($translations, array $options)
    {
    }
    /**
     * Deconstruct arguments to translation values
     *
     * @param $function
     * @param $args
     * @return array|null
     * @throws Exception
     */
    protected function deconstructArgs($function, $args)
    {
    }
}
class JsFunctionsScanner extends \Gettext\Utils\FunctionsScanner
{
    protected $code;
    protected $status = [];
    /**
     * Constructor.
     *
     * @param string $code The php code to scan
     */
    public function __construct($code)
    {
    }
    /**
     * {@inheritdoc}
     */
    public function getFunctions(array $constants = [])
    {
    }
    /**
     * Get the current context of the scan.
     *
     * @param null|string $match To check whether the current status is this value
     *
     * @return string|bool
     */
    protected function status($match = null)
    {
    }
    /**
     * Add a new status to the stack.
     *
     * @param string $status
     */
    protected function downStatus($status)
    {
    }
    /**
     * Removes and return the current status.
     *
     * @return string|null
     */
    protected function upStatus()
    {
    }
    /**
     * Prepares the arguments found in functions.
     *
     * @param string $argument
     *
     * @return string
     */
    protected static function prepareArgument($argument)
    {
    }
    /**
     * Decodes a string with an argument.
     *
     * @param string $value
     *
     * @return string
     */
    protected static function convertString($value)
    {
    }
}
/**
 * Comment parsed by PhpFunctionsScanner.
 */
class ParsedComment
{
    /**
     * The comment itself.
     *
     * @var string
     */
    protected $comment;
    /**
     * The line where the comment starts.
     *
     * @var int
     */
    protected $firstLine;
    /**
     * The line where the comment ends.
     *
     * @var int
     */
    protected $lastLine;
    /**
     * Initializes the instance.
     *
     * @param string $comment The comment itself.
     * @param int    $firstLine The line where the comment starts.
     * @param int    $lastLine The line where the comment ends.
     */
    public function __construct($comment, $firstLine, $lastLine)
    {
    }
    /**
     * Create new object from raw comment data.
     *
     * @param string $value The PHP comment string.
     * @param int $line The line where the comment starts.
     *
     * @return static The parsed comment.
     */
    public static function create($value, $line)
    {
    }
    /**
     * Return the line where the comment starts.
     *
     * @return int Line number.
     */
    public function getFirstLine()
    {
    }
    /**
     * Return the line where the comment ends.
     *
     * @return int Line number.
     */
    public function getLastLine()
    {
    }
    /**
     * Return the actual comment string.
     *
     * @return string The comment.
     */
    public function getComment()
    {
    }
    /**
     * Whether this comment is related with a given function.
     *
     * @param ParsedFunction $function The function to check.
     * @return bool Whether the comment is related or not.
     */
    public function isRelatedWith(\Gettext\Utils\ParsedFunction $function)
    {
    }
    /**
     * Whether the comment matches the required prefixes.
     *
     * @param array $prefixes An array of prefixes to check.
     * @return bool Whether the comment matches the prefixes or not.
     */
    public function checkPrefixes(array $prefixes)
    {
    }
}
/**
 * Function parsed by PhpFunctionsScanner.
 */
class ParsedFunction
{
    /**
     * The function name.
     *
     * @var string
     */
    protected $name;
    /**
     * The line where the function starts.
     *
     * @var int
     */
    protected $line;
    /**
     * The strings extracted from the function arguments.
     *
     * @var string[]
     */
    protected $arguments;
    /**
     * The current index of the function (-1 if no arguments).
     *
     * @var int|null
     */
    protected $argumentIndex;
    /**
     * Shall we stop adding string chunks to the current argument?
     *
     * @var bool
     */
    protected $argumentStopped;
    /**
     * Extracted comments.
     *
     * @var ParsedComment[]|null
     */
    protected $comments;
    /**
     * Initializes the instance.
     *
     * @param string $name The function name.
     * @param int    $line The line where the function starts.
     */
    public function __construct($name, $line)
    {
    }
    /**
     * Stop extracting strings from the current argument (because we found something that's not a string).
     */
    public function stopArgument()
    {
    }
    /**
     * Go to the next argument because we a comma was found.
     */
    public function nextArgument()
    {
    }
    /**
     * Add a string to the current argument.
     *
     * @param string|null $chunk
     */
    public function addArgumentChunk($chunk)
    {
    }
    /**
     * Add a comment associated to this function.
     *
     * @param ParsedComment $comment
     */
    public function addComment($comment)
    {
    }
    /**
     * Return the line the function starts.
     *
     * @return int Line number.
     */
    public function getLine()
    {
    }
    /**
     * A closing parenthesis was found: return the final data.
     * The array returned has the following values:
     *  0 => string The function name.
     *  1 => int The line where the function starts.
     *  2 => string[] the strings extracted from the function arguments.
     *  3 => string[] the comments associated to the function.
     *
     * @return array
     */
    public function close()
    {
    }
}
class PhpFunctionsScanner extends \Gettext\Utils\FunctionsScanner
{
    /**
     * PHP tokens of the code to be parsed.
     *
     * @var array
     */
    protected $tokens;
    /**
     * If not false, comments will be extracted.
     *
     * @var string|false|array
     */
    protected $extractComments = false;
    /**
     * Enable extracting comments that start with a tag (if $tag is empty all the comments will be extracted).
     *
     * @param mixed $tag
     */
    public function enableCommentsExtraction($tag = '')
    {
    }
    /**
     * Disable comments extraction.
     */
    public function disableCommentsExtraction()
    {
    }
    /**
     * Constructor.
     *
     * @param string $code The php code to scan
     */
    public function __construct($code)
    {
    }
    /**
     * {@inheritdoc}
     */
    public function getFunctions(array $constants = [])
    {
    }
    /**
     * Extract the actual text from a PHP comment.
     *
     * If set, only returns comments that match the prefix(es).
     *
     * @param string $value The PHP comment.
     * @param int $line Line number.
     *
     * @return null|ParsedComment Comment or null if comment extraction is disabled or if there is a prefix mismatch.
     */
    protected function parsePhpComment($value, $line)
    {
    }
}
class StringReader
{
    public $pos;
    public $str;
    public $strlen;
    /**
     * Constructor.
     *
     * @param string $str The string to read
     */
    public function __construct($str)
    {
    }
    /**
     * Read and returns a part of the string.
     *
     * @param int $bytes The number of bytes to read
     *
     * @return string
     */
    public function read($bytes)
    {
    }
    /**
     * Move the cursor to a specific position.
     *
     * @param int $pos The amount of bytes to move
     *
     * @return int The new position
     */
    public function seekto($pos)
    {
    }
}
namespace WP_CLI\I18n;

// Modified Gettext Blade extractor that
// uses the up-to-date BladeOne standalone Blade engine,
// correctly supports fromStringMultiple.
/**
 * Class to get gettext strings from blade.php files returning arrays.
 */
class BladeGettextExtractor extends \Gettext\Extractors\PhpCode
{
    /**
     * Prepares a Blade compiler/engine and returns it.
     *
     * @return BladeOne
     */
    protected static function getBladeCompiler()
    {
    }
    /**
     * Compiles the Blade template string into a PHP string in one step.
     *
     * @param string $text Blade string to be compiled to a PHP string
     * @return string
     */
    protected static function compileBladeToPhp($text)
    {
    }
    /**
     * {@inheritdoc}
     *
     * Note: In the parent PhpCode class fromString() uses fromStringMultiple() (overriden here)
     */
    public static function fromStringMultiple($text, array $translations, array $options = [])
    {
    }
}
trait IterableCodeExtractor
{
    protected static $dir = '';
    /**
     * Extract the translations from a file.
     *
     * @param array|string $file_or_files A path of a file or files
     * @param Translations $translations  The translations instance to append the new translations.
     * @param array        $options      {
     *     Optional. An array of options passed down to static::fromString()
     *
     *     @type bool  $wpExtractTemplates  Extract 'Template Name' headers in theme files. Default 'false'.
     *     @type bool  $wpExtractPatterns   Extract 'Title' and 'Description' headers in pattern files. Default 'false'.
     *     @type array $restrictFileNames   Skip all files which are not included in this array.
     *     @type array $restrictDirectories Skip all directories which are not included in this array.
     * }
     * @return null
     */
    public static function fromFile($file_or_files, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Extract the translations from a file.
     *
     * @param string $dir                Root path to start the recursive traversal in.
     * @param Translations $translations The translations instance to append the new translations.
     * @param array        $options      {
     *     Optional. An array of options passed down to static::fromString()
     *
     *     @type bool $wpExtractTemplates Extract 'Template Name' headers in theme files. Default 'false'.
     *     @type array $exclude           A list of path to exclude. Default [].
     *     @type array $extensions        A list of extensions to process. Default [].
     * }
     * @return void
     */
    public static function fromDirectory($dir, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Determines whether a file is valid based on given matchers.
     *
     * @param SplFileInfo $file     File or directory.
     * @param array       $matchers List of files and directories to match.
     * @return int How strongly the file is matched.
     */
    protected static function calculateMatchScore(\SplFileInfo $file, array $matchers = [])
    {
    }
    /**
     * Determines whether or not a directory has children that may be matched.
     *
     * @param SplFileInfo $dir      Directory.
     * @param array       $matchers List of files and directories to match.
     * @return bool Whether or not there are any matchers for children of this directory.
     */
    protected static function containsMatchingChildren(\SplFileInfo $dir, array $matchers = [])
    {
    }
    /**
     * Recursively gets all PHP files within a directory.
     *
     * @param string $dir A path of a directory.
     * @param array $includes List of files and directories to include.
     * @param array $excludes List of files and directories to skip.
     * @param array $extensions List of filename extensions to process.
     *
     * @return array File list.
     */
    public static function getFilesFromDirectory($dir, array $includes = [], array $excludes = [], $extensions = [])
    {
    }
    /**
     * Determines whether the file extension of a file matches any of the given file extensions.
     * The end/last part of a multi file extension must also match (`js` of `min.js`).
     *
     * @param SplFileInfo $file       File or directory.
     * @param array       $extensions List of file extensions to match.
     * @return bool Whether the file has a file extension that matches any of the ones in the list.
     */
    protected static function file_has_file_extension($file, $extensions)
    {
    }
    /**
     * Gets the single- (e.g. `php`) or multi-file extension (e.g. `blade.php`) of a file.
     *
     * @param SplFileInfo $file File or directory.
     * @return string The single- or multi-file extension of the file.
     */
    protected static function file_get_extension_multi($file)
    {
    }
    /**
     * Trim leading slash from a path.
     *
     * @param string $path Path to trim.
     * @return string Trimmed path.
     */
    protected static function trim_leading_slash($path)
    {
    }
}
final class BladeCodeExtractor extends \WP_CLI\I18n\BladeGettextExtractor
{
    use \WP_CLI\I18n\IterableCodeExtractor;
    public static $options = ['extractComments' => ['translators', 'Translators'], 'constants' => [], 'functions' => [
        '__' => 'text_domain',
        'esc_attr__' => 'text_domain',
        'esc_html__' => 'text_domain',
        'esc_xml__' => 'text_domain',
        '_e' => 'text_domain',
        'esc_attr_e' => 'text_domain',
        'esc_html_e' => 'text_domain',
        'esc_xml_e' => 'text_domain',
        '_x' => 'text_context_domain',
        '_ex' => 'text_context_domain',
        'esc_attr_x' => 'text_context_domain',
        'esc_html_x' => 'text_context_domain',
        'esc_xml_x' => 'text_context_domain',
        '_n' => 'single_plural_number_domain',
        '_nx' => 'single_plural_number_context_domain',
        '_n_noop' => 'single_plural_domain',
        '_nx_noop' => 'single_plural_context_domain',
        // Compat.
        '_' => 'gettext',
        // Same as 'text_domain'.
        // Deprecated.
        '_c' => 'text_domain',
        '_nc' => 'single_plural_number_domain',
        '__ngettext' => 'single_plural_number_domain',
        '__ngettext_noop' => 'single_plural_domain',
    ]];
    protected static $functionsScannerClass = 'WP_CLI\I18n\PhpFunctionsScanner';
    /**
     * {@inheritdoc}
     */
    public static function fromString($text, \Gettext\Translations $translations, array $options = [])
    {
    }
}
class JsonSchemaExtractor extends \Gettext\Extractors\Extractor
{
    use \WP_CLI\I18n\IterableCodeExtractor;
    /**
     * Source URL from which to download the latest theme-i18n.json file.
     *
     * @var string
     */
    const THEME_JSON_SOURCE = 'https://develop.svn.wordpress.org/trunk/src/wp-includes/theme-i18n.json';
    /**
     * Fallback theme-18n.json file path.
     *
     * @var string
     */
    const THEME_JSON_FALLBACK = __DIR__ . '/../assets/theme-i18n.json';
    /**
     * Source URL from which to download the latest block-i18n.json file.
     *
     * @var string
     */
    const BLOCK_JSON_SOURCE = 'https://develop.svn.wordpress.org/trunk/src/wp-includes/block-i18n.json';
    /**
     * Fallback block-18n.json file path.
     *
     * @var string
     */
    const BLOCK_JSON_FALLBACK = __DIR__ . '/../assets/block-i18n.json';
    /**
     * Static cache for the remote schema files.
     *
     * @var array<string, string>
     */
    protected static $schema_cache = [];
    /**
     * Load the i18n from a remote URL or fall back to a local schema in case of an error.
     * @param string $schema i18n schema URL.
     * @param string $fallback Fallback i18n schema JSON file.
     * @return array|mixed
     */
    protected static function load_schema($schema, $fallback)
    {
    }
    /**
     * @inheritdoc
     */
    public static function fromString($text, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Extract strings from a JSON file using its i18n schema.
     *
     * @param Translations                   $translations The translations instance to append the new translations.
     * @param string|null                    $file         JSON file name or null if no reference should be added.
     * @param string|string[]|array[]|object $i18n_schema  I18n schema for the setting.
     * @param string|string[]|array[]        $settings     Value for the settings.
     *
     * @return void
     */
    private static function extract_strings_using_i18n_schema(\Gettext\Translations $translations, $file, $i18n_schema, $settings)
    {
    }
    /**
     * Given a remote URL, fetches it remotely and returns its content.
     *
     * Returns an empty string in case of error.
     *
     * @param string $url URL of the file to fetch.
     *
     * @return string Contents of the file.
     */
    private static function remote_get($url)
    {
    }
}
final class BlockExtractor extends \WP_CLI\I18n\JsonSchemaExtractor
{
    /**
     * @inheritdoc
     */
    public static function fromString($text, \Gettext\Translations $translations, array $options = [])
    {
    }
}
/**
 * Provides internationalization tools for WordPress projects.
 *
 * ## EXAMPLES
 *
 *     # Create a POT file for the WordPress plugin/theme in the current directory
 *     $ wp i18n make-pot . languages/my-plugin.pot
 *
 * @when before_wp_load
 */
class CommandNamespace extends \WP_CLI\Dispatcher\CommandNamespace
{
}
class FileDataExtractor
{
    /**
     * Retrieves metadata from a file.
     *
     * Searches for metadata in the first 8kiB of a file, such as a plugin or theme.
     * Each piece of metadata must be on its own line. Fields can not span multiple
     * lines, the value will get cut at the end of the first line.
     *
     * If the file data is not within that first 8kiB, then the author should correct
     * their plugin file and move the data headers to the top.
     *
     * @see get_file_data()
     *
     * @param string $file Path to the file.
     * @param array $headers List of headers, in the format array('HeaderKey' => 'Header Name').
     *
     * @return array Array of file headers in `HeaderKey => Header Value` format.
     */
    public static function get_file_data($file, $headers)
    {
    }
    /**
     * Retrieves metadata from a string.
     *
     * @param string $text String to look for metadata in.
     * @param array $headers List of headers.
     *
     * @return array Array of file headers in `HeaderKey => Header Value` format.
     */
    public static function get_file_data_from_string($text, $headers)
    {
    }
    /**
     * Strip close comment and close php tags from file headers used by WP.
     *
     * @see _cleanup_header_comment()
     *
     * @param string $str Header comment to clean up.
     *
     * @return string
     */
    protected static function _cleanup_header_comment($str)
    {
    }
}
/**
 * Jed file generator.
 *
 * Adds some more meta data to JED translation files than the default generator.
 */
class JedGenerator extends \Gettext\Generators\Jed
{
    /**
     * {@parentDoc}.
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Generates an array with all translations.
     *
     * @param Translations $translations
     *
     * @return array
     */
    public static function buildMessages(\Gettext\Translations $translations)
    {
    }
}
final class JsCodeExtractor extends \Gettext\Extractors\JsCode
{
    use \WP_CLI\I18n\IterableCodeExtractor;
    public static $options = ['extractComments' => ['translators', 'Translators'], 'constants' => [], 'functions' => ['__' => 'text_domain', '_x' => 'text_context_domain', '_n' => 'single_plural_number_domain', '_nx' => 'single_plural_number_context_domain']];
    protected static $functionsScannerClass = 'WP_CLI\I18n\JsFunctionsScanner';
    /**
     * @inheritdoc
     */
    public static function fromString($text, \Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * @inheritDoc
     */
    public static function fromStringMultiple($text, array $translations, array $options = [])
    {
    }
}
final class JsFunctionsScanner extends \Gettext\Utils\JsFunctionsScanner
{
    /**
     * If not false, comments will be extracted.
     *
     * @var string|false|array
     */
    private $extract_comments = false;
    /**
     * Holds a list of source code comments already added to a string.
     *
     * Prevents associating the same comment to multiple strings.
     *
     * @var Node\Comment[] $comments_cache
     */
    private $comments_cache = [];
    /**
     * Enable extracting comments that start with a tag (if $tag is empty all the comments will be extracted).
     *
     * @param mixed $tag
     */
    public function enableCommentsExtraction($tag = '')
    {
    }
    /**
     * Disable comments extraction.
     */
    public function disableCommentsExtraction()
    {
    }
    /**
     * {@inheritdoc}
     */
    public function saveGettextFunctions($translations, array $options)
    {
    }
    /**
     * Resolve the callee of a call expression using known formats.
     *
     * @param Node\CallExpression $node The call expression whose callee to resolve.
     *
     * @return array|bool Array containing the name and comments of the identifier if resolved. False if not.
     */
    private function resolveExpressionCallee(\Peast\Syntax\Node\CallExpression $node)
    {
    }
    /**
     * Returns wether or not a comment precedes a node.
     * The comment must be before the node and on the same line or the one before.
     *
     * @param Node\Comment $comment The comment.
     * @param Node\Node    $node    The node.
     *
     * @return bool Whether or not the comment precedes the node.
     */
    private function commentPrecedesNode(\Peast\Syntax\Node\Comment $comment, \Peast\Syntax\Node\Node $node)
    {
    }
}
class MakeJsonCommand extends \WP_CLI_Command
{
    /**
     * Options passed to json_encode().
     *
     * @var int JSON options.
     */
    protected $json_options = 0;
    /**
     * Extract JavaScript strings from PO files and add them to individual JSON files.
     *
     * For JavaScript internationalization purposes, WordPress requires translations to be split up into
     * one Jed-formatted JSON file per JavaScript source file.
     *
     * See https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/ to learn more
     * about WordPress JavaScript internationalization.
     *
     * ## OPTIONS
     *
     * <source>
     * : Path to an existing PO file or a directory containing multiple PO files.
     *
     * [<destination>]
     * : Path to the destination directory for the resulting JSON files. Defaults to the source directory.
     *
     * [--domain=<domain>]
     * : Text domain to use for the JSON file name. Overrides the default one extracted from the PO file.
     *
     * [--extensions=<extensions>]
     * : Additional custom JS extensions, comma separated list. By default searches for .min.js and .js extensions.
     *
     * [--purge]
     * : Whether to purge the strings that were extracted from the original source file. Defaults to true, use `--no-purge` to skip the removal.
     *
     * [--update-mo-files]
     * : Whether MO files should be updated as well after updating PO files.
     * Only has an effect when used in combination with `--purge`.
     *
     * [--pretty-print]
     * : Pretty-print resulting JSON files.
     *
     * [--use-map=<paths_or_maps>]
     * : Whether to use a mapping file for the strings, as a JSON value, array to specify multiple.
     * Each element can either be a string (file path) or object (map).
     *
     * ## EXAMPLES
     *
     *     # Create JSON files for all PO files in the languages directory
     *     $ wp i18n make-json languages
     *
     *     # Create JSON files for my-plugin-de_DE.po and leave the PO file untouched.
     *     $ wp i18n make-json my-plugin-de_DE.po /tmp --no-purge
     *
     *     # Create JSON files with mapping
     *     $ wp i18n make-json languages --use-map=build/map.json
     *
     *     # Create JSON files with multiple mappings
     *     $ wp i18n make-json languages '--use-map=["build/map.json","build/map2.json"]'
     *
     *     # Create JSON files with object mapping
     *     $ wp i18n make-json languages '--use-map={"source/index.js":"build/index.js"}'
     *
     * @when before_wp_load
     *
     * @throws WP_CLI\ExitException
     */
    public function __invoke($args, $assoc_args)
    {
    }
    /**
     * Collect maps from paths, normalize and merge
     *
     * @param string|array|bool $paths_or_maps argument. False to do nothing.
     * @return array|null       Mapping array. Null if no maps specified.
     */
    protected function build_map($paths_or_maps)
    {
    }
    /**
     * Splits a single PO file into multiple JSON files.
     *
     * @param string     $source_file Path to the source file.
     * @param string     $destination Path to the destination directory.
     * @param array|null $map         Source to build file mapping.
     * @param string     $domain      Override text domain to use.
     * @param array      $extensions  Additional extensions.
     * @return array     List of created JSON files.
     */
    protected function make_json($source_file, $destination, $map, $domain, $extensions)
    {
    }
    /**
     * Takes the references and applies map, if given
     *
     * @param array      $references translation references
     * @param array|null $map file mapping
     * @return array     mapped references
     */
    protected function reference_map($references, $map)
    {
    }
    /**
     * Builds a mapping of JS file names to translation entries.
     *
     * Exports translations for each JS file to a separate translation file.
     *
     * @param array  $mapping        A mapping of files to translation entries.
     * @param string $base_file_name Base file name for JSON files.
     * @param string $destination    Path to the destination directory.
     *
     * @return array List of created JSON files.
     */
    protected function build_json_files($mapping, $base_file_name, $destination)
    {
    }
    /**
     * Removes strings from PO file that only occur in JavaScript file.
     *
     * @param string $source_file Path to the PO file.
     * @return bool True on success, false otherwise.
     */
    protected function remove_js_strings_from_po_file($source_file)
    {
    }
}
class MakeMoCommand extends \WP_CLI_Command
{
    /**
     * Create MO files from PO files.
     *
     * ## OPTIONS
     *
     * <source>
     * : Path to an existing PO file or a directory containing multiple PO files.
     *
     * [<destination>]
     * : Path to the destination file or directory for the resulting MO files. Defaults to the source directory.
     *
     * ## EXAMPLES
     *
     *     # Create MO files for all PO files in the current directory.
     *     $ wp i18n make-mo .
     *
     *     # Create a MO file from a single PO file in a specific directory.
     *     $ wp i18n make-mo example-plugin-de_DE.po languages
     *
     *     # Create a MO file from a single PO file to a specific file destination
     *     $ wp i18n make-mo example-plugin-de_DE.po languages/bar.mo
     *
     * @when before_wp_load
     *
     * @throws WP_CLI\ExitException
     */
    public function __invoke($args, $assoc_args)
    {
    }
}
class MakePhpCommand extends \WP_CLI_Command
{
    /**
     * Create PHP files from PO files.
     *
     * ## OPTIONS
     *
     * <source>
     * : Path to an existing PO file or a directory containing multiple PO files.
     *
     * [<destination>]
     * : Path to the destination directory for the resulting PHP files. Defaults to the source directory.
     *
     * ## EXAMPLES
     *
     *     # Create PHP files for all PO files in the current directory.
     *     $ wp i18n make-php .
     *     Success: Created 3 files.
     *
     *     # Create a PHP file from a single PO file in a specific directory.
     *     $ wp i18n make-php example-plugin-de_DE.po languages
     *     Success: Created 1 file.
     *
     * @when before_wp_load
     *
     * @throws WP_CLI\ExitException
     */
    public function __invoke($args, $assoc_args)
    {
    }
}
class MakePotCommand extends \WP_CLI_Command
{
    /**
     * @var string
     */
    protected $source;
    /**
     * @var string
     */
    protected $destination;
    /**
     * @var array
     */
    protected $merge = [];
    /**
     * @var Translations[]
     */
    protected $exceptions = [];
    /**
     * @var bool
     */
    protected $subtract_and_merge;
    /**
     * @var array
     */
    protected $include = [];
    /**
     * @var array
     */
    protected $exclude = ['node_modules', '.*', 'vendor', 'Gruntfile.js', 'webpack.config.js', '*.min.js', 'test', 'tests'];
    /**
     * @var string
     */
    protected $slug;
    /**
     * @var array
     */
    protected $main_file_data = [];
    /**
     * @var string
     */
    protected $main_file_path;
    /**
     * @var bool
     */
    protected $skip_js = false;
    /**
     * @var bool
     */
    protected $skip_php = false;
    /**
     * @var bool
     */
    protected $skip_blade = false;
    /**
     * @var bool
     */
    protected $skip_block_json = false;
    /**
     * @var bool
     */
    protected $skip_theme_json = false;
    /**
     * @var bool
     */
    protected $skip_audit = false;
    /**
     * @var bool
     */
    protected $location = true;
    /**
     * @var array
     */
    protected $headers = [];
    /**
     * @var string
     */
    protected $domain;
    /**
     * @var string
     */
    protected $package_name;
    /**
     * @var string
     */
    protected $file_comment;
    /**
     * @var string
     */
    protected $project_type = 'generic';
    /**
     * These Regexes copied from http://php.net/manual/en/function.sprintf.php#93552
     * and adjusted for better precision and updated specs.
     */
    const SPRINTF_PLACEHOLDER_REGEX = '/(?:
		(?<!%)                     # Don\'t match a literal % (%%).
		(
			%                          # Start of placeholder.
			(?:[0-9]+\$)?              # Optional ordering of the placeholders.
			[+-]?                      # Optional sign specifier.
			(?:
				(?:0|\'.)?                 # Optional padding specifier - excluding the space.
				-?                         # Optional alignment specifier.
				[0-9]*                     # Optional width specifier.
				(?:\.(?:[ 0]|\'.)?[0-9]+)? # Optional precision specifier with optional padding character.
				|                      # Only recognize the space as padding in combination with a width specifier.
				(?:[ ])?                   # Optional space padding specifier.
				-?                         # Optional alignment specifier.
				[0-9]+                     # Width specifier.
				(?:\.(?:[ 0]|\'.)?[0-9]+)? # Optional precision specifier with optional padding character.
			)
			[bcdeEfFgGosuxX]           # Type specifier.
		)
	)/x';
    /**
     * "Unordered" means there's no position specifier: '%s', not '%2$s'.
     */
    const UNORDERED_SPRINTF_PLACEHOLDER_REGEX = '/(?:
		(?<!%)                     # Don\'t match a literal % (%%).
		%                          # Start of placeholder.
		[+-]?                      # Optional sign specifier.
		(?:
			(?:0|\'.)?                 # Optional padding specifier - excluding the space.
			-?                         # Optional alignment specifier.
			[0-9]*                     # Optional width specifier.
			(?:\.(?:[ 0]|\'.)?[0-9]+)? # Optional precision specifier with optional padding character.
			|                      # Only recognize the space as padding in combination with a width specifier.
			(?:[ ])?                   # Optional space padding specifier.
			-?                         # Optional alignment specifier.
			[0-9]+                     # Width specifier.
			(?:\.(?:[ 0]|\'.)?[0-9]+)? # Optional precision specifier with optional padding character.
		)
		[bcdeEfFgGosuxX]           # Type specifier.
	)/x';
    /**
     * Create a POT file for a WordPress project.
     *
     * Scans PHP, Blade-PHP and JavaScript files for translatable strings, as well as theme stylesheets and plugin files
     * if the source directory is detected as either a plugin or theme.
     *
     * ## OPTIONS
     *
     * <source>
     * : Directory to scan for string extraction.
     *
     * [<destination>]
     * : Name of the resulting POT file.
     *
     * [--slug=<slug>]
     * : Plugin or theme slug. Defaults to the source directory's basename.
     *
     * [--domain=<domain>]
     * : Text domain to look for in the source code, unless the `--ignore-domain` option is used.
     * By default, the "Text Domain" header of the plugin or theme is used.
     * If none is provided, it falls back to the project slug.
     *
     * [--ignore-domain]
     * : Ignore the text domain completely and extract strings with any text domain.
     *
     * [--merge[=<paths>]]
     * : Comma-separated list of POT files whose contents should be merged with the extracted strings.
     * If left empty, defaults to the destination POT file. POT file headers will be ignored.
     *
     * [--subtract=<paths>]
     * : Comma-separated list of POT files whose contents should act as some sort of denylist for string extraction.
     * Any string which is found on that denylist will not be extracted.
     * This can be useful when you want to create multiple POT files from the same source directory with slightly
     * different content and no duplicate strings between them.
     *
     * [--subtract-and-merge]
     * : Whether source code references and comments from the generated POT file should be instead added to the POT file
     * used for subtraction. Warning: this modifies the files passed to `--subtract`!
     *
     * [--include=<paths>]
     * : Comma-separated list of files and paths that should be used for string extraction.
     * If provided, only these files and folders will be taken into account for string extraction.
     * For example, `--include="src,my-file.php` will ignore anything besides `my-file.php` and files in the `src`
     * directory. Simple glob patterns can be used, i.e. `--include=foo-*.php` includes any PHP file with the `foo-`
     * prefix. Leading and trailing slashes are ignored, i.e. `/my/directory/` is the same as `my/directory`.
     *
     * [--exclude=<paths>]
     * : Comma-separated list of files and paths that should be skipped for string extraction.
     * For example, `--exclude=.github,myfile.php` would ignore any strings found within `myfile.php` or the `.github`
     * folder. Simple glob patterns can be used, i.e. `--exclude=foo-*.php` excludes any PHP file with the `foo-`
     * prefix. Leading and trailing slashes are ignored, i.e. `/my/directory/` is the same as `my/directory`. The
     * following files and folders are always excluded: node_modules, .git, .svn, .CVS, .hg, vendor, *.min.js.
     *
     * [--headers=<headers>]
     * : Array in JSON format of custom headers which will be added to the POT file. Defaults to empty array.
     *
     * [--location]
     * : Whether to write `#: filename:line` lines.
     * Defaults to true, use `--no-location` to skip the removal.
     * Note that disabling this option makes it harder for technically skilled translators to understand each message’s context.
     *
     * [--skip-js]
     * : Skips JavaScript string extraction. Useful when this is done in another build step, e.g. through Babel.
     *
     * [--skip-php]
     * : Skips PHP string extraction.
     *
     * [--skip-blade]
     * : Skips Blade-PHP string extraction.
     *
     * [--skip-block-json]
     * : Skips string extraction from block.json files.
     *
     * [--skip-theme-json]
     * : Skips string extraction from theme.json files.
     *
     * [--skip-audit]
     * : Skips string audit where it tries to find possible mistakes in translatable strings. Useful when running in an
     * automated environment.
     *
     * [--file-comment=<file-comment>]
     * : String that should be added as a comment to the top of the resulting POT file.
     * By default, a copyright comment is added for WordPress plugins and themes in the following manner:
     *
     *      ```
     *      Copyright (C) 2018 Example Plugin Author
     *      This file is distributed under the same license as the Example Plugin package.
     *      ```
     *
     *      If a plugin or theme specifies a license in their main plugin file or stylesheet, the comment looks like
     *      this:
     *
     *      ```
     *      Copyright (C) 2018 Example Plugin Author
     *      This file is distributed under the GPLv2.
     *      ```
     *
     * [--package-name=<name>]
     * : Name to use for package name in the resulting POT file's `Project-Id-Version` header.
     * Overrides plugin or theme name, if applicable.
     *
     * ## EXAMPLES
     *
     *     # Create a POT file for the WordPress plugin/theme in the current directory.
     *     $ wp i18n make-pot . languages/my-plugin.pot
     *
     *     # Create a POT file for the continents and cities list in WordPress core.
     *     $ wp i18n make-pot . continents-and-cities.pot --include="wp-admin/includes/continents-cities.php" --ignore-domain
     *
     *     # Create a POT file for the WordPress theme in the current directory with custom headers.
     *     $ wp i18n make-pot . languages/my-theme.pot --headers='{"Report-Msgid-Bugs-To":"https://github.com/theme-author/my-theme/","POT-Creation-Date":""}'
     *
     * @when before_wp_load
     *
     * @throws WP_CLI\ExitException
     */
    public function __invoke($args, $assoc_args)
    {
    }
    /**
     * Process arguments from command-line in a reusable way.
     *
     * @throws WP_CLI\ExitException
     *
     * @param array $args       Command arguments.
     * @param array $assoc_args Associative arguments.
     */
    public function handle_arguments($args, $assoc_args)
    {
    }
    /**
     * Removes leading and trailing slashes of a string.
     *
     * @param string $text What to add and remove slashes from.
     * @return string String without leading and trailing slashes.
     */
    protected function unslashit($text)
    {
    }
    /**
     * Retrieves the main file data of the plugin or theme.
     *
     * @return array
     */
    protected function get_main_file_data()
    {
    }
    /**
     * Returns the file headers for themes and plugins.
     *
     * @param string $type Source type, either theme or plugin.
     *
     * @return array List of file headers.
     */
    protected function get_file_headers($type)
    {
    }
    /**
     * Creates a POT file and stores it on disk.
     *
     * @throws WP_CLI\ExitException
     *
     * @return Translations A Translation set.
     */
    protected function extract_strings()
    {
    }
    /**
     * Audits strings.
     *
     * Goes through all extracted strings to find possible mistakes.
     *
     * @param Translations $translations Translations object.
     */
    protected function audit_strings($translations)
    {
    }
    /**
     * Returns the copyright comment for the given package.
     *
     * @return string File comment.
     */
    protected function get_file_comment()
    {
    }
    /**
     * Sets default POT file headers for the project.
     *
     * @param Translations $translations Translations object.
     */
    protected function set_default_headers($translations)
    {
    }
    /**
     * Extracts the WordPress version number from wp-includes/version.php.
     *
     * @return string|false Version number on success, false otherwise.
     */
    protected function get_wp_version()
    {
    }
}
final class MapCodeExtractor extends \Gettext\Extractors\JsCode
{
    use \WP_CLI\I18n\IterableCodeExtractor;
    public static $options = ['extractComments' => ['translators', 'Translators'], 'constants' => [], 'functions' => ['__' => 'text_domain', '_x' => 'text_context_domain', '_n' => 'single_plural_number_domain', '_nx' => 'single_plural_number_context_domain']];
    /**
     * {@inheritdoc}
     */
    public static function fromString($text, \Gettext\Translations $translations, array $options = [])
    {
    }
}
/**
 * PHP array file generator.
 *
 * Returns output in the form WordPress uses.
 */
class PhpArrayGenerator extends \Gettext\Generators\PhpArray
{
    public static $options = ['includeHeaders' => false];
    /**
     * {@inheritdoc}
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Generates an array with the translations.
     *
     * @param Translations $translations
     * @param array        $options
     *
     * @return array
     */
    public static function generate(\Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Returns an array containing headers and translations.
     *
     * @param Translations $translations
     * @param bool         $include_headers
     * @param bool         $force_array Unused.
     *
     * @return array
     */
    protected static function toArray(\Gettext\Translations $translations, $include_headers, $force_array = false)
    {
    }
    /**
     * Determines if the given array is a list.
     *
     * An array is considered a list if its keys consist of consecutive numbers from 0 to count($array)-1.
     *
     * Polyfill for array_is_list() in PHP 8.1.
     *
     * @see https://github.com/symfony/polyfill-php81/tree/main
     *
     * @since 4.0.0
     *
     * @codeCoverageIgnore
     *
     * @param array<mixed> $arr The array being evaluated.
     * @return bool True if array is a list, false otherwise.
     */
    private static function array_is_list(array $arr)
    {
    }
    /**
     * Outputs or returns a parsable string representation of a variable.
     *
     * Like {@see var_export()} but "minified", using short array syntax
     * and no newlines.
     *
     * @since 4.0.0
     *
     * @param mixed $value The variable you want to export.
     * @return string The variable representation.
     */
    private static function var_export($value)
    {
    }
}
final class PhpCodeExtractor extends \Gettext\Extractors\PhpCode
{
    use \WP_CLI\I18n\IterableCodeExtractor;
    public static $options = ['extractComments' => ['translators', 'Translators'], 'constants' => [], 'functions' => [
        '__' => 'text_domain',
        'esc_attr__' => 'text_domain',
        'esc_html__' => 'text_domain',
        'esc_xml__' => 'text_domain',
        '_e' => 'text_domain',
        'esc_attr_e' => 'text_domain',
        'esc_html_e' => 'text_domain',
        'esc_xml_e' => 'text_domain',
        '_x' => 'text_context_domain',
        '_ex' => 'text_context_domain',
        'esc_attr_x' => 'text_context_domain',
        'esc_html_x' => 'text_context_domain',
        'esc_xml_x' => 'text_context_domain',
        '_n' => 'single_plural_number_domain',
        '_nx' => 'single_plural_number_context_domain',
        '_n_noop' => 'single_plural_domain',
        '_nx_noop' => 'single_plural_context_domain',
        // Compat.
        '_' => 'gettext',
        // Same as 'text_domain'.
        // Deprecated.
        '_c' => 'text_domain',
        '_nc' => 'single_plural_number_domain',
        '__ngettext' => 'single_plural_number_domain',
        '__ngettext_noop' => 'single_plural_domain',
    ]];
    protected static $functionsScannerClass = 'WP_CLI\I18n\PhpFunctionsScanner';
    /**
     * {@inheritdoc}
     */
    public static function fromString($text, \Gettext\Translations $translations, array $options = [])
    {
    }
}
class PhpFunctionsScanner extends \Gettext\Utils\PhpFunctionsScanner
{
    /**
     * {@inheritdoc}
     */
    public function saveGettextFunctions($translations, array $options)
    {
    }
}
/**
 * POT file generator.
 *
 * The only difference to the existing PO file generator is that this
 * adds some comments at the very beginning of the file.
 */
class PotGenerator extends \Gettext\Generators\Po
{
    protected static $comments_before_headers = [];
    /**
     * Text to include as a comment before the start of the PO contents
     *
     * Doesn't need to include # in the beginning of lines, these are added automatically.
     *
     * @param string $comment File comment.
     */
    public static function setCommentBeforeHeaders($comment)
    {
    }
    /**
     * {@parentDoc}.
     */
    public static function toString(\Gettext\Translations $translations, array $options = [])
    {
    }
    /**
     * Escapes and adds double quotes to a string.
     *
     * @param string $text Multiline string.
     *
     * @return string[]
     */
    protected static function multilineQuote($text)
    {
    }
    /**
     * Add one or more lines depending whether the string is multiline or not.
     *
     * @param array  &$lines Array lines should be added to.
     * @param string $name   Name of the line, e.g. msgstr or msgid_plural.
     * @param string $value  The line to add.
     */
    protected static function addLines(array &$lines, $name, $value)
    {
    }
}
class UpdatePoCommand extends \WP_CLI_Command
{
    /**
     * Update PO files from a POT file.
     *
     * This behaves similarly to the [msgmerge](https://www.gnu.org/software/gettext/manual/html_node/msgmerge-Invocation.html) command.
     *
     * ## OPTIONS
     *
     * <source>
     * : Path to an existing POT file to use for updating.
     *
     * [<destination>]
     * : PO file to update or a directory containing multiple PO files.
     *   Defaults to all PO files in the source directory.
     *
     * ## EXAMPLES
     *
     *     # Update all PO files from a POT file in the current directory.
     *     $ wp i18n update-po example-plugin.pot
     *     Success: Updated 3 files.
     *
     *     # Update a PO file from a POT file.
     *     $ wp i18n update-po example-plugin.pot example-plugin-de_DE.po
     *     Success: Updated 1 file.
     *
     *     # Update all PO files in a given directory from a POT file.
     *     $ wp i18n update-po example-plugin.pot languages
     *     Success: Updated 2 files.
     *
     * @when before_wp_load
     *
     * @throws WP_CLI\ExitException
     */
    public function __invoke($args, $assoc_args)
    {
    }
}