<?php

namespace Drupal\ai_screening_project;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\ai_screening_project\Helper\ProjectHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackToolHelper;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Breadcrumb builder.
 */
final class BreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  public function __construct(
    private readonly ProjectHelper $projectHelper,
    private readonly ProjectTrackToolHelper $toolHelper,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match): bool {
    return NULL !== $this->getEntity($route_match);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match): Breadcrumb {
    $breadcrumb = new Breadcrumb();

    $entity = $this->getEntity($route_match);

    /** @var ?NodeInterface $project */
    $project = NULL;
    /** @var ?ProjectTrackInterface $track */
    $track = NULL;
    /** @var \Drupal\ai_screening_project_track\ProjectTrackToolInterface $tool */
    $tool = NULL;

    if ($this->projectHelper->isProject($entity)) {
      $project = $entity;
    }
    elseif ($entity instanceof ProjectTrackInterface) {
      $track = $entity;
      $project = $track->getProject();
    }
    elseif ($entity instanceof ProjectTrackToolInterface) {
      $tool = $entity;
      $track = $tool->getProjectTrack();
      $project = $track->getProject();
    }

    if (NULL !== $project) {
      $breadcrumb->addLink(Link::fromTextAndUrl($this->t('Projects'), Url::fromUri('internal:/projects')));
      if ($project->isNew()) {
        $breadcrumb->addLink(Link::fromTextAndUrl(
          $this->t('Create new project'),
          Url::fromRoute('node.add', ['node_type' => $project->bundle()])
        ));
      }
      else {
        $breadcrumb->addLink($project->toLink());
        $breadcrumb->addCacheableDependency($project);
      }
    }

    if (NULL !== $track) {
      $breadcrumb->addLink($track->toLink());
      $breadcrumb->addCacheableDependency($track);
    }

    if (NULL !== $tool) {
      $toolEntity = $tool->getToolEntity();
      $breadcrumb->addLink($toolEntity->toLink($this->toolHelper->getToolLabel($tool), 'edit-form'));
      $breadcrumb->addCacheableDependency($tool);
    }

    return $breadcrumb;
  }

  /**
   * Get entity from route match.
   *
   * If we don't want to generate a bespoke breadcrumb for an entity we return
   * null.
   *
   * @return \Drupal\node\NodeInterface|ProjectTrackInterface|ProjectTrackToolInterface|null
   *   The entity if any.
   */
  private function getEntity(
    RouteMatchInterface $routeMatch,
  ): NodeInterface|ProjectTrackInterface|ProjectTrackToolInterface|null {
    $routeName = $routeMatch->getRouteName();
    switch ($routeName) {
      case 'node.add':
        $type = $routeMatch->getParameter('node_type');
        if (ProjectHelper::BUNDLE_PROJECT === $type->id()) {
          return $this->createProject();
        }

        break;

      case 'entity.node.canonical':
      case 'entity.node.edit_form':
        $node = $routeMatch->getParameter('node');

        if ($this->projectHelper->isProject($node)) {
          return $node;
        }

        break;

      case 'entity.project_track.canonical':
        $track = $routeMatch->getParameter('project_track');
        if ($track instanceof ProjectTrackInterface) {
          return $track;
        }

        break;

      case 'entity.webform_submission.edit_form':
        $submission = $routeMatch->getParameter('webform_submission');
        if ($submission instanceof WebformSubmissionInterface) {
          $source = $submission->getSourceEntity();
          if ($source instanceof ProjectTrackToolInterface) {
            return $source;
          }
        }

        break;
    }

    return NULL;
  }

  /**
   * Create a new (fake) project for use in breadcrumb trail.
   *
   * @return \Drupal\node\Entity\NodeInterface
   *   The fake project node.
   */
  private function createProject(): NodeInterface {
    return Node::create([
      'type' => ProjectHelper::BUNDLE_PROJECT,
      'label' => __METHOD__,
    ]);
  }

}
