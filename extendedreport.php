<?php

require_once 'extendedreport.civix.php';
use CRM_Extendedreport_ExtensionUtil as E;

/**
 * Implementation of hook_civicrm_config
 */
function extendedreport_civicrm_config(&$config) {
  _extendedreport_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function extendedreport_civicrm_xmlMenu(&$files) {
  _extendedreport_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function extendedreport_civicrm_install() {
  return _extendedreport_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function extendedreport_civicrm_uninstall() {
  return _extendedreport_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function extendedreport_civicrm_enable() {
  return _extendedreport_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function extendedreport_civicrm_disable() {
  return _extendedreport_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function extendedreport_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _extendedreport_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function extendedreport_civicrm_angularModules(&$angularModules) {
  _extendedreport_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function extendedreport_civicrm_managed(&$entities) {
  return _extendedreport_civix_civicrm_managed($entities);
}

/**
 * Check version is at least as high as the one passed.
 *
 * @param string $version
 *
 * @return bool
 */
function extendedreport_version_at_least($version) {
  $codeVersion = explode('.', CRM_Utils_System::version());
  if (version_compare($codeVersion[0] . '.' . $codeVersion[1], $version) >= 0) {
    return TRUE;
  }
  return FALSE;
}

function extendedreport_civicrm_tabset($tabsetName, &$tabs, $context) {
  $reports = civicrm_api3('ReportInstance', 'get', array('form_values' => array('LIKE' => '%contact_dashboard_tab";s:1:"1";%')));

  foreach ($reports['values'] as $report) {
    $tabs['report_' . $report['id']] = array(
      'title' => ts($report['title']),
      'url' => CRM_Utils_System::url( 'civicrm/report/instance/' . $report['id'], array(
          'log_civicrm_address_op' => 'in',
          'contact_id_value' => $context['contact_id'],
          'contact_id' => $context['contact_id'],
          'output' => 'html',
          'force' => 1,
          'section' => 2,
          'weight' => 70,
        )
      )
    );
  }

}

function extendedreport_civicrm_pageRun(&$page) {

  if (get_class($page) === 'CRM_Contact_Page_View_Summary') {
    if (($contactID = $page->getVar('_contactId')) !== FALSE) {
      $page->assign('apiOptions', ['metadata' => ['labels']]);
      $page->assign('contactID', $contactID);
    }
  }
}

/**
 * @param $op
 * @param $objectName
 * @param $objectId
 * @param $objectRef
 */
function extendedreport__civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName === 'ReportInstance') {
    CRM_Extendedreport_Page_Inline_ExtendedReportlets::flushReports();
  }
}

/**
 * Implements hook_civicrm_contactSummaryBlocks().
 *
 * @link https://github.com/civicrm/org.civicrm.contactlayout
 */
function extendedreport_civicrm_contactSummaryBlocks(&$blocks) {

  $reports = CRM_Extendedreport_Page_Inline_ExtendedReportlets::getReportsToDisplay();

  if (empty($reports)) {
    return;
  }
  // Provide our own group for this block to visually distinguish it on the contact summary editor palette.
  $blocks += [
    'extendedreports' => [
      'title' => ts('Extended report dashlets'),
      'icon' => 'fa-table',
      'blocks' => [],
    ]
  ];
  foreach ($reports as $report) {
    $blocks['extendedreports']['blocks']['report_' . $report['id']] = [
      'title' => ts('Report: ') . $report['title'],
      'tpl_file' => 'CRM/ExtendedReport/Page/Inline/ExtendedReport.tpl',
      'edit' => FALSE,
      'template_variables' => ['id' => $report['id']],
      'collapsible' => TRUE,
    ];
  }

}
