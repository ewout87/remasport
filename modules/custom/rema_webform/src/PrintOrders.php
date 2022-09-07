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
    $mpdf = new Mpdf([
    'setAutoTopMargin' => 'pad',
    'setAutoBottomMargin' => 'pad',
    ]);
    $batchSize = 100;
    $batches = array_chunk($submission_data, $batchSize);
    foreach($batches as $batch) {
      $html = $this->generateHtml($batch, $node);
      $mpdf->WriteHTML($html, 2);
    }
    
    $destination = DRUPAL_ROOT . '/sites/default/files/webforms';
    $filename = $webform_id . date('_d-m-Y_h:i:s') . '.pdf';
    $path = $destination . '/' . $filename;
    $css = file_get_contents('./modules/custom/rema_webform/css/pdf.css');
    $mpdf->WriteHTML($css, 1);
    $mpdf->Output($path, \Mpdf\Output\Destination::FILE);
    return '/sites/default/files/webforms/' . $filename;
  }

  /**
   * 
   */
  public function generateHtml($submission_data, NodeInterface $node) {
    $html = '<html><body>';
    foreach ($submission_data as $key => $order) {
      $html .= '<table>';
      $html .= '<tr><td></td></tr>';
      $html .= '<tr><td>' . $order['first_name'] . $order['name'] . '</td><td></td><td></td><td></td><td></td><td></td><td>'. $node->getTitle() . ': ' . $key . '</td></tr>';
      $html .= '<tr><td>' . $order['mobile'] . '</td></tr>';
      $html .= '<tr><td>' . $order['e_mail'] . '</td></tr>';
      $html .= '<tr><td></td></tr>';
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
            $html .= '<tr class="product-values"><td class="product-title">' . $product_title . '</td>';
            if ($order['bundle' . $product_key . '_size']) {
              $html .= '<td>' . $order['bundle' . $product_key . '_size'] . '</td>';
              $html .= '<td>' . $order['bundle' . $product_key . '_amount'] . '</td>';
              $print_bundle = $order['bundle' . $product_key . '_print'] ? 'Ja*' : 'Neen';
              $html .= '<td>' . $print_bundle . '</td>';
            }
            else {
              $html .= '<td></td><td></td><td></td>';
            }
            
            if ($order['extra' . $product_key . '_size']) {
              $html .= '<td>' . $order['extra' . $product_key . '_size'] . '</td>';
              $html .= '<td>' . $order['extra' . $product_key . '_amount'] . '</td>'; 
              $print_extra = $order['extra' . $product_key . '_print'] ? 'Ja*' : 'Neen';
              $html .= '<td>' . $print_extra . '</td>';
            }
            else {
              $html .= '<td></td><td></td><td></td>';
            }
            $html .= '</tr>';
          }
        }
      }

      $html .= '<tr><td></td></tr>';
      if ($order['print_name']) {
        $html .= '<tr><td>*' . $order['print_name'] . '</td></tr>';
      }
      $html .= '<tr><td></td></tr></table>';
    }
    $html .= '</body></html>';
    
    return $html;
  }
}