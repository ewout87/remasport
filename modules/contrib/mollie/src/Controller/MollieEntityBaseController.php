<?php

namespace Drupal\mollie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\mollie_customers\Entity\Customer;

/**
 * Class MollieEntityBaseController.
 *
 * @package Drupal\mollie\Controller
 */
class MollieEntityBaseController extends ControllerBase {

  /**
   * Returns the title for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   *
   * @return string
   *   Title for the entity.
   */
  protected function entityTitle(EntityInterface $entity): string {
    return $this->t(
      '@type @label',
      [
        '@type' => $entity->getEntityType()->getLabel(),
        '@label' => $entity->label(),
      ]
    );
  }

}
