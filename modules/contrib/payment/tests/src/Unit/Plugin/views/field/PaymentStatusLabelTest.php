<?php

namespace Drupal\Tests\payment\Unit\Plugin\views\field;

use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Drupal\payment\Plugin\views\field\PaymentStatusLabel;
use Drupal\Tests\UnitTestCase;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\views\field\PaymentStatusLabel
 *
 * @group Payment
 */
class PaymentStatusLabelTest extends UnitTestCase {

  /**
   * The line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $paymentStatusManager;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\views\field\PaymentStatusLabel
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->paymentStatusManager = $this->createMock(PaymentStatusManagerInterface::class);

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $this->sut = new PaymentStatusLabel($configuration, $plugin_id, $plugin_definition, $this->paymentStatusManager);
    $options = [
      'relationship' => 'none',
    ];
    $view_executable = $this->getMockBuilder(ViewExecutable::class)
      ->disableOriginalConstructor()
      ->getMock();
    $display = $this->getMockBuilder(DisplayPluginBase::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();
    $this->sut->init($view_executable, $display, $options);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->createMock(ContainerInterface::class);
    $map = array(
      array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $sut = PaymentStatusLabel::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(PaymentStatusLabel::class, $sut);
  }

  /**
   * @covers ::render
   */
  public function testRender() {
    $plugin_id = $this->randomMachineName();
    $plugin_label = $this->randomMachineName();

    $plugin_definition = [
      'label' => $plugin_label,
    ];

    $this->paymentStatusManager->expects($this->atLeastOnce())
      ->method('getDefinition')
      ->with($plugin_id)
      ->willReturn($plugin_definition);

    $result_row = new ResultRow();
    $result_row->{$this->sut->field_alias} = $plugin_id;

    $this->assertSame($plugin_label, $this->sut->render($result_row));
  }

}
