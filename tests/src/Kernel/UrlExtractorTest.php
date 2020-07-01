<?php

namespace Drupal\Tests\twig_tweak\Kernel;

use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\Tests\TestFileCreationTrait;

/**
 * A test for UrlExtractor.
 *
 * @group twig_tweak
 */
final class UrlExtractorTest extends KernelTestBase {

  use TestFileCreationTrait;

  /**
   * A node to test.
   *
   * @var \Drupal\node\NodeInterface
   */
  private $node;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'twig_tweak',
    'twig_tweak_test',
    'system',
    'views',
    'node',
    'block',
    'image',
    'field',
    'text',
    'media',
    'file',
    'user',
    'filter',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installConfig(['node', 'twig_tweak_test']);
    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('file');
    $this->installEntitySchema('media');

    $test_files = $this->getTestFiles('image');
    //
    $image_file = File::create([
      'uri' => $test_files[0]->uri,
      'uuid' => 'a2cb2b6f-7bf8-4da4-9de5-316e93487518',
      'status' => FILE_STATUS_PERMANENT,
    ]);
    $image_file->save();

    $media_file = File::create([
      'uri' => $test_files[2]->uri,
      'uuid' => '5dd794d0-cb75-4130-9296-838aebc1fe74',
      'status' => FILE_STATUS_PERMANENT,
    ]);
    $media_file->save();

    $media = Media::create([
      'bundle' => 'image',
      'name' => 'Image 1',
      'field_media_image' => ['target_id' => $media_file->id()],
    ]);
    $media->save();

    $node_values = [
      'title' => 'Alpha',
      'type' => 'page',
      'field_image' => [
        'target_id' => $image_file->id(),
      ],
      'field_media' => [
        'target_id' => $media->id(),
      ],
    ];
    $this->node = Node::create($node_values);
  }

  /**
   * Test callback.
   */
  public function testUrlExtractor(): void {

    $extractor = $this->container->get('twig_tweak.url_extractor');
    $base_url = file_create_url('');

    $request = \Drupal::request();
    $absolute_url = "{$request->getScheme()}://{$request->getHost()}/foo/bar.txt";
    $url = $extractor->extractUrl($absolute_url);
    self::assertSame('/foo/bar.txt', $url);

    $url = $extractor->extractUrl($absolute_url, FALSE);
    self::assertSame($base_url . 'foo/bar.txt', $url);

    $url = $extractor->extractUrl('foo/bar.jpg');
    self::assertSame('/foo/bar.jpg', $url);

    $url = $extractor->extractUrl('foo/bar.jpg', FALSE);
    self::assertSame($base_url . 'foo/bar.jpg', $url);

    $url = $extractor->extractUrl('');
    self::assertSame('/', $url);

    $url = $extractor->extractUrl('', FALSE);
    self::assertSame($base_url, $url);

    $url = $extractor->extractUrl(NULL);
    self::assertNull($url);

    $url = $extractor->extractUrl($this->node);
    self::assertNull($url);

    $url = $extractor->extractUrl($this->node->get('title'));
    self::assertNull($url);

    $url = $extractor->extractUrl($this->node->get('field_image')[0]);
    self::assertStringEndsWith('/files/image-test.png', $url);
    self::assertStringNotContainsString($base_url, $url);

    $url = $extractor->extractUrl($this->node->get('field_image')[0], FALSE);
    self::assertStringStartsWith($base_url, $url);
    self::assertStringEndsWith('/files/image-test.png', $url);

    $url = $extractor->extractUrl($this->node->get('field_image')[1]);
    self::assertNull($url);

    $url = $extractor->extractUrl($this->node->get('field_image'));
    self::assertStringEndsWith('/files/image-test.png', $url);

    $url = $extractor->extractUrl($this->node->get('field_image')->entity);
    self::assertStringEndsWith('/files/image-test.png', $url);

    $this->node->get('field_image')->removeItem(0);
    $url = $extractor->extractUrl($this->node->get('field_image'));
    self::assertNull($url);

    $url = $extractor->extractUrl($this->node->get('field_media')[0]);
    self::assertStringEndsWith('/files/image-test.gif', $url);

    $url = $extractor->extractUrl($this->node->get('field_media')[1]);
    self::assertNull($url);

    $url = $extractor->extractUrl($this->node->get('field_media'));
    self::assertStringEndsWith('/files/image-test.gif', $url);

    $url = $extractor->extractUrl($this->node->get('field_media')->entity);
    self::assertStringEndsWith('/files/image-test.gif', $url);

    $this->node->get('field_media')->removeItem(0);
    $url = $extractor->extractUrl($this->node->get('field_media'));
    self::assertNull($url);
  }

}
