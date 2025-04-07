<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for site setup page.
 */
final class SiteSetupController extends ControllerBase {

  use AutowireTrait;

  /**
   * Builds the response for showing setup page.
   */
  public function __invoke(Request $request): array {
    return [
      '#theme' => 'site_setup',
      '#data' => [],
    ];
  }

}
