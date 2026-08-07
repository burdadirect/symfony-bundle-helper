<?php

namespace HBM\HelperBundle\Service;

/**
 * Taken from WordPress: wp-includes/formatting.php
 */
class SanitizingHelper
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    private function lang(?string $lang): ?string
    {
        if ($lang === null) {
            $lang = $this->config['language'];
        }

        return $lang;
    }

    private function sep()
    {
        return $this->config['sep'];
    }

    public function repairHtml(?string $html, array $options = []): string
    {
        $defaultOptions = [
          'show-body-only'  => true,
          'output-xhtml'    => true,
          'quote-ampersand' => false,
          'wrap'            => false,
          'char-encoding'   => 'utf8',
          'newline'         => 'CRLF',
        ];

        $mergedOptions = array_merge($defaultOptions, $options);

        $htmlTidy = \tidy::repairString($html, $mergedOptions, 'UTF8') ?: '';

        return str_replace("\r\n", "\n", trim($htmlTidy));
    }

    public function ensureSep(?string $path, ?bool $leading = null, ?bool $trailing = null): string
    {
        if ($leading !== null) {
            $path = ltrim($path, $this->sep());
        }

        if ($leading === true) {
            $path = $this->sep() . $path;
        }

        if ($trailing !== null) {
            $path = rtrim($path, $this->sep());
        }

        if ($trailing === true) {
            $path .= $this->sep();
        }

        return $path;
    }

    /**
     * Ensures a folder sep at the end of the path.
     */
    public function ensureTrailingSep(?string $path): string
    {
        return $this->ensureSep($path, null, true);
    }

    /**
     * Ensures a folder sep at the beginning of the path.
     */
    public function ensureLeadingSep(?string $path): string
    {
        return $this->ensureSep($path, true);
    }

    /**
     * Ensures a folder sep at the end of the directory and no folder sep at the beginning
     */
    public function normalizeFolderRelative(?string $path): string
    {
        return $this->ensureSep($this->unifySep($path), false, true);
    }

    /**
     * Ensures a folder sep at the beginning and at the end of the directory.
     */
    public function normalizeFolderAbsolute(?string $path): string
    {
        return $this->ensureSep($this->unifySep($path), true, true);
    }

    /**
     * Strips the folder separator from the beginning of the file.
     */
    public function normalizeFileRelative(?string $path): string
    {
        return $this->ensureSep($this->unifySep($path), false);
    }

    /**
     * Strips the folder separator from the beginning of the file.
     */
    public function normalizeFileAbsolute(?string $path): string
    {
        return $this->ensureSep($this->unifySep($path), true);
    }

    /**
     * Replace Windows folder delimiter.
     */
    public function unifySep(?string $path): string
    {
        return str_replace('\\', $this->sep(), $path);
    }

    /**
     * Returns a path where all string parts between the folder separator have been sanitized.
     */
    public function sanitizePath(?string $path, bool $case_sensitive = false, ?string $lang = null): string
    {
        $path_parts = explode($this->sep(), $this->unifySep($path));

        $sanitized_path_parts = [];
        foreach ($path_parts as $path_part) {
            $sanitized_path_part = $this->sanitizeChars($path_part, false, $case_sensitive, $this->lang($lang));

            if ($sanitized_path_part !== '') {
                $sanitized_path_parts[] = $sanitized_path_part;
            }
        }

        return implode($this->sep(), $sanitized_path_parts) . $this->sep();
    }

    /**
     * Returns a string where all invalid chars have been sanitized.
     */
    public function sanitizeString(?string $string, bool $with_slash = false, bool $case_sensitive = false, ?string $lang = null): ?string
    {
        return $this->sanitizeChars($string, $with_slash, $case_sensitive, $this->lang($lang));
    }

    /**
     * Returns a lowercase string where all invalid chars have been sanitized.
     */
    public function slug(?string $string, ?string $lang = null): string
    {
        return $this->sanitizeString($string, false, false, $this->lang($lang));
    }

    /**
     * To be continued: http://unicode.e-workers.de/unicode.php
     *
     * TODO: Continue at "Latin Extended-B" (character 384)
     */
    private function sanitizeChars(?string $string, bool $withSlash = false, bool $caseSensitive = false, ?string $lang = null): string
    {
        // Prepare string.
        if (!$caseSensitive) {
            $string = mb_strtolower($string, 'UTF-8');
        } else {
            $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
        }

        $stringSanitized = $string;
        if (!$withSlash) {
            $stringSanitized = str_replace('/', '-', $stringSanitized);
        }


        // Replace chars.
        $searchReplace   = [];
        $searchReplace[] = ['search' => ' ', 'replace' => '-'];

        $this->addReplacementsLanguage($lang, $searchReplace);
        $this->addReplacementsLowercase($searchReplace);
        if ($caseSensitive) {
            $this->addReplacementsUppercase($searchReplace);
        }

        foreach ($searchReplace as $data) {
            $stringSanitized = str_replace($data['search'], $data['replace'], $stringSanitized);
        }


        // Cleanup hyphens.
        $searchReplace = [
          ['search' => '/^(-*)/', 'replace' => ''],  // Replace starting hyphens
          ['search' => '/(-*)$/', 'replace' => ''],  // Remove trailing hyphens
          ['search' => '/(-+)/',  'replace' => '-'], // Merge multiple hyphens to one
        ];

        foreach ($searchReplace as $data) {
            $stringSanitized = preg_replace($data['search'], $data['replace'], $stringSanitized);
        }


        // Determine valid characters.
        $validCharacters = 'abcdefghijklmnopqrstuvwxyz0123456789-_.';
        if ($caseSensitive) {
            $validCharacters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($withSlash) {
            $validCharacters .= '/';
        }
        $validCharacters = str_split($validCharacters);


        // Replace invalid characters.
        $letters = str_split($stringSanitized);
        foreach ($letters as $key => $value) {
            if (!\in_array($value, $validCharacters, true)) {
                $letters[$key] = '';
            }
        }

        return implode('', $letters);
    }

    private function addReplacementsLanguage(?string $lang, array &$searchReplace): void {
        if (!in_array($lang, ['de', 'en'], true)) {
            return;
        }

        $langs = [
          '@' => ['de' => '-at-',                 'en' => '-at-'],
          '&' => ['de' => '-und-',                'en' => '-and-'],
          '#' => ['de' => '-nummer-',             'en' => '-number-'],

          '€' => ['de' => '-euro-',               'en' => '-euro-'],
          '¢' => ['de' => '-cent-',               'en' => '-cent-'],
          '£' => ['de' => '-pfund-',              'en' => '-pound-'],
          '¥' => ['de' => '-yen-',                'en' => '-yen-'],

          '©' => ['de' => '-copyright-',          'en' => '-copyright-'],
          '®' => ['de' => '-eingetragene-marke-', 'en' => '-registered-trade-mark-'],

          '¼' => ['de' => '-viertel-',            'en' => '-quater-'],
          '½' => ['de' => '-halb-',               'en' => '-half-'],
          '¾' => ['de' => '-dreiviertel-',        'en' => '-three-quater-'],
        ];

        foreach ($langs as $char => $langData) {
            $searchReplace[] = ['search' => $char, 'replace' => $langData[$lang]];
        }
    }

    private function addReplacementsLowercase(array &$searchReplace): void {
        // UMLAUT
        $searchReplace[] = ['search' => 'ä', 'replace' => 'ae'];
        $searchReplace[] = ['search' => 'ö', 'replace' => 'oe'];
        $searchReplace[] = ['search' => 'ü', 'replace' => 'ue'];
        $searchReplace[] = ['search' => 'ß', 'replace' => 'ss'];

        // LETTERS
        $searchReplace[] = ['search' => ['à', 'â', 'á', 'ã', 'å', 'æ', 'ā', 'ă', 'ą'], 'replace' => 'a'];
        $searchReplace[] = ['search' => ['þ'],                                         'replace' => 'b'];
        $searchReplace[] = ['search' => ['ç', 'ć', 'ĉ', 'ċ', 'č'],                     'replace' => 'c'];
        $searchReplace[] = ['search' => ['ď', 'đ', 'ð'],                               'replace' => 'd'];
        $searchReplace[] = ['search' => ['ð'],                                         'replace' => 'd']; // eth
        $searchReplace[] = ['search' => ['è', 'ê', 'é', 'ë', 'ē', 'ĕ', 'ė', 'ę', 'ě'], 'replace' => 'e'];
        $searchReplace[] = ['search' => ['ƒ'],                                         'replace' => 'f'];
        $searchReplace[] = ['search' => ['ĝ', 'ğ', 'ġ', 'ģ'],                          'replace' => 'g'];
        $searchReplace[] = ['search' => ['ĥ', 'ħ'],                                    'replace' => 'h'];
        $searchReplace[] = ['search' => ['ì', 'î', 'í', 'ĩ', 'ï', 'ī', 'ĭ', 'į', 'ı'], 'replace' => 'i'];
        $searchReplace[] = ['search' => ['ĳ'],                                         'replace' => 'ij'];
        $searchReplace[] = ['search' => ['ĵ'],                                         'replace' => 'j'];
        $searchReplace[] = ['search' => ['ķ', 'ĸ'],                                    'replace' => 'k'];
        $searchReplace[] = ['search' => ['ĺ', 'ļ', 'ľ', 'ŀ', 'ł'],                     'replace' => 'l'];
        $searchReplace[] = ['search' => ['ñ', 'ń', 'ņ', 'ň', 'ŉ', 'ŋ'],                'replace' => 'n'];
        $searchReplace[] = ['search' => ['ò', 'ô', 'ó', 'õ', 'ø', 'ō', 'ŏ', 'ő'],      'replace' => 'o'];
        $searchReplace[] = ['search' => ['œ'],                                         'replace' => 'oe'];
        $searchReplace[] = ['search' => ['ŕ', 'ŗ', 'ř'],                               'replace' => 'r'];
        $searchReplace[] = ['search' => ['š', 'ś', 'ŝ', 'ş', 'ſ'],                     'replace' => 's'];
        $searchReplace[] = ['search' => ['ţ', 'ť', 'ŧ'],                               'replace' => 't'];
        $searchReplace[] = ['search' => ['þ'],                                         'replace' => 'th']; // thorn
        $searchReplace[] = ['search' => ['ù', 'û', 'ú', 'ũ', 'ū', 'ŭ', 'ů', 'ű', 'ų'], 'replace' => 'u'];
        $searchReplace[] = ['search' => ['ŵ'],                                         'replace' => 'w'];
        $searchReplace[] = ['search' => ['ÿ', 'ý', 'ŷ'],                               'replace' => 'y'];
        $searchReplace[] = ['search' => ['ž', 'ź', 'ż'],                               'replace' => 'z'];
    }

    private function addReplacementsUppercase(array &$searchReplace): void {
        // UMLAUT
        $searchReplace[] = ['search' => 'Ä', 'replace' => 'Ae'];
        $searchReplace[] = ['search' => 'Ö', 'replace' => 'Oe'];
        $searchReplace[] = ['search' => 'Ü', 'replace' => 'Ue'];

        // LETTERS
        $searchReplace[] = ['search' => ['À', 'Â', 'Á', 'Ã', 'Å', 'Æ', 'Ā', 'Ă', 'Ą'], 'replace' => 'A'];
        $searchReplace[] = ['search' => ['Ç', 'Ć', 'Ĉ', 'Ċ', 'Č'],                     'replace' => 'C'];
        $searchReplace[] = ['search' => ['Ď', 'Đ'],                                    'replace' => 'D'];
        $searchReplace[] = ['search' => ['Ð'],                                         'replace' => 'D']; // Eth
        $searchReplace[] = ['search' => ['È', 'Ê', 'É', 'Ë', 'Ē', 'Ĕ', 'Ė', 'Ę', 'Ě'], 'replace' => 'E'];
        $searchReplace[] = ['search' => ['Ĝ', 'Ğ', 'Ġ', 'Ģ'],                          'replace' => 'G'];
        $searchReplace[] = ['search' => ['Ĥ', 'Ħ'],                                    'replace' => 'H'];
        $searchReplace[] = ['search' => ['Ì', 'Î', 'Í', 'Ĩ', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ'], 'replace' => 'I'];
        $searchReplace[] = ['search' => ['Ĳ'],                                         'replace' => 'IJ'];
        $searchReplace[] = ['search' => ['Ĵ'],                                         'replace' => 'J'];
        $searchReplace[] = ['search' => ['Ķ'],                                         'replace' => 'K'];
        $searchReplace[] = ['search' => ['Ĺ', 'Ļ', 'Ľ', 'Ŀ', 'Ł'],                     'replace' => 'L'];
        $searchReplace[] = ['search' => ['Ñ', 'Ń', 'Ņ', 'Ň', 'Ŋ'],                     'replace' => 'N'];
        $searchReplace[] = ['search' => ['Ò', 'Ô', 'Ó', 'Õ', 'Ø', 'Ō', 'Ŏ', 'Ő'],      'replace' => 'O'];
        $searchReplace[] = ['search' => ['Œ'],                                         'replace' => 'Oe'];
        $searchReplace[] = ['search' => ['Ŕ', 'Ŗ', 'Ř'],                               'replace' => 'R'];
        $searchReplace[] = ['search' => ['Š', 'Ś', 'Ŝ', 'Ş'],                          'replace' => 'S'];
        $searchReplace[] = ['search' => ['Ţ', 'Ť', 'Ŧ'],                               'replace' => 'T'];
        $searchReplace[] = ['search' => ['Þ'],                                         'replace' => 'TH']; // Thorn
        $searchReplace[] = ['search' => ['Ù', 'Û', 'Ú', 'Ũ', 'Ū', 'Ŭ', 'Ů', 'Ű', 'Ų'], 'replace' => 'U'];
        $searchReplace[] = ['search' => ['Ŵ'],                                         'replace' => 'W'];
        $searchReplace[] = ['search' => ['Ý', 'Ŷ', 'Ÿ'],                               'replace' => 'Y'];
        $searchReplace[] = ['search' => ['Ž', 'Ź', 'Ż'],                               'replace' => 'Z'];
    }

}
