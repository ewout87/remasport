<?php

namespace Drupal\codimth_custom_action\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * create custom action
 *
 * @Action(
 *   id = "download_orders_action",
 *   label = @Translation("Download orders"),
 *   type = "node"
 * )
 */
class DownloadOrdersAction extends ActionBase {

    /**
     * {@inheritdoc}
     */
    public function execute($node = NULL) {
        if ($node) {
            // TODO: export your node here
            \Drupal::messenger()->addStatus('node is exported ');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
        /** @var \Drupal\node\NodeInterface $object */
        // TODO: write here your permissions
        $result = $object->access('create', $account, TRUE);
        return $return_as_object ? $result : $result->isAllowed();
    }

}