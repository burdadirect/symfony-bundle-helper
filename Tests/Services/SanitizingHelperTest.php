<?php

namespace Tests\HBM\HelperBundle\Service;

use HBM\HelperBundle\Services\SanitizingHelper;
use PHPUnit\Framework\TestCase;

class SanitizingHelperTest extends TestCase
{

  /** @var SanitizingHelper */
  private $sanitizingHelper;

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * @return SanitizingHelper
   */
  protected function getSanitizingHelper() {
    if ($this->sanitizingHelper === NULL) {
      $this->sanitizingHelper = new SanitizingHelper(['language' => 'de', 'sep' => '/']);
    }

    return $this->sanitizingHelper;
  }

  public function testEnsureTrailingSep() {
    $values = array(
      'C:/path/to/images'   => 'C:/path/to/images/',
      'C:/path/to/images/'  => 'C:/path/to/images/',
      'C:/path/to/images//' => 'C:/path/to/images/',
    );

    foreach ($values as $key => $value) {
      $this->assertEquals($value, $this->getSanitizingHelper()->ensureTrailingSep($key), 'Normalizing of dir "'.$key.'" should be "'.$value.'".');
    }
  }

  public function testNormalizeFolderSepFolder() {
    $values = array(
      'path/to/images'     => 'path/to/images/',
      '/path/to/images/'   => 'path/to/images/',
      '//path/to/images//' => 'path/to/images/',
    );

    foreach ($values as $key => $value) {
      $this->assertEquals($value, $this->getSanitizingHelper()->normalizeFolderRelative($key), 'Normalizing of folder "'.$key.'" should be "'.$value.'".');
    }
  }

  public function testNormalizeFolderSepFile() {
    $values = array(
      'path/to/images/1.jpg'   => 'path/to/images/1.jpg',
      '/path/to/images/1.jpg'  => 'path/to/images/1.jpg',
      '//path/to/images/1.jpg' => 'path/to/images/1.jpg',
    );

    foreach ($values as $key => $value) {
      $this->assertEquals($value, $this->getSanitizingHelper()->normalizeFileRelative($key), 'Normalizing of file "'.$key.'" should be "'.$value.'".');
    }
  }

  public function testSanitizePath() {
    $values = array(
      'bla_blub/@home/5€/süß/' => 'bla_blub/at-home/5-euro/suess/',
      'bla_blub/@home/5€/süß'  => 'bla_blub/at-home/5-euro/suess/',
    );

    foreach ($values as $key => $value) {
      $this->assertEquals($value, $this->getSanitizingHelper()->sanitizePath($key), 'Sanitizing of path "'.$key.'" should be "'.$value.'".');
    }
  }

  public function testSanitizeStringWithSlashes() {
    $values = array(
      'bla_blub/@home---/5€/süß/' => 'bla_blub/-at-home-/5-euro-/suess/',
      'bla_blub/@home/5€/süß'     => 'bla_blub/-at-home/5-euro-/suess',
    );

    foreach ($values as $key => $value) {
      $this->assertEquals($value, $this->getSanitizingHelper()->sanitizeString($key, TRUE), 'Sanitizing of string "'.$key.'" should be "'.$value.'".');
    }
  }

  public function testSanitizeStringWithoutSlashes() {
    $values = array(
      'bla_blub/@home---/5€/süß/-' => 'bla_blub-at-home-5-euro-suess',
      'bla_blub/@home/5€/süß'      => 'bla_blub-at-home-5-euro-suess',
    );

    foreach ($values as $key => $value) {
      $this->assertEquals($value, $this->getSanitizingHelper()->sanitizeString($key, FALSE), 'Sanitizing of string "'.$key.'" should be "'.$value.'".');
    }
  }

  public function testSlugDE() {
    $values = array(
      'CocaCola®' => 'cocacola-eingetragene-marke',
      '5£' => '5-pfund',
    );

    foreach ($values as $key => $value) {
      $this->assertEquals($value, $this->getSanitizingHelper()->slug($key, 'de'), 'Slug (de) of string "'.$key.'" should be "'.$value.'".');
    }
  }

  public function testSlugEN() {
    $values = array(
      'CocaCola®' => 'cocacola-registered-trade-mark',
      '5£' => '5-pound',
    );

    foreach ($values as $key => $value) {
      $this->assertEquals($value, $this->getSanitizingHelper()->slug($key, 'en'), 'Slug (en) of string "'.$key.'" should be "'.$value.'".');
    }
  }

  public function testRepairHtml() {
    $testStrings = [
      '<p>Das ist <strong>eine</stong> erste Beschreibung.' => "<p>Das ist <strong>eine erste Beschreibung.</strong></p>",
      'Das ist <b>eine</i> zweite Beschreibung.' => "Das ist <b>eine</b> zweite Beschreibung.",
      'Das ist <P>eine dritte Beschreibung.' => "Das ist".PHP_EOL."<p>eine dritte Beschreibung.</p>",
    ];

    foreach ($testStrings as $key => $value) {
      $this->assertEquals($value, $this->getSanitizingHelper()->repairHtml($key), 'HTML should be: '.$value);
    }
  }

}
