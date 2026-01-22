<?php

namespace Drupal\ai_screening_fixtures_base\Fixture;

use Drupal\ai_screening_fixtures_base\Helper\Helper;
use Drupal\ai_screening_project\Helper\ProjectHelper;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Page fixture.
 *
 * @package Drupal\ai_screening_fixtures_base\Fixture
 */
class ProjectFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  public final const int EXTRA_PROJECTS = 20;

  /**
   * Constructor.
   */
  public function __construct(
    private readonly Helper $helper,
  ) {
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function load(): void {
    $owner = $this->getReference('user:administrator');
    if (!($owner instanceof UserInterface)) {
      return;
    }

    $this->helper->userLogin($owner);

    $node = Node::create([
      'type' => 'project',
      'title' => 'Ordinary screening',
      'status' => NodeInterface::PUBLISHED,
      'field_department' => ['target_id' => $this->getReference('department:Department A')->id()],
      'field_description' => [
        'value' => 'En ny screening',
        'format' => 'plain_text',
      ],
      ProjectHelper::FIELD_CORRUPTED => 0,
    ]);
    $node->setOwner($owner);

    $this->addReference('project:Ordinary screening', $node);
    $node->save();

    $node = Node::create([
      'type' => 'project',
      'title' => 'Screening with multiple departments',
      'status' => NodeInterface::PUBLISHED,
      'field_department' => [
        ['target_id' => $this->getReference('department:Department B')->id()],
        ['target_id' => $this->getReference('department:Department C')->id()],
      ],
      'field_description' => [
        'value' => 'Tværgående screening',
        'format' => 'plain_text',
      ],
      ProjectHelper::FIELD_CORRUPTED => 0,
    ]);
    $node->setOwner($owner);
    $node->save();

    $node = Node::create([
      'type' => 'project',
      'title' => 'Finished screening',
      'status' => NodeInterface::PUBLISHED,
      'field_department' => ['target_id' => $this->getReference('department:Department C')->id()],
      'field_description' => [
        'value' => 'En afsluttet screening',
        'format' => 'plain_text',
      ],
      'field_project_state' => 'finished',
      ProjectHelper::FIELD_CORRUPTED => 0,
    ]);
    $node->setOwner($owner);

    $this->addReference('project:Finished screening', $node);
    $node->save();

    $node = Node::create([
      'type' => 'project',
      'title' => 'Corrupted screening',
      'status' => NodeInterface::NOT_PUBLISHED,
      'field_department' => ['target_id' => $this->getReference('department:Department C')->id()],
      'field_description' => [
        'value' => 'En ødelagt screening bør slettes med cron.',
        'format' => 'plain_text',
      ],
      'corrupted' => 1,
    ]);
    $node->setOwner($owner);

    $this->addReference('project:Corrupted screening', $node);
    $node->save();

    for ($projectCount = 1; $projectCount <= self::EXTRA_PROJECTS; $projectCount++) {
      $label = 'Screening - ' . $projectCount;
      $node = Node::create([
        'type' => 'project',
        'title' => $label,
        'status' => NodeInterface::PUBLISHED,
        'field_department' => ['target_id' => $this->getReference('department:Department B')->id()],
        'field_description' => [
          'value' => 'Screeningsnummer ' . $projectCount,
          'format' => 'plain_text',
        ],
        'corrupted' => 0,
      ]);

      $node->setOwner($owner);

      $this->addReference('project:' . $label, $node);
      $node->save();

      // Update timestamps on project.
      $node
        ->setCreatedTime((new \DateTimeImmutable(sprintf('now -%d days', $projectCount + 9)))->getTimestamp())
        ->setChangedTime((new \DateTimeImmutable(sprintf('now -%d days', $projectCount + 8)))->getTimestamp());
      $node->save();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      TermDepartmentFixture::class,
      ProjectTrackTermFixture::class,
      UserFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['nodes'];
  }

}
