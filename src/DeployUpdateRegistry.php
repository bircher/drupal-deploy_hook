<?php


namespace Drupal\deploy_hook;


use Drupal\Core\Update\UpdateRegistry;

class DeployUpdateRegistry extends UpdateRegistry {
  /**
   * The used hook name.
   *
   * @var string
   */
  protected $updateType = 'deploy';

  /**
   * Unregisters that update functions were executed.
   *
   * This is the inverse of registerInvokedUpdates
   *
   * @param string[] $function_names
   *   The update functions to remove from the list.
   *
   * @return $this
   */
  public function resetInvokedUpdates(array $function_names) {
    $executed_updates = $this->keyValue->get('existing_updates', []);
    $executed_updates = array_diff($executed_updates, $function_names);
    $this->keyValue->set('existing_updates', $executed_updates);

    return $this;
  }

}
