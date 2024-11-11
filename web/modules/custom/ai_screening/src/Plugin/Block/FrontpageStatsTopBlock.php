<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ai_screening\Helper\BlockHelper;
use Drupal\Core\Plugin\ContainerFactoryAutowireTrait;

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
    $a = $this->blockHelper->getFrontpageStats();
    $b = 1;
    return [
      '#theme' => 'stats_top_block',
      '#data' => [
        'accepted' => 0,
        'in_progress' => 0,
        'refused' => 0,
      ],
    ];
  }

}
