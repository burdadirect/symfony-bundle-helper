<?php

namespace HBM\HelperBundle\Service;

/**
 * Taken from WordPress: wp-includes/formatting.php
 */
class SanitizingHelper
{
    /** @var array */
    private $config;

    /**
     * SanitizingHelper constructor.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
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

    /**
     * Repair html.
     */
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

        $tidy     = new \tidy();
        $htmlTidy = $tidy->repairString($html, $mergedOptions, 'UTF8');

        return str_replace("\r\n", "\n", trim($htmlTidy));
    }

    /**
     * Ensures folder sep according to arguments.
     */
    public function ensureSep(?string $path, bool $leading = null, bool $trailing = null): string
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
     * Replace windows folder delimiter.
     */
    public function unifySep(?string $path): string
    {
        return str_replace('\\', $this->sep(), $path);
    }

    /**
     * Returns a path where all string parts between the folder separator have been sanitized.
     */
    public function sanitizePath(?string $path, bool $case_sensitive = false, string $lang = null): string
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
     *
     * @param null|string $string
     */
    public function sanitizeString(?string $string, bool $with_slash = false, bool $case_sensitive = false, string $lang = null): string
    {
        return $this->sanitizeChars($string, $with_slash, $case_sensitive, $this->lang($lang));
    }

    /**
     * Returns a lowercase string where all invalid chars have been sanitized.
     *
     * @param string $lang
     */
    public function slug($string, $lang = null): string
    {
        return $this->sanitizeString($string, false, false, $this->lang($lang));
    }

    /**
     * To be continued: http://unicode.e-workers.de/unicode.php
     *
     * TODO: Continue at "Großes G mit Zirkumflex"
     */
    private function sanitizeChars(?string $string, bool $withSlash = false, bool $caseSensitive = false, string $lang = null): string
    {
        $langs = [
          '@' => ['de' => '-at-',     'en' => '-at-'],
          '&' => ['de' => '-und-',    'en' => '-and-'],
          '#' => ['de' => '-nummer-', 'en' => '-number-'],

          '€' => ['de' => '-euro-',   'en' => '-euro-'],
          '¢' => ['de' => '-cent-',   'en' => '-cent-'],
          '£' => ['de' => '-pfund-',  'en' => '-pound-'],
          '¥' => ['de' => '-yen-',    'en' => '-yen-'],

          '©' => ['de' => '-copyright-',         'en' => '-copyright-'],
          '®' => ['de' => '-eingetragene-marke-', 'en' => '-registered-trade-mark-'],

          '¼' => ['de' => '-viertel-',    'en' => '-quater-'],
          '½' => ['de' => '-halb-',       'en' => '-half-'],
          '¾' => ['de' => '-dreiviertel-', 'en' => '-three-quater-'],
        ];

        if (!$caseSensitive) {
            $string = mb_strtolower($string, 'UTF-8');
        } else {
            $string = utf8_encode($string);
        }

        if (!$withSlash) {
            $string = str_replace('/', '-', $string);
        }

        $search_replace   = [];
        $search_replace[] = ['search' => ' ', 'replace' => '-'];

        // TRANS
        if ($lang !== null) {
            foreach ($langs as $tmp_key => $tmp_value) {
                $search_replace[] = ['search' => $tmp_key, 'replace' => $tmp_value[$lang]];
            }
        }

        // UMLAUT
        $search_replace[] = ['search' => 'ä', 'replace' => 'ae'];
        $search_replace[] = ['search' => 'ö', 'replace' => 'oe'];
        $search_replace[] = ['search' => 'ü', 'replace' => 'ue'];
        $search_replace[] = ['search' => 'ß', 'replace' => 'ss'];

        // LETTERS
        $search_replace[] = ['search' => ['à', 'â', 'á', 'ã', 'å', 'æ', 'ā', 'ă', 'ą'],	'replace' => 'a'];
        $search_replace[] = ['search' => ['ç', 'ć', 'ĉ', 'ċ', 'č'],						          'replace' => 'c'];
        $search_replace[] = ['search' => ['ď', 'đ'],										                'replace' => 'd'];
        $search_replace[] = ['search' => ['è', 'ê', 'é', 'ë', 'ē', 'ĕ', 'ė', 'ę', 'ě'],	'replace' => 'e'];
        $search_replace[] = ['search' => ['ì', 'î', 'í', 'ĩ', 'ï'],						          'replace' => 'i'];
        $search_replace[] = ['search' => ['ð'],											                    'replace' => 'd']; // eth
        $search_replace[] = ['search' => ['ñ'],											                    'replace' => 'n'];
        $search_replace[] = ['search' => ['ò', 'ô', 'ó', 'õ', 'ø'],						          'replace' => 'o'];
        $search_replace[] = ['search' => ['ù', 'û', 'ú', 'ũ'],							            'replace' => 'u'];
        $search_replace[] = ['search' => ['þ'],											                    'replace' => 'th']; // thorn
        $search_replace[] = ['search' => ['ÿ', 'ý'],										                'replace' => 'y'];
        $search_replace[] = ['search' => ['š'],											                    'replace' => 's'];
        $search_replace[] = ['search' => ['ž'],											                    'replace' => 'z'];
        $search_replace[] = ['search' => ['þ'],											                    'replace' => 'b'];
        $search_replace[] = ['search' => ['ƒ'],											                    'replace' => 'f'];

        // UPPER
        if ($caseSensitive) {
            // UMLAUT
            $search_replace[] = ['search' => 'Ä', 'replace' => 'Ae'];
            $search_replace[] = ['search' => 'Ö', 'replace' => 'Oe'];
            $search_replace[] = ['search' => 'Ü', 'replace' => 'Ue'];

            // LETTERS
            $search_replace[] = ['search' => ['À', 'Â', 'Á', 'Ã', 'Å', 'Æ', 'Ā', 'Ă', 'Ą'],	'replace' => 'A'];
            $search_replace[] = ['search' => ['Ç', 'Ć', 'Ĉ', 'Ċ', 'Č'],						          'replace' => 'C'];
            $search_replace[] = ['search' => ['Ď', 'Đ'],										                'replace' => 'D'];
            $search_replace[] = ['search' => ['È', 'Ê', 'É', 'Ë', 'Ē', 'Ĕ', 'Ė', 'Ę', 'Ě'],	'replace' => 'E'];
            $search_replace[] = ['search' => ['Ì', 'Î', 'Í', 'Ĩ', 'Ï'],						          'replace' => 'I'];
            $search_replace[] = ['search' => ['Ð'],											                    'replace' => 'D']; // Eth
            $search_replace[] = ['search' => ['Ñ'],											                    'replace' => 'N'];
            $search_replace[] = ['search' => ['Ò', 'Ô', 'Ó', 'Õ', 'Ø'],						          'replace' => 'O'];
            $search_replace[] = ['search' => ['Ù', 'Û', 'Ú', 'Ũ'],							            'replace' => 'U'];
            $search_replace[] = ['search' => ['Ý'],											                    'replace' => 'Y'];
            $search_replace[] = ['search' => ['Þ'],											                    'replace' => 'Th']; // Thorn
            $search_replace[] = ['search' => ['Š'],											                    'replace' => 'S'];
            $search_replace[] = ['search' => ['Ž'],											                    'replace' => 'Z'];
        }

        foreach ($search_replace as $data) {
            $string = str_replace($data['search'], $data['replace'], $string);
        }

        $search_replace = [
          ['search' => '/^(-*)/', 'replace' => ''],                  // Replace starting hyphens
          ['search' => '/(-*)$/', 'replace' => ''],                  // Remove trailing hyphens
          ['search' => '/(-+)/', 'replace' => '-'],                   // Merge multiple hyphens to one
        ];

        foreach ($search_replace as $data) {
            $string = preg_replace($data['search'], $data['replace'], $string);
        }

        $valid_characters = 'abcdefghijklmnopqrstuvwxyz0123456789-_.';

        if ($caseSensitive) {
            $valid_characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($withSlash) {
            $valid_characters .= '/';
        }
        $valid_characters = str_split($valid_characters);

        $letters = str_split($string);
        foreach ($letters as $key => $value) {
            if (!\in_array($value, $valid_characters, true)) {
                $letters[$key] = '';
            }
        }

        return implode('', $letters);
    }
}
