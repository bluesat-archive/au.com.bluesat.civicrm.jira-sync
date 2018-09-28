<?php
/**
 * Helper Functions for the JIRA Api
 */


class CRM_JiraSync_JiraApiHelper {

  const TOKEN_URL = 'https://accounts.atlassian.com/oauth/token';
  const JIRA_REST_API_BASE = "https://api.atlassian.com/ex/jira/";

  public static function oauthHelper() {
    static $oauthHelperObj = null;
    if($oauthHelperObj == null) {
      $oauthHelperObj = new CRM_OauthSync_OAuthHelper("jira", self::TOKEN_URL);
    }
    return $oauthHelperObj;
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
        $return_path = CRM_Utils_System::url('civicrm/jira-sync/connection', 'reset=1', TRUE, NULL, FALSE, FALSE);
        header("Location: " . $return_path);
        die();
      }
    }

  }


  /**
   * Retrieves the jira cloud ids for our current token from the api
   *
   * @return array
   */
  public static function retrieveJiraCloudId() {

    $ch = curl_init( 'https://api.atlassian.com/oauth/token/accessible-resources');
//    $ch = curl_init( 'http://localhost:1500');
    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => TRUE,
    ));
    self::oauthHelper()->addAccessToken($ch);

    print("<br/>connecting\n\n<br/>");
    $response = curl_exec($ch);
    print("<br/>response\n\n<br/>");
    print_r($response);
    print("<br/>response\n\n<br/>");
    print "-" . $response . "-";
    print("\n\n<br/>");
    print curl_getinfo($ch, CURLINFO_HTTP_CODE);
    print("\n\n<br/>");
    if(curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
      echo 'Request Error:' . curl_error($ch);
      return [];
      // TODO: handle this better
    } else {
      $response_json = json_decode($response, true);//, true);
      print json_last_error();
      print json_last_error_msg();
      print("\n\n<br/>");
      print_r($response_json);
      $ids = array();
      print("\n\n<br/>");
      print_r($response_json[0]);
      print("\n\n<br/>");
      print_r($response);
      foreach ($response_json as $domain) {
        print_r($domain);
        $ids[] = $domain['id'];
      }
      return $ids;
    }
  }


  /**
   * Call a JIRA api endpoint
   *
   * @param string $path the path after the jira base url
   * @param bool $get if this is a get request
   * @param bool $post if this is a post request
   *  Ex. /rest/api/3/groups/picker
   *
   * @return array | CRM_Core_Error
   */
  public static function callJiraApi($path, $get = true, $post = false) {
    assert($get != $post);

    // build the url
    $url = self::JIRA_REST_API_BASE .
      Civi::settings()->get("jira_cloud_id") .
      '/' .
      $path;


    $ch = curl_init($url);
    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_POST => $post,
      CURLOPT_HTTPGET => $get,
    ));
    self::oauthHelper()->addAccessToken($ch);

    print("<br/>connecting\n\n<br/>");
    $response = curl_exec($ch);
    print("<br/>response\n\n<br/>");
    print_r($response);
    print("<br/>response\n\n<br/>");
    print "-" . $response . "-";
    print("\n\n<br/>");
    print curl_getinfo($ch, CURLINFO_HTTP_CODE);
    print("\n\n<br/>");
    if (curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
      echo 'Request Error:' . curl_error($ch);
      return CRM_Core_Error::createError("Failed to access jira API");
      // TODO: handle this better
    } else {
      return json_decode($response, true);
    }
  }
}

//require_once CRM_Extension_System::singleton()->getMapper()->classToPath('CRM_OauthSync_OAuthHelper');
require_once CRM_Extension_System::singleton()->getMapper()->keyToPath('com.hjed.civicrm.oauth-sync');
CRM_JiraSync_JiraApiHelper::oauthHelper();

