<?php

namespace HBM\HelperBundle\Services;

/**
 * Taken from WordPress: wp-includes/formatting.php
 */
class StringSanitizer
{

  /** @var array */
  private $config;

  public function __construct($config) {
    $this->config = $config;
  }

  private function sep() {
    return $this->config['sep'];
  }

  private function lang($lang = NULL) {
    if ($lang === NULL) {
      $lang = $this->config['lang'];
    }

    return $lang;
  }

  /**
   * Repair html.
   *
   * @param string $html
   * @return string
   */
  public function repairHtml($html) {
    $tidy = new \tidy();
    $htmlTidy = $tidy->repairString($html, [
      'show-body-only' => TRUE,
      'output-xhtml' => TRUE,
      'quote-ampersand' => FALSE,
      'wrap' => FALSE,
      'char-encoding' => 'utf8',
      'newline' => 'CRLF',
    ], 'UTF8');

    return str_replace("\r\n", "\n", trim($htmlTidy));
  }

  /****************************************************************************/

  /**
   * Ensures folder sep according to arguments.
   *
   * @param $path
   * @param null $leading
   * @param null $trailing
   * @return string
   */
  public function ensureSep($path, $leading = NULL, $trailing = NULL) {
    if ($leading !== NULL) {
      $path = ltrim($path, $this->sep());
    }
    if ($leading === TRUE) {
      $path = $this->sep().$path;
    }

    if ($trailing !== NULL) {
      $path = rtrim($path, $this->sep());
    }
    if ($trailing === TRUE) {
      $path = $path.$this->sep();
    }

    return $path;
  }

  /**
   * Ensures a folder sep at the end of the path.
   *
   * @param string $path
   * @return string
   */
  public function ensureTrailingSep($path) {
    return $this->ensureSep($path, NULL, TRUE);
  }

  /**
   * Ensures a folder sep at the beginning of the path.
   *
   * @param string $path
   * @return string
   */
  public function ensureLeadingSep($path) {
    return $this->ensureSep($path, TRUE, NULL);
  }

  /**
   * Ensures a folder sep at the end of the directory and no folder sep at the beginning
   *
   * @param string $path
   * @return string
   */
  public function normalizeFolderRelative($path) {
    return $this->ensureSep($path, FALSE, TRUE);
  }

  /**
   * Ensures a folder sep at the beginning and at the end of the directory.
   *
   * @param string $path
   * @return string
   */
  public function normalizeFolderAbsolute($path) {
    return $this->ensureSep($path, TRUE, TRUE);
  }

  /**
   * Strips the folder separator from the beginning of the file.
   *
   * @param string $path
   * @return string
   */
  public function normalizeFileRelative($path) {
    return $this->ensureSep($path, FALSE, NULL);
  }

  /**
   * Strips the folder separator from the beginning of the file.
   *
   * @param string $path
   * @return string
   */
  public function normalizeFileAbsolute($path) {
    return $this->ensureSep($path, TRUE, NULL);
  }

  /****************************************************************************/

  /**
   * Returns a path where all string parts between the folder separator have been sanitized.
   *
   * @param $path
   * @param bool $case_sensitive
   * @param string $lang
   * @return string
   */
  public function sanitizePath($path, $case_sensitive = FALSE, $lang = NULL) {
    $path_parts = explode($this->sep(), $path);

    $sanitized_path_parts = array();
    foreach ($path_parts as $path_part) {
      $sanitized_path_part = $this->sanitizeChars($path_part, FALSE, $case_sensitive, $this->lang($lang));

      if (strlen($sanitized_path_part) > 0) {
        $sanitized_path_parts[] = $sanitized_path_part;
      }
    }

    return implode($this->sep(), $sanitized_path_parts).$this->sep();
  }

  /**
   * Returns a string where all invalid chars have been sanitized.
   * @param $string
   * @param bool $with_slash
   * @param bool $case_sensitive
   * @param string $lang
   * @return string
   */
  public function sanitizeString($string, $with_slash = FALSE, $case_sensitive = FALSE, $lang = NULL) {
    return $this->sanitizeChars($string, $with_slash, $case_sensitive, $this->lang($lang));
  }

  /**
   * Returns a lowercase string where all invalid chars have been sanitized.
   *
   * @param $string
   * @param string $lang
   * @return string
   */
  public function slug($string, $lang = NULL) {
    return $this->sanitizeString($string, FALSE, FALSE, $this->lang($lang));
  }

