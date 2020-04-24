<?php

namespace Drupal\Tests\twig_tweak\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * A test for BlockViewBuilder.
 *
 * @group twig_tweak
 */
final class BlockViewBuilderTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'twig_tweak',
    'twig_tweak_test',
    'user',
    'system',
    'block',
  ];

  /**
   * Test callback.
   *
   * @see \Drupal\twig_tweak_test\Plugin\Block\FooBlock
   */
  public function testBlockViewBuilder(): void {

    $view_builder = $this->container->get('twig_tweak.block_view_builder');

    // -- Default output.
    $this->setUpCurrentUser(['name' => 'User 1']);
    $build = $view_builder->build('twig_tweak_test_foo');
    $expected_build = [
      'content' => [
        '#markup' => 'Foo',
        '#cache' => [
          'contexts' => ['url'],
          'tags' => ['tag_from_build'],
        ],
      ],
      '#theme' => 'block',
      '#attributes' => [],
      '#contextual_links' => [],
      '#configuration' => [
        'id' => 'twig_tweak_test_foo',
        'label' => '',
        'provider' => 'twig_tweak_test',
        'label_display' => 'visible',
        'content' => 'Foo',
      ],
      '#plugin_id' => 'twig_tweak_test_foo',
      '#base_plugin_id' => 'twig_tweak_test_foo',
      '#derivative_plugin_id' => NULL,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['tag_from_blockAccess'],
        'max-age' => 35,
        'keys' => [
          'twig_tweak_block',
          'twig_tweak_test_foo',
          '9ef25a303eb97a01011ea20c50e731590c000f8bc6196241020fc118cce68180',
        ],
      ],
    ];
    self::assertSame($expected_build, $build);
    self::assertSame('<div>Foo</div>', $this->renderPlain($build));

    // -- Non-default configuration.
    $build = $view_builder->build('twig_tweak_test_foo', ['content' => 'Bar', 'label' => 'Example']);
    $expected_build['content']['#markup'] = 'Bar';
    $expected_build['#configuration']['label'] = 'Example';
    $expected_build['#configuration']['content'] = 'Bar';
    $expected_build['#cache']['keys'][2] = '96b740819b50d416148484351f52de1c894b63cf59af255b502c9399c73b8557';
    self::assertSame($expected_build, $build);
    self::assertSame('<div><h2>Example</h2>Bar</div>', $this->renderPlain($build));

    // -- Without wrapper.
    $build = $view_builder->build('twig_tweak_test_foo', [], FALSE);
    $expected_build = [
      'content' => [
        '#markup' => 'Foo',
        '#cache' => [
          'contexts' => ['url'],
          'tags' => ['tag_from_build'],
        ],
      ],
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['tag_from_blockAccess'],
        'max-age' => 35,
        'keys' => [
          'twig_tweak_block',
          'twig_tweak_test_foo',
          '82b254ef229c95f336386ee50454e0cc56705e97c1991d68fe9afdb1fb401f3a',
        ],
      ],
    ];
    self::assertSame($expected_build, $build);
    self::assertSame('Foo', $this->renderPlain($build));

    // -- Unprivileged user.
    $this->setUpCurrentUser(['name' => 'User 2']);
    $build = $view_builder->build('twig_tweak_test_foo');
    $expected_build = [
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['tag_from_blockAccess'],
        'max-age' => 35,
        'keys' => [
          'twig_tweak_block',
          'twig_tweak_test_foo',
          '9ef25a303eb97a01011ea20c50e731590c000f8bc6196241020fc118cce68180',
        ],
      ],
    ];
    self::assertSame($expected_build, $build);
    self::assertSame('', $this->renderPlain($build));
  }

  /**
   * Renders a render array.
   */
  private function renderPlain(array $build): string {
    $renderer = $this->container->get('renderer');
    return rtrim(preg_replace('#\s{2,}#', '', $renderer->renderPlain($build)));
  }

}
