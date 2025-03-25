<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\UncacheableDependencyTrait;
use Drupal\Core\Plugin\ContainerFactoryAutowireTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a frontpage help text block.
 */
#[Block(
  id: 'ai_screening_frontpage_help_text',
  admin_label: new TranslatableMarkup('Frontpage help text'),
  category: new TranslatableMarkup('Custom'),
)]
final class FrontpageHelpTextBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use ContainerFactoryAutowireTrait;
  use UncacheableDependencyTrait;

  /**
   * Constructor.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected StateInterface $state,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'frontpage_help_text_block',
      '#data' => [
        'help_text' => $this->state->get('ai_screening_frontpage_help', ''),
      ],
    ];
  }

}
