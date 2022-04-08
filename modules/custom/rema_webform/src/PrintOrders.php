<?php

namespace Drupal\rema_webform;

use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Mpdf\Mpdf;

/**
 * Class PrintOrders
 * @package Drupal\rema_webform\Services
 */
class PrintOrders {

  protected $currentUser;

  /**
   * CustomService constructor.
   * @param AccountInterface $currentUser
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }


  /**
   * @return \Drupal\Component\Render\MarkupInterface|string
   */
  public function generatePdf($submission_data, $webform_id, NodeInterface $node) {
    $html = $this->generateHtml($submission_data, $node);
    $destination = DRUPAL_ROOT . '/sites/default/files/webforms';
    $filename = $webform_id . date('_d-m-Y_h:i:s') . '.pdf';
    $path = $destination . '/' . $filename;
    $css = file_get_contents(__DIR__ . '/../../css/pdf.css');
    $mpdf = new Mpdf([
    'setAutoTopMargin' => 'pad',
    'setAutoBottomMargin' => 'pad',
    ]);
    $mpdf->WriteHTML($css, 1);
    $mpdf->WriteHTML($html, 2);
    $mpdf->Output($path, \Mpdf\Output\Destination::FILE);
    return '/sites/default/files/webforms/' . $filename;
  }

  /**
   * 
   */
  public function generateHtml($submission_data, NodeInterface $node) {
    $html = '<html><body>';
    foreach ($submission_data as $order) {
      $html .= '<table>';
      $html .= '<tr><td>' . $order['first_name'] . '</td></tr>';
      $html .= '<tr><td>' . $order['mobile'] . '</td></tr>';
      $html .= '<tr><td>' . $order['e_mail'] . '</td></tr>';
      $html .= '<tr><th>Product</th><th>Maat</th><th>Aantal</th><th>Bedrukking</th><th>Extra</th><th>Aantal</th><th>Bedrukking</th></tr>';
      $products = $node->get('field_bundle_products')->getValue();

      if (empty($products)) {
        return;
      }

      foreach ($products as $product) {
        $product_id = $product['target_id'];
        $product = Node::load($product_id);
        if ($product instanceof NodeInterface) {
          $product_title = $product->getTitle();
          $product_id = $product->id();
          $product_key = '_product_'.$product_id;    
          if ($order['bundle' . $product_key . '_size'] || $order['extra' . $product_key . '_size']) {
            $html .= '<tr><td>' . $product_title . '</td>';
            $html .= '<td>' . $order['bundle' . $product_key . '_size'] . '</td>';
            $html .= '<td>' . $order['bundle' . $product_key . '_amount'] . '</td>';
            $html .= '<td>' . $order['bundle' . $product_key . '_print'] . '</td>';
            $html .= '<td>' . $order['bundle' . $product_key . '_size'] . '</td>';
            $html .= '<td>' . $order['extra' . $product_key . '_amount'] . '</td>'; 
            $html .= '<td>' . $order['extra' . $product_key . '_print'] . '</td></tr>';
          }
        }
      }

      $html .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td>Totaal:</td>€ ' . $order['total_amount'] . '</tr></table><br><hr><br>';
    }
    $html .= '</body></html>';
    
    return $html;
  }
}