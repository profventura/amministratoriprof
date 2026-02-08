<?php
namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;

class CustomTemplateProcessor extends TemplateProcessor
{
    // Default to {} as per user request
    protected static $macroOpeningChars = '{';
    protected static $macroClosingChars = '}';

    /**
     * Override to ensure macros are wrapped with delimiters
     */
    protected static function ensureMacroCompleted($macro)
    {
        if (substr($macro, 0, strlen(self::$macroOpeningChars)) !== self::$macroOpeningChars && 
            substr($macro, -strlen(self::$macroClosingChars)) !== self::$macroClosingChars) {
            $macro = self::$macroOpeningChars . $macro . self::$macroClosingChars;
        }
        return $macro;
    }

    /**
     * Override to fix broken macros
     * Adapted to handle dynamic delimiters
     */
    protected function fixBrokenMacros($documentPart)
    {
        $start = preg_quote(self::$macroOpeningChars, '/');
        $end = preg_quote(self::$macroClosingChars, '/');
        
        // Handling the first char of start delimiter being separated (e.g. $ and {)
        $startFirst = preg_quote(substr(self::$macroOpeningChars, 0, 1), '/');
        $startRest = preg_quote(substr(self::$macroOpeningChars, 1), '/');
        
        // Regex to find broken macros
        // Matches startFirst followed by (startRest OR something that ends with >startRest)
        // Then content that is not end delimiter, ending with end delimiter
        // This is a generalization of the regex in generate_certificate.php
        
        $regex = '/' . $startFirst . '(?:' . $startRest . '|[^{]*\>\{)[^' . substr($end, -1) . ']*' . $end . '/U';
        
        // If delimiters are simple (like #{ and })
        if (self::$macroOpeningChars === '#{') {
             $regex = '/#(?:\{|[^{#]*\>\{)[^}#]*\}/U';
        } elseif (self::$macroOpeningChars === '${') {
             $regex = '/\$(?:\{|[^{$]*\>\{)[^}$]*\}/U';
        } elseif (self::$macroOpeningChars === '{') {
             // Case {TAG}
             // Matches { followed by content that is not }, ending with }
             // Handles fragmentation: {<w:t>TAG</w:t>}
             $regex = '/\{(?:[^{]*\>)?(?:[^{}]*)(?:\<[^{]*)?\}/U';
             // Simplified regex for broken macros with single char delimiters is trickier
             // Let's stick to the generic one constructed above which should work:
             // startFirst='\{' startRest='' 
             // Regex: /\{(?:|[^{]*\>\{)[^}]*\}/U  <-- this looks wrong for empty startRest
             
             // Let's rely on the generic construction below, but we need to ensure startRest is handled
             if ($startRest === '') {
                 // For single char delimiter '{'
                 // Look for { then anything not } then }
                 // Allowing for XML tags in between
                 $regex = '/\{[^{}]+\}/U'; // Basic non-broken
                 // For broken: {<..>TAG<..>}
                 // We want to match { ... } where ... does not contain {
                 $regex = '/\{[^{}]+\}/U'; 
                 // Wait, the generic regex logic was:
                 // $regex = '/' . $startFirst . '(?:' . $startRest . '|[^{]*\>\{)[^' . substr($end, -1) . ']*' . $end . '/U';
                 
                 // For '{' and '}':
                 // startFirst = \{
                 // startRest = 
                 // regex = /\{(?:|[^{]*\>\{)[^}]*\}/U
                 // The (?:|) part matches empty string, so it matches \{ followed by [^}]* \}
                 // This effectively matches { ... } 
                 // But we want to match broken tags too.
                 
                 // If we have {<w:t>NAME</w:t>}
                 // The strip_tags in callback handles the cleaning.
                 // We just need to capture the whole block.
                 
                 $regex = '/\{[^{}]+\}/U';
             }
        }

        return preg_replace_callback(
            $regex,
            function ($match) {
                return strip_tags($match[0]);
            },
            $documentPart
        );
    }
    
    /**
     * Override setValue to ensure we use our ensureMacroCompleted
     */
    public function setValue($search, $replace, $limit = -1): void
    {
        if (is_array($search)) {
            foreach ($search as &$item) {
                $item = static::ensureMacroCompleted($item);
            }
            unset($item);
        } else {
            $search = static::ensureMacroCompleted($search);
        }

        parent::setValue($search, $replace, $limit);
    }

    public function setMacroChars(string $macroOpeningChars, string $macroClosingChars): void {
        self::$macroOpeningChars = $macroOpeningChars;
        self::$macroClosingChars = $macroClosingChars;
    }
}
