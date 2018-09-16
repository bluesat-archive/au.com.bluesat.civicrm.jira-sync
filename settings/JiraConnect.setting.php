<?php
/**
 * Created by IntelliJ IDEA.
 * User: hjed
 * Date: 9/09/18
 * Time: 6:04 PM
 */

return array(
    'jira_cloud_id' => array(
      'group_name' => 'Jira Settings',
      'group' => 'jira_settings',
      'name' => 'jira_cloud_id',
      'type' => 'String',
      'add' => '4.4',
      'is_domain' => 1,
      'is_contact' => 0,
      'description' => 'JIRA "cloudid" for the domain we are connected to',
      'title' => 'JIRA CloudID',
      'help_text' => '',
      'default' => false,
    ),
  ) +
  CRM_OAuthSync_Settings::generateSettings('jira', 'JIRA');

?>