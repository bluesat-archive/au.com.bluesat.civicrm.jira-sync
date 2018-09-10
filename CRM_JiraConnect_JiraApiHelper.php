<?php
/**
 * Helper Functions for the JIRA Api
 */

class CRM_JiraConnect_JiraApiHelper {

  const TOKEN_URL = 'https://accounts.atlassian.com/oauth/token';

  /**
   * Generate a new state key for oauth, store it in the settings
   *
   * @return string
   *  Ex: 1234
   */
  public static function newStateKey() {
    $stateKey = uniqid("", true);

    Civi::settings()->set('jira_oauth_state', $stateKey);

    return $stateKey;
  }

  /**
   * Check if the state key is valid by comparing it against our stored value
   *
   * @param $stateKey string
   *  Ex: 123123123
   * @return bool
   */
  public static function verifyState($stateKey) {

    $actualValue = Civi::settings()->get('jira_oauth_state');

    // are they equal
    return $actualValue == $stateKey;
  }

  public static function doOAuthCodeExchange($code) {
    $client_id = Civi::settings()->get('jira_client_id');
    $client_secret = Civi::settings()->get('jira_secret');
    $redirect_url = self::generateRedirectUrl();

    $requestJsonDict = array(
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'redirect_uri' => $redirect_url,
      'grant_type' => 'authorization_code',
      'code' => $code
    );
    print_r($requestJsonDict);
    $postBody = json_encode($requestJsonDict);
    print $postBody;

    // make a request
    $ch = curl_init(self::TOKEN_URL);
    curl_setopt_array($ch, array(
      CURLOPT_POST => TRUE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
      CURLOPT_POSTFIELDS => $postBody
    ));
//    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
    print $ch;
    print_r($ch);
    $response = curl_exec($ch);
    if(curl_errno($ch)) {
      echo 'Request Error:' . curl_error($ch);
    }
    print_r($response);

//    $response = http_post_data(self::TOKEN_URL, $postBody);
//    print_r($response);


  }

  /**
   * Generates a urlencoded oauth redirect url for the app
   *
   * @return string
   */
  public static function generateRedirectUrlEncoded() {
    $redirect_url = urlencode(CRM_Utils_System::url('civicrm/jira-connect/oauth-callback', 'reset=1', TRUE, NULL, FALSE, TRUE));
    return $redirect_url;
  }

  /**
   * Generates a oauth redirect url for the app
   *
   * @return string
   */
  public static function generateRedirectUrl() {
    $redirect_url = CRM_Utils_System::url('civicrm/jira-connect/oauth-callback', 'reset=1', TRUE, NULL, FALSE, TRUE);
    return $redirect_url;
  }


}