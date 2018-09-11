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

  /**
   * Performs an oauth authorization code grant exchange.
   * Redirects back if successful.
   *
   * @param $code the code to use for the exchange
   */
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
    $postBody = json_encode($requestJsonDict, JSON_UNESCAPED_SLASHES);
    print $postBody;

    // make a request
    $ch = curl_init(self::TOKEN_URL);
//    $ch = curl_init('http://localhost:1500');
    curl_setopt_array($ch, array(
      CURLOPT_POST => TRUE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
      // the token endpoint requires a user agent
      CURLOPT_USERAGENT => 'curl/7.55.1',
      CURLOPT_POSTFIELDS => $postBody
    ));
//    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
    $response = curl_exec($ch);
    if(curl_errno($ch)) {
      echo 'Request Error:' . curl_error($ch);
      // TODO: handle this better
    } else {
      $response_json = json_decode($response, true);
      if(in_array("error", $response_json)) {
        // TODO: handle this better
        echo "<br/><br/>Error\n\n";
        echo $response_json["error_description"];
      } else {
        self::parseOAuthTokenResponse($response_json);
        // get the cloud id
        $ids = self::retrieveJiraCloudId();
        if(count($ids) > 1) {
          //TODO: handle multiple ids
          echo "Too many ids";
          die();
        } else if(count($ids) == 1) {
          Civi::settings()->set("jira_cloud_id", $ids[0]);
          Civi::settings()->set("jira connected", true);
        } else {
          //TODO: handle this
          echo "request failed";
          die();
        }
        $return_path = CRM_Utils_System::url('civicrm/jira-connect/connection', 'reset=1', TRUE, NULL, FALSE, FALSE);
        header("Location: " . $return_path);
        die();
      }
    }

  }

  /**
   * Parse a standard oauth token response and store in settings.
   * Does not handle error conditions.
   * @param $response_json array
   */
  public static function parseOAuthTokenResponse($response_json) {
    // for now just store the tokens
    Civi::settings()->set("jira_token", $response_json["access_token"]);
    Civi::settings()->set("jira_refresh", $response_json["refresh_token"]);
    Civi::settings()->set("jira_expiry", time() + $response_json["refresh_token"]);
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

  /**
   * Adds the access token to a curl request
   *
   * refreshes the token if it has expired
   * @param $curl_request
   */
  private static function addAccessToken(&$curl_request) {
    // TODO: check expiry and refresh
    curl_setopt(
      $curl_request,
      CURLOPT_HTTPHEADER,
      array(
        'Authorization: Bearer ' . Civi::settings()->get('jira_token'),
        'Accept: application/json'
      )
    );

  }

  /**
   * Retrieves the jira cloud ids for our current token from the api
   *
   * @return array
   */
  private static function retrieveJiraCloudId() {

    $ch = curl_init( 'https://api.atlassian.com/oauth/token/accessible-resources');
//    $ch = curl_init( 'http://localhost:1500');
    curl_setopt_array($ch, array(
      CURLOPT_GET => TRUE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_USERAGENT => 'curl/7.55.1',
    ));
    self::addAccessToken($ch);

    $response = curl_exec($ch);
    if(curl_errno($ch)) {
      echo 'Request Error:' . curl_error($ch);
      return [];
      // TODO: handle this better
    } else {
      echo $response;
      $response_json = json_decode($response, true);
      $ids = array();
      foreach ($response_json as $domain) {
        $ids[] = $domain['id'];
      }
      return $ids;
    }
  }

}