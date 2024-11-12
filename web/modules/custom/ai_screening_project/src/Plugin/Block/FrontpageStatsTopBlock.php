<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryAutowireTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ai_screening_project\Helper\BlockHelper;

/**
 * Provides a frontpage stats top block.
 */
#[Block(
  id: 'ai_screening_frontpage_stats_top',
  admin_label: new TranslatableMarkup('Frontpage stats top'),
  category: new TranslatableMarkup('Custom'),
)]
final class FrontpageStatsTopBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use ContainerFactoryAutowireTrait;

  /**
   * Constructor.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected BlockHelper $blockHelper,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'stats_top_block',
      '#data' => [
        'stats' => $this->blockHelper->getFrontpageStats(),
      ],
    ];
  }

}
