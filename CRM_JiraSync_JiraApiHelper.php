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
   *  Ex. /rest/api/3/groups/picker
   * @param string $method the http method to use
   * @param array $body the body of the post request
   * @return array | CRM_Core_Error
   */
  public static function callJiraApi($path, $method = "GET", $body = NULL) {

    // build the url
    $url = self::JIRA_REST_API_BASE .
      Civi::settings()->get("jira_cloud_id") .
      '/' .
      $path;


    $ch = curl_init($url);
    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_CUSTOMREQUEST => $method
    ));
    if($body != NULL) {
      $encodedBody = json_encode($body);
      print("\b<br>");
      print($encodedBody);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedBody);
    }
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
    if (curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) >= 300) {
      print 'Request Error:' . curl_error($ch);
      print '<br/>\nStatus Code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE);
      print_r($ch);
      return CRM_Core_Error::createError("Failed to access jira API");
      // TODO: handle this better
    } else {
      return json_decode($response, true);
    }
  }

  /**
   * Retrieve the id of the custom field "jira_user_key"
   * @return int|null|string
   * @throws CiviCRM_API3_Exception
   */
  private static function getJiraUserAccountCustomFieldId() {
    return CRM_Core_BAO_CustomField::getCustomFieldID("jira_account_id", "jira_user_details");
  }

  /**
   * Retrieve the id of the custom field "jira_user_key"
   * @return int|null|string
   * @throws CiviCRM_API3_Exception
   */
  private static function getJiraEmailAddressCustomFieldId() {
    return CRM_Core_BAO_CustomField::getCustomFieldID("jira_email_address", "jira_user_details");
  }

  /**
   * Finds a contact for given jira user object. If the contact
   * does not exist this will create it.
   * @param array $jiraUserObj the jira api array representing a user
   * @return int the contact id
   */
  public static function findOrCreateContact($jiraUserObj) {
    print "\n<br/>find or create contact<br/>\n";
    $contact= CRM_Contact_BAO_Contact::matchContactOnEmail($jiraUserObj['emailAddress']);
    if($contact == null) {
      //guess the name based on the split
      $name_words = explode(" ",  $jiraUserObj["name"]);

      $params = array(
        'contact_type' => 'Individual',
        'nick_name' => $jiraUserObj['displayName'],
        'first_name' => $name_words[0],
        'last_name' => $name_words[-1],
        'email' => $jiraUserObj['emailAddress']
      );
      print_r($params);
      // the api method does magic here
      // TODO: standardize using/not using the api methods
      $contact = civicrm_api3('Contact', 'create', $params);
      print_r($contact);
      $contactId = $contact["id"];
    } else {
      $contactId = $contact->contact_id;
    }
    print_r(self::getJiraUserAccountCustomFieldId());
    $params = array(
      'entityID' => $contactId,
      'custom_' . self::getJiraUserAccountCustomFieldId() => $jiraUserObj['accountId'],
      'custom_' . self::getJiraEmailAddressCustomFieldId() => $jiraUserObj['emailAddress']
    );
    CRM_Core_BAO_CustomValueTable::setValues($params);
    print_r($contact);

    return $contactId;
  }

  /**
   * Adds the contact to the remote group.
   * If the contact does not exist in jira this will create it.
   * If the contact has not been synced before it will add its jira account details
   * @param $contactId the contact id of the remote contact
   * @param $remoteGroup the remote group name
   */
  public static function addContactToRemoteGroup($contactId, $remoteGroup) {
    // see if the contact has an atlassian id
    $params = array(
      'entityID' => $contactId,
      'custom_' , self::getJiraUserAccountCustomFieldId() => 1
    );
    $atlassianId = CRM_Core_BAO_CustomValueTable::getValues($params)['custom_' . self::getJiraUserAccountCustomFieldId()];

    if($atlassianId == null) {
      $contactEmail = CRM_Contact_BAO_Contact::getPrimaryEmail($contactId);
      // first check if the user exists
      print("\n<br/>find " . $contactEmail);
      $atlassianId = self::findJiraUserByEmail($contactEmail);

      // if they still don't exist invite them
      if($atlassianId == null) {
        $atlassianId = self::createJiraUser($contactId);
      }

      // TODO: set atlassian fields
      print_r(self::getJiraUserAccountCustomFieldId());
      $params = array(
        'entityID' => $contactId,
        'custom_' . self::getJiraUserAccountCustomFieldId() => $atlassianId
      );
      CRM_Core_BAO_CustomValueTable::setValues($params);
    }
    $response = self::callJiraApi(
      '/rest/api/3/group/user?groupname=' . $remoteGroup, "POST", array(
        'accountId' => $atlassianId
      )
    );
  }

  /**
   * Removes a given contact from a remote group if they have an atlassianId stored
   * @param int $contactId the contact to remove
   * @param string $remoteGroup the remote group to remove them from
   */
  public static function removeContactFromRemoteGroup(&$contactId, $remoteGroup) {
    // see if the contact has an atlassian id
    $params = array(
      'entityID' => $contactId,
      'custom_' , self::getJiraUserAccountCustomFieldId() => 1
    );
    $atlassianId = CRM_Core_BAO_CustomValueTable::getValues($params)['custom_' . self::getJiraUserAccountCustomFieldId()];
    if($atlassianId != null) {
      $response = self::callJiraApi(
        '/rest/api/3/group/user?groupname=' . $remoteGroup . '&accountid=' . $atlassianId, "DELETE"
      );
    }
  }

  /**
   * Find a jira user by their email address and return their accountId
   * @param string $email the email address
   * @return string|null the user's account id or null if they don't exist
   */
  public static function findJiraUserByEmail(&$email) {
    $response = self::callJiraApi(
      '/rest/api/3/user/search?query=' . urlencode($email), "GET"
    );
    if(count($response) > 0) {
      print("count > 0");
      print_r($response[0]);
      return $response[0]["accountId"];
    } else {
      return null;
    }
  }

  /**
   * Creates a new jira user
   *
   * @param $contactId the contact id of the user to create
   * @return string the user's account id
   */
  public static function createJiraUser(&$contactId) {
    $contactDetails = CRM_Contact_BAO_Contact::getContactDetails($contactId);

    $response = self::callJiraApi(
      '/rest/api/3/user', "POST", array(
        "emailAddress" => $contactDetails[1],
        "displayName" => $contactDetails[0],
        "name" => $contactDetails[1],
        "notification" => true
      )
    );

    return $response["accountId"];
  }
}

//require_once CRM_Extension_System::singleton()->getMapper()->classToPath('CRM_OauthSync_OAuthHelper');
require_once CRM_Extension_System::singleton()->getMapper()->keyToPath('com.hjed.civicrm.oauth-sync');
CRM_JiraSync_JiraApiHelper::oauthHelper();

