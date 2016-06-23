<?php

namespace PBY\CyberclubBundle\Services;

use HBM\HelperBundle\Entity\Interfaces\CleverReachUser;

class CleverReachHelper {

  /** @var array */
  private $config;

  /** @var \SoapClient */
  private $api;

  public function __construct($config) {
    $this->config = $config;

    $wsdl_url = "http://api.cleverreach.com/soap/interface_v5.1.php?wsdl";
    try {
      $this->api = new \SoapClient($wsdl_url);
    } catch (\Exception $e) {}
  }

  /**
   * Add user to CleverReach list.
   *
   * @param \HBM\HelperBundle\Entity\Interfaces\CleverReachUser $user
   * @return bool
   */
  public function addUser(CleverReachUser $user) {
    if ($this->alreadyAdded($user->getEmail())) {
      return true;
    }

    $attributes = [];
    foreach ($this->config['fields'] as $field) {
      $attributes[] = [
        'key'   => $field['key'],
        'value' => $user->{$field['value']}()];
    }


    $data = [
      'email' => $user->getEmail(),
      'registered' => time(),
      'source' => $this->config['source'],
      'attributes' => $attributes
    ];

    $result = $this->api->receiverAdd($this->config['apikey'], $this->config['listid'], $data);
    if (strcmp($result->status, 'SUCCESS') == 0) {
      $doidata = [
        "user_ip" => $_SERVER['REMOTE_ADDR'],
        "user_agent" => $_SERVER['HTTP_USER_AGENT'],
        "referer" => $_SERVER['HTTP_REFERER'],
        "postdata" => $this->config['doi']['data'],
        "info" => $this->config['doi']['info'],
      ];

      $result = $this->api->formsSendActivationMail($this->config['apikey'], $this->config['formid'], $user->getEmail(), $doidata);
    }

    return (strcmp($result->status, 'SUCCESS') == 0);
  }

  private function alreadyAdded($mail) {
    $result = $this->api->receiverGetByEmail($this->config['apikey'], $this->config['listid'], $mail, 7);
    return (strcmp($result->status, 'SUCCESS') == 0);
  }

  public function receiverDelete($mail, $listId) {
    $result = $this->api->receiverDelete($this->config['apikey'], $listId, $mail);

    if ($result->status === 'SUCCESS') {
      return TRUE;
    } elseif (($result->status === 'ERROR') && ($result->statuscode === 20)) {
      return NULL;
    } else {
      return FALSE;
    }
  }

  public function receiverSetInactive($mail, $listId) {
    $result = $this->api->receiverSetInactive($this->config['apikey'], $listId, $mail);

    if ($result->status === 'SUCCESS') {
      return TRUE;
    } elseif (($result->status === 'ERROR') && ($result->statuscode === 20)) {
      return NULL;
    } else {
      return FALSE;
    }
  }

}
