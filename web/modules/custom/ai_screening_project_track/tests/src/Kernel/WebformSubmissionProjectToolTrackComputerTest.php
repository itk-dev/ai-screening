<?php

namespace Drupal\Tests\ai_screening_project_track\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\ai_screening_project\Helper\ProjectHelper;
use Drupal\ai_screening_project_track\Computer\WebformSubmissionProjectTrackToolComputer;
use Drupal\ai_screening_project_track\Entity\ProjectTrack;
use Drupal\ai_screening_project_track\Entity\ProjectTrackTool;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\ai_screening_project_track\Status;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Tests for WebformSubmissionProjectToolTrackComputer.
 */
final class WebformSubmissionProjectToolTrackComputerTest extends KernelTestBase {
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
  public function testWebformSubmissionProjectTrackToolComputer(): void {
    $computer = new WebformSubmissionProjectTrackToolComputer();

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

    return ProjectTrackTool::create([
      'project_id' => $track,
    ] + $values);
  }

  /**
   * Create webform submission.
   */
  private function createWebformSubmission(array $data): WebformSubmissionInterface {
    $webform = Webform::create([
      'id' => __METHOD__,
    ]);

    return WebformSubmission::create([
      'webform' => $webform,
      'data' => $data,
    ]);
  }

}
