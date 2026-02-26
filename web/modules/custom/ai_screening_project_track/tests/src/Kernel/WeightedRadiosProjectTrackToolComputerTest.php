<?php

namespace Drupal\Tests\ai_screening_project_track\Kernel;

use Drupal\ai_screening_project\Helper\ProjectHelper;
use Drupal\ai_screening_project_track\Computer\ProjectTrackToolComputer\WeightedRadiosProjectTrackToolComputer;
use Drupal\ai_screening_project_track\Entity\ProjectTrack;
use Drupal\ai_screening_project_track\Entity\ProjectTrackTool;
use Drupal\ai_screening_project_track\Helper\ProjectTrackTypeHelper;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\ai_screening_project_track\Status;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Tests for WebformSubmissionProjectToolTrackComputer.
 */
final class WeightedRadiosProjectTrackToolComputerTest extends KernelTestBase {
  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ai_screening_project_track',
    'user',
    'node',
    'taxonomy',
    'webform',
    // Needed to define constants used by webform (like `DRUPAL_DISABLED`)
    'system',
  ];

  /**
   * Test track tool computer.
   *
   * Test that track tool computer computes correctly given a tool and a
   * webform submission.
   */
  public function testWeightedRadiosComputer(): void {
    $computer = new WeightedRadiosProjectTrackToolComputer();

    $tool = $this->createTool([])
      ->setProjectTrackToolStatus(Status::NEW);
    $submission = $this->createWebformSubmission([]);
    $this->assertTrue($computer->supports($tool, $submission));

    $this->assertSame(Status::NEW, $tool->getProjectTrackToolStatus());
    $computer->compute($tool, $submission);
    $this->assertSame(Status::IN_PROGRESS, $tool->getProjectTrackToolStatus());
  }

  /**
   * Create project track tool.
   */
  private function createTool(array $values): ProjectTrackToolInterface {
    $project = Node::create([
      'type' => ProjectHelper::BUNDLE_PROJECT,
    ]);

    $track = ProjectTrack::create([
      'project_id' => $project,
    ]);
    $track->setConfiguration([
      ProjectTrackTypeHelper::CONFIGURATION_KEY_DIMENSIONS => [
        'x', 'y',
      ],
    ]);

    return ProjectTrackTool::create([
      'project_track_id' => $track,
    ] + $values);
  }

  /**
   * Create webform submission.
   */
  private function createWebformSubmission(array $data): WebformSubmissionInterface {
    $webform = Webform::create([
      'id' => __METHOD__,
      'elements' => <<<'YAML'
question:
  '#type': ai_screening_weighted_radios
  '#title': 'Question??'
  '#options':
    '10,0': '1-5 items'
    '7,0': '6-10 items'
    '5,0': '11-15 items'
    '3,0': '16-20 items'
    '1,0': '21+ items'
  '#options__properties': ''
YAML,
    ]);

    return WebformSubmission::create([
      'webform' => $webform,
      'data' => $data,
    ]);
  }

}
