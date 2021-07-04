<?php

namespace Drupal\localgov_openreferral\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\TypedData\TypedDataInternalPropertiesHelper;
use Drupal\localgov_openreferral\MappingInformation;

/**
 * Normalizes/denormalizes Drupal content entities into an array structure.
 */
class ContentEntityNormalizer extends NormalizerBase {

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = ['openreferral_json'];

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = ContentEntityInterface::class;

  /**
   * Mapping information service.
   *
   * @var \Drupal\localgov_openreferral\MappingInformation
   */
  protected $mappingInformation;

  /**
   * Normalizer constructor.
   *
   * @param \Drupal\localgov_openreferral\MappingInformation $mapping_information
   *   Mapping information helper service.
   */
  public function __construct(MappingInformation $mapping_information) {
    $this->mappingInformation = $mapping_information;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $property_mapping = $this->mappingInformation->getPropertyMapping($entity->getEntityTypeId(), $entity->bundle());
    $context += [
      'account' => NULL,
    ];
    $context['parents'][] = $this->mappingInformation->getPublicType($entity->getEntityTypeId(), $entity->bundle());

    $attributes = [];
    $object = TypedDataInternalPropertiesHelper::getNonInternalProperties($entity->getTypedData());

    foreach ($property_mapping as $property) {
      $field_items = $object[$property['fieldName']];
      if ($field_items->access('view', $context['account'])) {
        $context['field'] = $property;
        if ($property['publicName'] == '_flatten') {
          $attributes += $this->serializer->normalize($field_items, $format, $context);
        }
        elseif (is_array($property['publicName'])) {
          $attributes[$property['publicName']] += $this->serializer->normalize($field_items, $format, $context);
        }
        else {
          $attributes[$property['publicName']] = $this->serializer->normalize($field_items, $format, $context);
        }
      }
    }

    return $attributes;
  }

}
