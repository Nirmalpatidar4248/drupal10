<?php

namespace Drupal\portfolio\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\file\Entity\File;

class PortfolioForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'portfolio_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['firstname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
    ];
    $form['lastname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    ];
    $form['profile_picture'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Profile Picture'),
      '#upload_location' => 'public://profile_pictures/',
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
      ],
    ];
    $form['mobile_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile Number'),
      '#required' => TRUE,
    ];
    $form['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address'),
      '#required' => TRUE,
    ];
    $form['gender'] = [
      '#type' => 'select',
      '#title' => $this->t('Gender'),
      '#options' => ['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'],
      '#required' => TRUE,
    ];
    $form['birth_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Birth Date'),
      '#required' => TRUE,
    ];
    $form['short_bio'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Short Bio'),
      '#required' => TRUE,
    ];

    // Projects (Paragraph).
    $form['projects'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Projects'),
    ];

    $form['projects']['project_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Project Title'),
      '#required' => TRUE,
    ];
    $form['projects']['project_images'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Project Images'),
      '#upload_location' => 'public://project_images/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
      ],
      '#multiple' => TRUE,
    ];
    $form['projects']['project_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Project Description'),
      '#required' => TRUE,
    ];
    $form['projects']['project_link'] = [
      '#type' => 'url',
      '#title' => $this->t('Project Website Link'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit Portfolio'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Create project paragraph.
    $paragraph = Paragraph::create([
      'type' => 'projects',
      'field_titlee' => $form_state->getValue('project_title'),
      'field_description' => $form_state->getValue('project_description'),
      'field_link' => $form_state->getValue('project_link'),
    ]);

    // Handle multiple image uploads.
    $project_images = $form_state->getValue('project_images');
    if (!empty($project_images)) {
      foreach ($project_images as $fid) {
        $file = File::load($fid);
        $file->setPermanent();
        $file->save();
        $paragraph->get('field_images')->appendItem($file);
      }
    }
    $paragraph->save();
    // dd($form_state->getValue('birth_date'));  die;

    // Create portfolio node.
    $node = Node::create([
      'type' => 'port',
      'title' => $form_state->getValue('firstname') . ' ' . $form_state->getValue('lastname'),
      'field_firstname' => $form_state->getValue('firstname'),
      'field_lastname' => $form_state->getValue('lastname'),
      'field_email' => $form_state->getValue('email'),
      'field_mobile_number' => $form_state->getValue('mobile_number'),
      'field_address' => $form_state->getValue('address'),
      'field_gender' => $form_state->getValue('gender'),
      'field_birth_date' => $form_state->getValue('birth_date'),
      'field_short_bio' => $form_state->getValue('short_bio'),
      'field_projects' => [$paragraph],
    ]);

    // Handle profile picture upload.
    $profile_picture_fid = $form_state->getValue('profile_picture')[0];
    // dd($profile_picture_fid);
    if ($profile_picture_fid) {
      $file = File::load($profile_picture_fid);
      // dd($file);
      $file->setPermanent();
      $file->save();
      $node->set('field_profile_picture', $file);
    }

    $node->save();

    \Drupal::messenger()->addMessage($this->t('Portfolio created successfully.'));
  }
}
