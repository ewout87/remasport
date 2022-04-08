<?php

namespace Drupal\rema_webform\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * An example controller.
 */
class WebformController extends ControllerBase {

  /**
   * Returns a render-able array for a test page.
   */
  public function exportPdf() {
    $build = [
      '#markup' => $this->t('Hello World!'),
    ];
    return $build;
  }

}