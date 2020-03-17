<?php

namespace Drupal\deploy_hook\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\deploy_hook\DeployUpdateRegistry;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
class DeployHookCommands extends DrushCommands {

  /**
   * @var \Drupal\deploy_hook\DeployUpdateRegistry
   */
  private $registry;

  /**
   * DeployHookCommands constructor.
   *
   * @param \Drupal\deploy_hook\DeployUpdateRegistry $registry
   */
  public function __construct(DeployUpdateRegistry $registry) {
    parent::__construct();
    $this->registry = $registry;
  }

  /**
   * Prints information about pending deploy update hooks.
   *
   * @usage deploy-hook:info
   *   Prints information about pending deploy hooks.
   *
   * @field-labels
   *   module: Module
   *   hook: Hook
   *   description: Description
   * @default-fields module,hook,description
   *
   * @command deploy-hook:info
   * @aliases deploy:info
   *
   * @filter-default-field hook
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   */
  public function info() {
    $updates = $this->registry->getPendingUpdateInformation();
    $rows = [];
    foreach ($updates as $module => $update) {
      if (!empty($update['pending'])) {
        foreach ($update['pending'] as $hook => $description) {
          $rows[] = [
            'module' => $module,
            'hook' => $hook,
            'description' => $description,
          ];
        }
      }
    }

    return new RowsOfFields($rows);
  }

  /**
   * Run pending deploy update hooks.
   *
   * @usage deploy-hook:run
   *   Run pending deploy hooks.
   *
   * @command deploy-hook:run
   * @aliases deploy:run
   */
  public function run() {

    $pending = $this->registry->getPendingUpdateFunctions();
    foreach ($pending as $function) {

      $func = new \ReflectionFunction($function);
      $description = trim(str_replace(["\n", '*', '/'], '', $func->getDocComment()), ' ');
      $this->logger()->info('Performing: ' . $description);
      $sandbox = [];
      // @todo: decide if we want to catch exceptions.
      // @todo: deal with sandbox and repeat calling the function to emulate
      // the core behaviour of hook_post_update_NAME hooks.
      $return = $function($sandbox);
      if (!empty($return)) {
        $this->logger()->notice($return);
      }

      $this->registry->registerInvokedUpdates([$function]);
      $this->logger()->debug('Performed: ' . $description);
    }
  }

  /**
   * Marks a deploy update hook as not having run.
   *
   * During development one often wants to re-run the hook, so this helps
   * re-setting it so that it can be run again.
   *
   * @usage deploy-hook:reset mymodule_deploy_runagain
   *   Unregisters that a deploy hook has run so that it runs again.
   *
   * @param string $hook
   *   The hook name to reset.
   *
   * @command deploy-hook:reset
   * @aliases deploy:reset
   */
  public function reset(string $hook) {
    $this->registry->resetInvokedUpdates([$hook]);
  }

}
