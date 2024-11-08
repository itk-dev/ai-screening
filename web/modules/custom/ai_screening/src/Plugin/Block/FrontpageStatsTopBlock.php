<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a frontpage stats top block.
 */
#[Block(
  id: 'ai_screening_frontpage_stats_top',
  admin_label: new TranslatableMarkup('Frontpage stats top'),
  category: new TranslatableMarkup('Custom'),
)]
final class FrontpageStatsTopBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
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
