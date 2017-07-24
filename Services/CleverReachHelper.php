<?php

namespace HBM\HelperBundle\Services;

use HBM\HelperBundle\Entity\Interfaces\CleverReachUser;

class CleverReachHelper {

  /** @var array */
  private $config;

  public function __construct($config) {
    $this->config = $config;
  }

  /**
   * @return null|\SoapClient
   */
  public function getClient() {
    try {
      return new \SoapClient('http://api.cleverreach.com/soap/interface_v5.1.php?wsdl');
    } catch (\Exception $e) {}

    return NULL;
  }

  /**
   * Add user to CleverReach list.
   *
   * @param \HBM\HelperBundle\Entity\Interfaces\CleverReachUser $user
   * @return bool
   */
  public function addUser(CleverReachUser $user) {
    $client = $this->getClient();
    if ($client === NULL) {
      return FALSE;
    }

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

    $result = $client->receiverAdd($this->config['apikey'], $this->config['listid'], $data);
    if (strcmp($result->status, 'SUCCESS') === 0) {
      $doidata = [
        "user_ip" => $_SERVER['REMOTE_ADDR'],
        "user_agent" => $_SERVER['HTTP_USER_AGENT'],
        "referer" => $_SERVER['HTTP_REFERER'],
        "postdata" => $this->config['doi']['data'],
        "info" => $this->config['doi']['info'],
      ];

      $result = $client->formsSendActivationMail($this->config['apikey'], $this->config['formid'], $user->getEmail(), $doidata);
    }

    return (strcmp($result->status, 'SUCCESS') === 0);
  }

  private function alreadyAdded($mail) {
    $client = $this->getClient();
    if ($client === NULL) {
      return FALSE;
    }

    $result = $client->receiverGetByEmail($this->config['apikey'], $this->config['listid'], $mail, 7);

    return (strcmp($result->status, 'SUCCESS') === 0);
  }

  public function receiverDelete($mail, $listId) {
    $client = $this->getClient();
    if ($client === NULL) {
      return FALSE;
    }

    $result = $client->receiverDelete($this->config['apikey'], $listId, $mail);

    if ($result->status === 'SUCCESS') {
      return TRUE;
    } elseif (($result->status === 'ERROR') && ($result->statuscode === 20)) {
      return NULL;
    } else {
      return FALSE;
    }
  }

  public function receiverSetInactive($mail, $listId) {
    $client = $this->getClient();
    if ($client === NULL) {
      return FALSE;
    }

    $result = $client->receiverSetInactive($this->config['apikey'], $listId, $mail);

    if ($result->status === 'SUCCESS') {
      return TRUE;
    } elseif (($result->status === 'ERROR') && ($result->statuscode === 20)) {
      return NULL;
    } else {
      return FALSE;
    }
  }

}