  /**
   * To be continued: http://unicode.e-workers.de/unicode.php
   *
   * TODO: Continue at "Großes G mit Zirkumflex"
   *
   * @param string $string
   * @param string|boolean $with_slash
   * @param string|boolean $case_sensitive
   * @param string $lang
   * @return string
   */
  private function sanitizeChars($string, $with_slash = FALSE, $case_sensitive = FALSE, $lang = NULL) {
    $langs = array(
      '@' => array('de' => '-at-',     'en' => '-at-'),
      '&' => array('de' => '-und-',    'en' => '-and-'),
      '#' => array('de' => '-nummer-', 'en' => '-number-'),

      '€' => array('de' => '-euro-',   'en' => '-euro-'),
      '¢' => array('de' => '-cent-',   'en' => '-cent-'),
      '£' => array('de' => '-pfund-',  'en' => '-pound-'),
      '¥' => array('de' => '-yen-',    'en' => '-yen-'),

      '©' => array('de' => '-copyright-',         'en' => '-copyright-'),
      '®' => array('de' => '-eingetragene-marke-','en' => '-registered-trade-mark-'),

      '¼' => array('de' => '-viertel-',    'en' => '-quater-'),
      '½' => array('de' => '-halb-',       'en' => '-half-'),
      '¾' => array('de' => '-dreiviertel-','en' => '-three-quater-'),

    );

    if (!$case_sensitive) {
      $string = mb_strtolower($string, 'UTF-8');
    } else {
      $string = utf8_encode($string);
    }

    if (!$with_slash) {
      $string = str_replace('/', '-', $string);
    }

    $search_replace = array();
    $search_replace[] = array('search' => ' ', 'replace' => '-');

    // TRANS
    if ($lang !== NULL) {
      foreach ($langs as $tmp_key => $tmp_value) {
        $search_replace[] = array('search' => $tmp_key, 'replace' => $tmp_value[$lang]);
      }
    }

    /**********************************************************************/

    // UMLAUT
    $search_replace[] = array('search' => 'ä', 'replace' => 'ae');
    $search_replace[] = array('search' => 'ö', 'replace' => 'oe');
    $search_replace[] = array('search' => 'ü', 'replace' => 'ue');
    $search_replace[] = array('search' => 'ß', 'replace' => 'ss');

    // LETTERS
    $search_replace[] = array('search' => array('à', 'â', 'á', 'ã', 'å', 'æ', 'ā', 'ă', 'ą'),	'replace' => 'a');
    $search_replace[] = array('search' => array('ç', 'ć', 'ĉ', 'ċ', 'č'),						          'replace' => 'c');
    $search_replace[] = array('search' => array('ď', 'đ'),										                'replace' => 'd');
    $search_replace[] = array('search' => array('è', 'ê', 'é', 'ë', 'ē', 'ĕ', 'ė', 'ę', 'ě'),	'replace' => 'e');
    $search_replace[] = array('search' => array('ì', 'î', 'í', 'ĩ', 'ï'),						          'replace' => 'i');
    $search_replace[] = array('search' => array('ð'),											                    'replace' => 'd'); // eth
    $search_replace[] = array('search' => array('ñ'),											                    'replace' => 'n');
    $search_replace[] = array('search' => array('ò', 'ô', 'ó', 'õ', 'ø'),						          'replace' => 'o');
    $search_replace[] = array('search' => array('ù', 'û', 'ú', 'ũ'),							            'replace' => 'u');
    $search_replace[] = array('search' => array('þ'),											                    'replace' => 'th'); // thorn
    $search_replace[] = array('search' => array('ÿ', 'ý'),										                'replace' => 'y');
    $search_replace[] = array('search' => array('š'),											                    'replace' => 's');
    $search_replace[] = array('search' => array('ž'),											                    'replace' => 'z');
    $search_replace[] = array('search' => array('þ'),											                    'replace' => 'b');
    $search_replace[] = array('search' => array('ƒ'),											                    'replace' => 'f');

    /**********************************************************************/

    // UPPER
    if ($case_sensitive) {
      // UMLAUT
      $search_replace[] = array('search' => 'Ä', 'replace' => 'Ae');
      $search_replace[] = array('search' => 'Ö', 'replace' => 'Oe');
      $search_replace[] = array('search' => 'Ü', 'replace' => 'Ue');

      // LETTERS
      $search_replace[] = array('search' => array('À', 'Â', 'Á', 'Ã', 'Å', 'Æ', 'Ā', 'Ă', 'Ą'),	'replace' => 'A');
      $search_replace[] = array('search' => array('Ç', 'Ć', 'Ĉ', 'Ċ', 'Č'),						          'replace' => 'C');
      $search_replace[] = array('search' => array('Ď', 'Đ'),										                'replace' => 'D');
      $search_replace[] = array('search' => array('È', 'Ê', 'É', 'Ë', 'Ē', 'Ĕ', 'Ė', 'Ę', 'Ě'),	'replace' => 'E');
      $search_replace[] = array('search' => array('Ì', 'Î', 'Í', 'Ĩ', 'Ï'),						          'replace' => 'I');
      $search_replace[] = array('search' => array('Ð'),											                    'replace' => 'D'); // Eth
      $search_replace[] = array('search' => array('Ñ'),											                    'replace' => 'N');
      $search_replace[] = array('search' => array('Ò', 'Ô', 'Ó', 'Õ', 'Ø'),						          'replace' => 'O');
      $search_replace[] = array('search' => array('Ù', 'Û', 'Ú', 'Ũ'),							            'replace' => 'U');
      $search_replace[] = array('search' => array('Ý'),											                    'replace' => 'Y');
      $search_replace[] = array('search' => array('Þ'),											                    'replace' => 'Th'); // Thorn
      $search_replace[] = array('search' => array('Š'),											                    'replace' => 'S');
      $search_replace[] = array('search' => array('Ž'),											                    'replace' => 'Z');
    }

    /**********************************************************************/

    foreach ($search_replace as $data) {
      $string = str_replace($data['search'], $data['replace'], $string);
    }

    $search_replace = array(
      array('search' => '/^(-*)/', 'replace' => ''),                  // Replace starting hyphens
      array('search' => '/(-*)$/', 'replace' => ''),                  // Remove trailing hyphens
      array('search' => '/(-+)/', 'replace' => '-')                   // Merge multiple hyphens to one
    );

    foreach ($search_replace as $data) {
      $string = preg_replace($data['search'], $data['replace'], $string);
    }

    /**********************************************************************/

    $valid_characters = 'abcdefghijklmnopqrstuvwxyz'.'0123456789'.'-_.';
    if ($case_sensitive) {
      $valid_characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    if ($with_slash) {
      $valid_characters .= '/';
    }
    $valid_characters = str_split($valid_characters);

    $letters = str_split($string);
    foreach ($letters as $key => $value) {
      if (!in_array($value, $valid_characters)) {
        $letters[$key] = '';
      }
    }

    return implode('', $letters);
  }

}
