<?php

namespace Drupal\localgov_openreferral\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Property Mapping form.
 *
 * @property \Drupal\localgov_openreferral\Entity\PropertyMappingInterface $entity
 */
class PropertyMappingForm extends EntityForm {

  /**
   * Entity Bundle Information service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityBundleInfo;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeBundleInfoInterface $entity_bundle_info) {
    $this->entityBundleInfo = $entity_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $form['id'] = [
      '#type' => 'markup',
      '#markup' => $this->entity->id(),
    ];

    if ($this->entity->isNew()) {
      $bundle_info = $this->entityBundleInfo->getAllBundleInfo();
      $form['entity_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Entity type'),
        '#description' => $this->t('Entity type mapped.'),
        '#options' => array_combine(array_keys($bundle_info), array_keys($bundle_info)),
        '#required' => TRUE,
        '#disabled' => !$this->entity->isNew(),
        '#ajax' => [
          'callback' => [$this, 'getBundles'],
          'event' => 'change',
          'disable-refocus' => FALSE,
          'wrapper' => 'mapped-bundle',
          'progress' => [
            'type' => 'throbber',
            'message' => NULL,
           ],
        ],
      ];

      $form['bundle'] = [
        '#type' => 'select',
        '#title' => $this->t('Bundle'),
        '#description' => $this->t('Bundle mapped'),
        '#options' => ['' => ''],
        '#required' => TRUE,
        '#prefix' => '<div id="mapped-bundle">',
        '#suffix' => '</div>',
        '#disabled' => !$this->entity->isNew(),
        // If a value has been previously selected the render array replacement
        // doesn't reset it, and causes an invalid selection error.
        '#validated' => TRUE,
      ];
    }
    else {
      $entity_type = $this->entity->mappedEntityType();
      $form['entity_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Entity type'),
        '#default_value' => $entity_type,
        '#options' => [$entity_type => $entity_type],
        '#required' => TRUE,
        '#disabled' => TRUE,
      ];
      $bundle = $this->entity->mappedBundle();
      $form['bundle'] = [
        '#type' => 'select',
        '#title' => $this->t('Bundle'),
        '#default_value' => $bundle,
        '#options' => [$bundle => $bundle],
        '#required' => TRUE,
        '#disabled' => TRUE,
      ];
    }

    $form['public_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Open Referral type'),
      '#default_value' => $this->entity->getPublicType(),
      // @todo extend this list as we know they normalize fine.
      //   Move to a central location rather than tucked away here.
      '#options' => [
        'organization' => 'organization',
        'service' => 'service',
        'location' => 'location',
        'taxonomy' => 'taxonomy',
      ],
      '#required' => TRUE,
    ];

    $form['public_datatype'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Data type'),
      '#description' => $this->t('For taxonomy terms <a href="@curies">Open Referral has guidance</a>.', ['@curies' => 'https://developers.openreferraluk.org/UseOfTaxonomies/#curies-to-use']),
      '#states' => [
        'visible' => [
          ':input[name="public_type"]' => ['value' => 'taxonomy'],
        ],
      ],
      '#size' => 45,
      '#maxlength' => 60,
    ];

    $form['mapping'] = [
      '#type' => 'table',
      '#caption' => $this->t('Field mapping'),
      '#header' => [
        $this->t('Drupal field'),
        $this->t('Open Referral property'),
      ], 
    ];
    $delta = 0;
    foreach ($this->entity->getMapping('default') as $delta => $mapping) {
      $form['mapping'][$delta]['field_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Drupal field'),
        '#title_display' => 'invisible',
        '#default_value' => $mapping['field_name'],
      ];
      $form['mapping'][$delta]['public_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Open Referral property'),
        '#title_display' => 'invisible',
        '#default_value' => $mapping['public_name'],
      ];
    }
    $delta++;
    $form['mapping'][$delta]['field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Drupal field'),
      '#title_display' => 'invisible',
    ];
    $form['mapping'][$delta]['public_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Open Referral property'),
      '#title_display' => 'invisible',
    ];

    return $form;
  }

  public function getBundles(array &$form, FormStateInterface $form_state) {
    if ($entity_type = $form_state->getValue('entity_type')) {
      $bundle_info = $this->entityBundleInfo->getAllBundleInfo();
      $bundle_options = array_combine(array_keys($bundle_info[$entity_type]), array_keys($bundle_info[$entity_type]));
      $form['bundle']['#options'] = $bundle_options;
    }

    return $form['bundle'];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    if ($this->entity->isNew() && !empty($form_state->getValue('entity_type')) && !empty($form_state->getValue('bundle'))) {
      $this->entity->setOriginalId($form_state->getValue('entity_type') . '.' . $form_state->getValue('bundle'));
    }
    $result = parent::save($form, $form_state);
    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new property mapping %label.', $message_args)
      : $this->t('Updated property mapping %label.', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    $entity = parent::buildEntity($form, $form_state);

    $mapping = [];
    // @todo array filter and validate before it gets here.
    foreach ($form_state->getValue('mapping') as $row) {
      if (!empty($row['field_name']) && !empty($row['public_name'])) {
        $mapping[] = [
          'field_name' => $row['field_name'],
          'public_name' => $row['public_name'],
        ];
      }
    }
    $entity->setMapping($mapping);

    return $entity;
  }

}
