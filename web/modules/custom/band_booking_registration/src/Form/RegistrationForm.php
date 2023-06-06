<?php

namespace Drupal\band_booking_registration\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the registration entity edit forms.
 */
class RegistrationForm extends ContentEntityForm {

  public function buildForm(array $form, FormStateInterface $form_state, $registration = null)
  {
    // Pass registration entity to storage (case of getForm).
    if ($registration) {
      $storage = $form_state->getStorage();
      $storage['registration'] = $registration;
      $form_state->setStorage($storage);
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New registration %label has been created.', $message_arguments));
        $this->logger('band_booking_registration')->notice('Created new registration %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The registration %label has been updated.', $message_arguments));
        $this->logger('band_booking_registration')->notice('Updated registration %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.registration.canonical', ['registration' => $entity->id()]);

    return $result;
  }

}
