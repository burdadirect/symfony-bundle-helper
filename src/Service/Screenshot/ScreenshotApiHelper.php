<?php

namespace HBM\HelperBundle\Service\Screenshot;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class ScreenshotApiHelper
{
    /** @var array */
    private $config;

    /** @var Client */
    private $client;

    /**
     * ScreenshotApiHelper constructor.
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    private function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client(['base_uri' => 'https://api.screenshotapi.io/']);
        }

        return $this->client;
    }

    /**
     * Options are:
     *   "viewport": "1280x1024"
     *   "fullpage": false
     *   "javascript": true
     *   "webdriver": "firefox"
     *   "device": "apple_iphone_3gs"
     *   "fresh": false
     *   "waitSeconds": 0
     *
     * POSSIBLE WEBDRIVERS:
     * 'firefox'
     * 'chrome'
     * 'phantomjs'
     *
     *  POSSIBLE DEVICES:
     * 'apple_iphone_3gs',
     * 'apple_iphone_4',
     * 'apple_iphone_5',
     * 'apple_iphone_6',
     * 'apple_iphone_6_plus',
     * 'blackberry_z10',
     * 'blackberry_z30',
     * 'google_nexus_4',
     * 'google_nexus_5',
     * 'google_nexus_s',
     * 'htc_evo',
     * 'lg_optimus_g',
     * 'lg_optimus_one',
     * 'motorola_droid_4',
     * 'motorola_droid_razr_hd',
     * 'samsung_galaxy_note_3',
     * 'samsung_galaxy_note',
     * 'samsung_galaxy_s4',
     * 'firefox_os_flame',
     * 'alcatel_fire_e',
     * 'geeksphone_keon',
     * 'intex_cloud_fx',
     * 'lg_fireweb',
     * 'zen_fire_105',
     * 'zte_open',
     * 'amazon_kindle_fire_hdx_7',
     * 'apple_ipad_mini',
     * 'apple_ipad_4',
     * 'blackberry_playbook',
     * 'google_nexus_10',
     * 'google_nexus_7',
     * 'motorola_xoom',
     * 'samsung_galaxy_tab',
     * 'via_vixen',
     * 'matchstick',
     * 'chromecast',
     * '720p_hd_television',
     * '1080p_full_hd_television',
     * '4k_ultra_hd_television',
     * 'lg_g_watch',
     * 'lg_g_watch_r',
     * 'moto_360'
     *
     * @return null|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function capture($url, array $options = [], array &$data = null)
    {
        $options['url'] = $url;

        // Device emulation only with chrome.
        if ($options['webdriver'] === 'firefox') {
            unset($options['device']);
        }

        // Fullpage only with firefox.
        if ($options['webdriver'] !== 'firefox') {
            $options['fullpage'] = false;
        }

        // No fullpage with device emulation.
        if (isset($options['device']) && $options['device']) {
            $options['fullpage'] = false;
        }

        $response = null;

        try {
            $response = $this->getClient()->request(
                'POST',
                'capture',
                [
                'headers' => [
                  'Content-Type' => 'application/json',
                  'Accept'       => 'application/json',
                  'apikey'       => $this->config['apikey'],
                ],
                'body' => json_encode($options),
        ]
            );
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        }

        if ($response instanceof ResponseInterface) {
            $data = json_decode($response->getBody(), true);
        } else {
            $data = [];
        }

        return $response;
    }

    /**
     * @return null|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function retrieve($key, array &$data = null)
    {
        $params = ['key' => $key];

        $response = null;

        try {
            $response = $this->getClient()->request(
                'GET',
                'retrieve?' . http_build_query($params),
                [
                'headers' => [
                  'Accept' => 'application/json',
                  'apikey' => $this->config['apikey'],
                ],
        ]
            );
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        }

        if ($response instanceof ResponseInterface) {
            $data = json_decode($response->getBody(), true);
        } else {
            $data = [];
        }

        return $response;
    }
}
