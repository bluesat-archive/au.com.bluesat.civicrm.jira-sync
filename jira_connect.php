<?php

require_once 'jira_connect.civix.php';
use CRM_JiraConnect_ExtensionUtil as E;
require_once 'CRM_JiraConnect_JiraApiHelper.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function jira_connect_civicrm_config(&$config) {
  _jira_connect_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function jira_connect_civicrm_xmlMenu(&$files) {
  _jira_connect_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function jira_connect_civicrm_install() {
  _jira_connect_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function jira_connect_civicrm_postInstall() {
  _jira_connect_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function jira_connect_civicrm_uninstall() {
  _jira_connect_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function jira_connect_civicrm_enable() {
  _jira_connect_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function jira_connect_civicrm_disable() {
  _jira_connect_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function jira_connect_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _jira_connect_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function jira_connect_civicrm_managed(&$entities) {
  _jira_connect_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function jira_connect_civicrm_caseTypes(&$caseTypes) {
  _jira_connect_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function jira_connect_civicrm_angularModules(&$angularModules) {
  _jira_connect_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function jira_connect_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _jira_connect_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function jira_connect_civicrm_entityTypes(&$entityTypes) {
  _jira_connect_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_pageRun().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 */
function jira_connect_civicrm_pageRun(&$run) {
}


/**
 * Implements hook_civicrm_oauthsync_consent_success().
 *
 * Used to get the connection id
 */
function jira_connect_civicrm_oauthsync_consent_success(&$prefix) {

  $ids = CRM_JiraConnect_JiraApiHelper::retrieveJiraCloudId();
  if(count($ids) > 1) {
    //TODO: handle multiple ids
    echo "Too many ids";
    die();
  } else if(count($ids) == 1) {
    Civi::settings()->set("jira_cloud_id", $ids[0]);
  } else {
    //TODO: handle this
    echo "request failed";
    die();
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
 */
function jira_connect_civicrm_navigationMenu(&$menu) {
  _jira_connect_civix_insert_navigation_menu($menu, 'Administer', array(
    'label' => E::ts('JIRA Settings'),
    'name' => 'JIRA',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _jira_connect_civix_insert_navigation_menu($menu, 'Administer/JIRA', array(
    'label' => E::ts('JIRA API Settings'),
    'name' => 'jira_sync_settings',
    'url' => 'civicrm/jira-connect/config',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _jira_connect_civix_insert_navigation_menu($menu, 'Administer/JIRA', array(
    'label' => E::ts('JIRA Connection'),
    'name' => 'jira_connection',
    'url' => 'civicrm/jira-connect/connection',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _jira_connect_civix_navigationMenu($menu);
}
