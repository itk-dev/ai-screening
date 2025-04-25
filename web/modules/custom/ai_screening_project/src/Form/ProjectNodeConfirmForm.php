<?php

namespace Drupal\ai_screening_project\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form before saving a project node.
 */
class ProjectNodeConfirmForm extends ConfirmFormBase {
  use AutowireTrait;

  /**
   * The node being edited.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected Node $node;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *   The temp store factory service.
   */
  public function __construct(
    private readonly PrivateTempStoreFactory $tempStoreFactory
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'project_node_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Node $node = NULL) {
    $this->node = $node;

    // Get the stored form values from tempstore
    $tempstore = $this->tempStoreFactory->get('ai_screening_project_deactivate_confirm');
    $form_values = $tempstore->get('project_form_values_' . $node->id());

    if (empty($form_values)) {
      // If no form values in tempstore, redirect back to the edit form
      $this->messenger()->addError($this->t('No pending changes found. Please try again.'));
      return $this->redirect('entity.node.edit_form', ['node' => $node->id()]);
    }

    // Store form values in the form for later use in submitForm
    $form_state->set('stored_form_values', $form_values);

    // Add other important fields to the summary as needed
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to save the project "@title"?', ['@title' => $this->node->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('entity.node.edit_form', ['node' => $this->node->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Deactivating a project will disable further work on the project and archive it. Reactivation of the project requires site administrative privileges.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Save project');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the stored form values
    $form_values = $form_state->get('stored_form_values');
    
    // Load the node entity
    $node = Node::load($this->node->id());

    // Apply the stored form values to the node
    foreach ($form_values as $field_name => $value) {
      if ($node->hasField($field_name)) {
        $node->set($field_name, $value);
      }
    }

    // Save the node
    $node->save();
    
    // Clear the tempstore data
    $tempstore = $this->tempStoreFactory->get('ai_screening_project_deactivate_confirm');
    $tempstore->delete('project_form_values_' . $node->id());
    
    // Set a success message
    $this->messenger()->addStatus($this->t('Project "@title" has been updated.', ['@title' => $node->label()]));
    
    // Redirect to the node view page
    $form_state->setRedirectUrl(Url::fromRoute('entity.node.canonical', ['node' => $node->id()]));
  }
}