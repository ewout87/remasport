<?php

namespace Drupal\rema_webform;

use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Mpdf\Mpdf;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
  public function generatePdf($submissions, $webform_id, NodeInterface $node) {
    $mpdf = new Mpdf([
    'setAutoTopMargin' => 'pad',
    'setAutoBottomMargin' => 'pad',
    ]);
    $batchSize = 100;
    $batches = array_chunk($submissions, $batchSize);
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
  public function generateHtml($submissions, NodeInterface $node) {
    $html = '<html><body>';
    $bundle_products = $node->get('field_bundle_products')->getValue();
    $products = [];

    if (empty($products)) {
      return;
    }

    foreach ($bundle_products_ids as $bundle_product) {
      $product_id = $bundle_product['target_id'];
      $product = Node::load($product_id);
      if ($product instanceof NodeInterface) {
        $product_title = $product->getTitle();
        $product_id = $product->id();
        $product_size = explode(': ', $data['bundle' . $product_key . '_size'])[1] ?? $data['bundle' . $product_key . '_size'];
        $products['_product_'.$product_id] = ;
      }
    }

    foreach ($submissions as $submission) {
      $data = $submission['data'];
      if ($data['payment_status'] !== 'paid') {
        continue;
      }

      $html .= '<table style="border-collapse: collapse; page-break-inside: avoid; border-bottom: dashed 1px #000000; font-size: 14px;">';
      $html .= '<tr><td style="padding: 7px 5px;"></td></tr>';
      $html .= '<tr><td style="padding: 7px 5px;">' . $data['first_name'] . ' ' . $data['name'] . '</td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td><td>'. $node->getTitle() . '</td><td>'. $submission['sid'] . '</td></tr>';
      $html .= '<tr><td style="padding: 7px 5px;">' . $data['mobile'] . '</td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;">'. $data['payment_status'] . '</td></tr>';
      $html .= '<tr><td style="padding: 7px 5px;">' . $data['e_mail'] . '</td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td><td style="padding: 7px 5px;"></td></tr>';
      $html .= '<tr><td style="padding: 7px 5px;"></td></tr>';
      $html .= '<tr><th style="padding: 7px 5px;">Product</th><th style="padding: 7px 5px;">Maat</th><th style="padding: 7px 5px;">Aantal</th><th style="padding: 7px 5px;">Naam</th><th style="padding: 7px 5px;">Maat</th><th style="padding: 7px 5px;">Aantal</th><th style="padding: 7px 5px;">Naam</th></tr>';

      foreach ($products as $key => $value) {
        if ($data['bundle' . $key . '_size'] || $data['extra' . $key . '_size']) {
          $product_size = explode(': ', $data['bundle' . $product_key . '_size'])[1] ?? $data['bundle' . $product_key . '_size'];
          $html .= '<tr class="product-values"><td style="border: 1px solid; text-align: center; padding: 7px 5px;" class="product-title">' . $product_title . '</td>';
          if ($data['bundle' . $key . '_size']) {
            $html .= '<td style="border: 1px solid; text-align: center; padding: 7px 5px;">' . $product_size . '</td>';
            $html .= '<td style="border: 1px solid; text-align: center; padding: 7px 5px;">' . $data['bundle' . $key . '_amount'] . '</td>';
            $print_bundle = $data['bundle' . $key . '_print'] ? 'Ja' : '';
            $html .= '<td style="border: 1px solid; text-align: center; padding: 7px 5px;">' . $print_bundle . '</td>';
          }
          else {
            $html .= '<td style="border: 1px solid; text-align: center; padding: 7px 5px;"></td><td style="border: 1px solid; text-align: center; padding: 7px 5px;"></td><td style="border: 1px solid; text-align: center; padding: 7px 5px;"></td>';
          }
              
          if ($data['extra' . $key . '_size']) {
            $extra_size = explode(': ', $data['bundle' . $key . '_size'])[1] ?? $data['bundle' . $key . '_size'];
            $html .= '<td style="border: 1px solid; text-align: center; padding: 7px 5px;">' . $extra_size . '</td>';
            $html .= '<td style="border: 1px solid; text-align: center; padding: 7px 5px;">' . $data['extra' . $key . '_amount'] . '</td>'; 
            $print_extra = $data['extra' . $key . '_print'] ? 'Ja' : '';
            $html .= '<td style="border: 1px solid; text-align: center; padding: 7px 5px;">' . $print_extra . '</td>';
          }
          else {
            $html .= '<td style="border: 1px solid; text-align: center; padding: 7px 5px;"></td><td style="border: 1px solid; text-align: center; padding: 7px 5px;"></td><td style="border: 1px solid; text-align: center; padding: 7px 5px;"></td>';
          }
          $html .= '</tr>';
        }
      }

      $html .= '<tr><td style="padding: 7px 5px;"></td></tr>';
      if ($data['print_name']) {
        $html .= '<tr><td style="padding: 7px 5px;">*' . $data['print_name'] . '</td></tr>';
      }
      $html .= '<tr><td style="padding: 7px 5px;"></td></tr></table>';
    }
    $html .= '</body></html>';
    
    return $html;
  }

  /**
   * 
   */
  public function getPriceFormGroupName(NodeInterface $product, $groupame) {
    $size_ranges = $product->get('field_product_size_range')->getValue();
    $price = 0.00;
   
    if (!empty($size_ranges)) {
      foreach($size_ranges as $size_range) {
        $opt_group = Node::load($size_range['target_id']);
        if ($opt_group === NULL) {
          continue;
        }
        
        if ($opt_group->getTitle() === $groupName) {
          $price = (float)$opt_group->get('field_size_price')->getValue()[0]['value'];
        }
      }
    }

    return $price;
  }
}