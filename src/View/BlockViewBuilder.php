<?php

namespace Drupal\twig_tweak\View;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Block\TitleBlockPluginInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * BlockViewBuilder service.
 */
class BlockViewBuilder {

  /**
   * The plugin.manager.block service.
   *
   * @var \Drupal\Core\Cache\CacheableDependencyInterface
   */
  protected $pluginManagerBlock;

  /**
   * The context repository service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Constructs a BlockViewBuilder object.
   */
  public function __construct(
    CacheableDependencyInterface $plugin_manager_block,
    ContextRepositoryInterface $context_repository,
    ContextHandlerInterface $context_handler,
    AccountInterface $account,
    RequestStack $request_stack,
    RouteMatchInterface $route_match,
    TitleResolverInterface $title_resolver
  ) {
    $this->pluginManagerBlock = $plugin_manager_block;
    $this->contextRepository = $context_repository;
    $this->contextHandler = $context_handler;
    $this->account = $account;
    $this->requestStack = $request_stack;
    $this->routeMatch = $route_match;
    $this->titleResolver = $title_resolver;
  }

  /**
   * Builds the render array for a block.
   *
   * @param string $id
   *   The string of block plugin to render.
   * @param array $configuration
   *   (optional) Pass on any configuration to the plugin block.
   * @param bool $wrapper
   *   (optional) Whether or not use block template for rendering.
   *
   * @return array
   *   A renderable array representing the content of the block.
   */
  public function build(string $id, array $configuration = [], bool $wrapper = TRUE): array {

    $configuration += ['label_display' => BlockPluginInterface::BLOCK_LABEL_VISIBLE];

    /** @var \Drupal\Core\Block\BlockPluginInterface $block_plugin */
    $block_plugin = $this->pluginManagerBlock->createInstance($id, $configuration);

    // Inject runtime contexts.
    if ($block_plugin instanceof ContextAwarePluginInterface) {
      $contexts = $this->contextRepository->getRuntimeContexts($block_plugin->getContextMapping());
      $this->contextHandler->applyContextMapping($block_plugin, $contexts);
    }

    $build = [];
    $access = $block_plugin->access($this->account, TRUE);
    if ($access->isAllowed()) {
      // Title block needs a special treatment.
      if ($block_plugin instanceof TitleBlockPluginInterface) {
        $request = $this->requestStack->getCurrentRequest();
        $title = $this->titleResolver->getTitle($request, $this->routeMatch->getRouteObject());
        $block_plugin->setTitle($title);
      }

      $build['content'] = $block_plugin->build();

      if ($block_plugin instanceof TitleBlockPluginInterface) {
        $build['content']['#cache']['contexts'][] = 'url';
      }

      if ($wrapper && !Element::isEmpty($build['content'])) {
        $build += [
          '#theme' => 'block',
          '#attributes' => [],
          '#contextual_links' => [],
          '#configuration' => $block_plugin->getConfiguration(),
          '#plugin_id' => $block_plugin->getPluginId(),
          '#base_plugin_id' => $block_plugin->getBaseId(),
          '#derivative_plugin_id' => $block_plugin->getDerivativeId(),
        ];
      }
    }

    CacheableMetadata::createFromRenderArray($build)
      ->merge(CacheableMetadata::createFromObject($access))
      ->merge(CacheableMetadata::createFromObject($block_plugin))
      ->applyTo($build);

    if (!isset($build['#cache']['keys'])) {
      $configuration['_wrapper'] = $wrapper;
      $configuration_hash = hash('sha256', serialize($configuration));
      $build['#cache']['keys'] = ['twig_tweak_block', $id, $configuration_hash];
    }

    return $build;
  }

}
