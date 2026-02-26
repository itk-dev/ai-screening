<?php

namespace Drupal\ai_screening_project_track\Computer\ProjectTrackToolComputer;

use Dom\HTMLDocument;
use Dom\XPath;
use Drupal\ai_screening_project_track\Evaluation;
use Drupal\ai_screening_project_track\Plugin\WebformElement\YesNoStop;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\ai_screening_project_track\Status;
use Drupal\webform\Utility\WebformElementHelper;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use const Dom\HTML_NO_DEFAULT_NS;

/**
 * Yes/no stop computer.
 */
final class YesNoStopProjectTrackToolComputer extends AbstractProjectTrackToolComputer {

  public function __construct(
    #[Autowire(service: 'webform_submission.conditions_validator')]
    private readonly WebformSubmissionConditionsValidatorInterface $validator,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function supports(ProjectTrackToolInterface $tool, WebformSubmissionInterface $submission): bool {
    return YesNoStop::ID === $this->getComputableElementType($tool, $submission);
  }

  /**
   * {@inheritdoc}
   */
  public function compute(ProjectTrackToolInterface $tool, WebformSubmissionInterface $submission): void {
    $evaluation = $this->computeEvaluation($tool, $submission);

    $toolData = $tool->getToolData();
    $toolData['evaluation'] = $evaluation->value;
    $tool->setToolData($toolData);

    $tool->setProjectTrackToolStatus(Status::IN_PROGRESS);

    if (!empty($tool->getToolData()['history'])) {
      $projectTrack = $tool->getProjectTrack();
      if (Status::NEW === $projectTrack->getProjectTrackStatus()) {
        $projectTrack->setProjectTrackStatus(Status::IN_PROGRESS);
        $projectTrack->save();
      }
    }
  }

  /**
   * Compute evaluation.
   *
   * 1: Hvis der forefindes Krav i rapporten, så skal scoringen være RØD
   * 2: Hvis der forefindes ubesvarede spørgsmål, så skal scoringen være GUL
   * 3: Hvis der forefindes Opgaver i rapporten, så skal scoringen være GUL.
   *
   * Scoringen kan således kun være GRØN, såfremt alle spørgsmål er besvarede,
   * og rapporten ikke indeholder hverken Krav eller Opgaver
   */
  private function computeEvaluation(
    ProjectTrackToolInterface $tool,
    WebformSubmissionInterface $submission,
  ): Evaluation {
    $elements = $this->getElementsByType($submission->getWebform(), YesNoStop::ID);
    // Keep only visible elements.
    $elements = array_filter($elements, function (array $element) use ($submission) {
      $states = WebformElementHelper::getStates($element);
      if (isset($states['visible'])) {
        return TRUE === $this->validator->validateConditions($states['visible'], $submission);
      }

      return TRUE;
    });
    $responses = array_filter($submission->getData(),
      static fn(mixed $value) => in_array($value, [YesNoStop::VALUE_NO, YesNoStop::VALUE_YES], TRUE));

    // Refuse if a stop value has been selected.
    foreach ($responses as $key => $response) {
      if ($element = ($elements[$key] ?? NULL)) {
        if ($response === ($element['#' . YesNoStop::ELEMENT_STOP_VALUE] ?? NULL)) {
          return Evaluation::REFUSED;
        }
      }
    }

    if (count($elements) === count($responses)) {
      // All questions have been answered.
      $requirements = $this->getRequirements($elements, $responses);

      if (!isset($requirements[self::CLASS_NAME_TASK])
        && !isset($requirements[self::CLASS_NAME_STOP])) {
        return Evaluation::APPROVED;
      }
    }

    return Evaluation::UNDECIDED;
  }

  private const string CLASS_NAME_TASK = 'task';
  private const string CLASS_NAME_STOP = 'stop';
  private const string CLASS_NAME_RULE = 'rule';
  private const string CLASS_NAME_CONSIDERATION = 'consideration';

  /**
   * Get requirements from texts on elements.
   *
   * @return array<string, <string, string[]>>
   *   An array keys by class name.
   *   Each values is an array of texts keyed by element key.
   */
  private function getRequirements(array $elements, array $responses): array {
    $requirements = [];
    foreach ($elements as $key => $element) {
      $response = $responses[$key] ?? NULL;
      $textKey = match ($response) {
        ($element['#' . YesNoStop::ELEMENT_STOP_VALUE] ?? NULL) => YesNoStop::ELEMENT_TEXT_STOP,
        YesNoStop::VALUE_YES => YesNoStop::ELEMENT_TEXT_YES,
        YesNoStop::VALUE_NO => YesNoStop::ELEMENT_TEXT_NO,
        default => NULL,
      };
      if ($text = ($element['#' . $textKey] ?? NULL)) {
        $dom = HTMLDocument::createFromString($text, options: HTML_NO_DEFAULT_NS | \LIBXML_NOERROR | \LIBXML_HTML_NOIMPLIED);
        $xpath = new XPath($dom);
        foreach ([
          self::CLASS_NAME_CONSIDERATION,
          self::CLASS_NAME_RULE,
          self::CLASS_NAME_STOP,
          self::CLASS_NAME_TASK,
        ] as $className) {
          $nodes = $xpath->query(sprintf('//div[contains(concat(" ", normalize-space(@class), " "), " %s ")]',
            $className));
          foreach ($nodes as $node) {
            $requirements[$className][$key][] = $dom->saveHtml($node);
          }
        }
      }
    }

    return $requirements;
  }

}
