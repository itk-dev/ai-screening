<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Helper;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Logger\LoggerChannel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;

/**
 * Abstract helper.
 */
abstract class AbstractHelper implements LoggerAwareInterface {
  use LoggerAwareTrait;
  use LoggerTrait;

  /**
   * Constructor.
   */
  public function __construct(
    LoggerChannel $logger,
  ) {
    $this->setLogger($logger);
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public function log($level, string|\Stringable $message, array $context = []): void {
    $this->logger->log($level, $message, $context);
  }

  /**
   * Get absolute URL to entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string|null $rel
   *   See \Drupal\Core\Entity\EntityInterface::toUrl().
   * @param array $options
   *   See \Drupal\Core\Entity\EntityInterface::toUrl() for
   *   the available options.
   *
   *   Unless set, 'absolute' will default to true.
   *
   * @see EntityInterface::toUrl()
   */
  public function getUrl(EntityInterface $entity, ?string $rel = NULL, array $options = []): string {
    $url = $entity->toUrl($rel, $options + ['absolute' => TRUE]);

    return $url->toString(TRUE)->getGeneratedUrl();
  }

  /**
   * Log exception.
   */
  protected function logException(\Exception $exception, string $location, array $context = []): void {
    $this->error('Exception (@type) in @location: @message', [
      '@type' => $exception::class,
      '@location' => $location,
      '@message' => $exception->getMessage(),
      'exception' => $exception,
      'context' => $context,
    ]);
  }

}
