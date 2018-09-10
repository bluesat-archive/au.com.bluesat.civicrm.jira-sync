<?php
/**
 * Created by IntelliJ IDEA.
 * User: hjed
 * Date: 9/09/18
 * Time: 6:04 PM
 */

return array(
  'jira_client_id' => array(
    'group_name' => 'Jira Settings',
    'group' => 'jira',
    'name' => 'jira_client_id',
    'type' => 'String',
    'add' => '4.4',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Jira Client ID',
    'title' =>  'Jira Client ID',
    'help_text' => '',
    'html_type' => 'Text',
    'html_attributes' => array(
      'size' => 50,
    ),
    'quick_form_type' => 'Element',
  ),
  'jira_secret' => array(
    'group_name' => 'Jira Settings',
    'group' => 'jira',
    'name' => 'jira_secret',
    'type' => 'String',
    'add' => '4.4',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Jira Secret',
    'title' => 'Jira Secret',
    'help_text' => '',
    'html_type' => 'Text',
    'html_attributes' => array(
      'size' => 50,
    ),
    'quick_form_type' => 'Element',
  ),
  'jira_token' => array(
    'group_name' => 'Jira Token Control',
    'group' => 'jira_token',
    'name' => 'jira_token',
    'type' => 'String',
    'add' => '4.4',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Jira Token',
    'title' => 'Jira Token',
    'help_text' => '',
    'html_type' => 'Text',
    'html_attributes' => array(
      'size' => 50,
    ),
    'quick_form_type' => 'Element',
  ),
  'jira_refresh' => array(
    'group_name' => 'Jira Token Control',
    'group' => 'jira_token',
    'name' => 'jira_refresh',
    'type' => 'String',
    'add' => '4.4',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Jira Refresh Token',
    'title' => 'Jira Refresh Token',
    'help_text' => '',
    'html_type' => 'Text',
    'html_attributes' => array(
      'size' => 50,
    ),
    'quick_form_type' => 'Element',
  ),
  'jira_expiry' => array(
    'group_name' => 'Jira Token Control',
    'group' => 'jira_token',
    'name' => 'jira_expiry',
    'type' => 'Integer',
    'add' => '4.4',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Jira Token Expiry in Seconds since the unix epoch',
    'title' => 'Jira Token Expiry (s)',
    'help_text' => '',
  ),
  'jira_connected' => array(
    'group_name' => 'Jira Token Control',
    'group' => 'jira_token',
    'name' => 'jira_connected',
    'type' => 'Boolean',
    'add' => '4.4',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'If we have succesfully connected a JIRA instance',
    'title' => 'Jira Connected',
    'help_text' => '',
    'default' => false,
  ),
  'jira_oauth_state' => array(
    'group_name' => 'Jira Token Control',
    'group' => 'jira_token',
    'name' => 'jira_oauth_state',
    'type' => 'String',
    'add' => '4.4',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Temporary setting to store unique state code',
    'title' => 'Temporary OAuth State Code',
    'help_text' => '',
    'default' => false,
  ),

)

?>