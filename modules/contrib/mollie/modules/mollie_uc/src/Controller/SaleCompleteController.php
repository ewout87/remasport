<?php

namespace Drupal\mollie_uc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\uc_cart\CartManagerInterface;
use Drupal\uc_order\Entity\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SaleCompleteController.
 *
 * @package Drupal\mollie_uc\Controller
 */
class SaleCompleteController extends ControllerBase {

  /**
   * Cart manager.
   *
   * @var \Drupal\uc_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * SaleCompleteController constructor.
   *
   * @param \Drupal\uc_cart\CartManagerInterface $cartManager
   *   Cart manager.
   */
  public function __construct(CartManagerInterface $cartManager) {
    $this->cartManager = $cartManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uc_cart.manager')
    );
  }

  /**
   * Completes a sale and show the sales complete page to the user.
   *
   * @param \Drupal\uc_order\Entity\Order $order
   *   Order for which to complete the sale.
   *
   * @return array
   *   Renderable array for the sales complate page.
   */
  public function completeSale(Order $order): array {
    // Create a real order from the cart.
    return $this->cartManager->completeSale($order);
  }

}
