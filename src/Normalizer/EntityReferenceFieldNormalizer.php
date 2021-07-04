<?php

namespace Drupal\localgov_openreferral\Normalizer;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\localgov_openreferral\MappingInformation;
use Drupal\serialization\Normalizer\NormalizerBase;

/**
 * Normalizer class specific for entity reference items in field.
 */
class EntityReferenceFieldNormalizer extends NormalizerBase {

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = ['openreferral_json'];

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = EntityReferenceFieldItemListInterface::class;

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
  public function normalize($field, $format = NULL, array $context = []) {
    assert($field instanceof EntityReferenceFieldItemListInterface);
    $attributes = [];

    $parent = $field->getEntity();
    $parent_type = $this->mappingInformation->getPublicType($parent->getEntityTypeId(), $parent->bundle());
    $reference_parent = [
      'service_at_locations' => 'service',
      'service_taxonomys' => 'taxonomy',
    ];
    $direction = $reference_parent[$context['field']['publicName']] == $parent_type;

    foreach ($field->referencedEntities() as $entity) {
      $type = $this->mappingInformation->getPublicType($entity->getEntityTypeId(), $entity->bundle());
      $id = $direction ?
        $parent->uuid() . '-' . $entity->uuid() :
        $entity->uuid() . '-' . $parent->uuid();
      $attribute = ['id' => $id];
      if (count($context['parents']) < 3) {
        $attribute[$type] = $this->serializer->normalize($entity, $format, $context);
      }
      $attributes[] = $attribute;
    }

    return $attributes;
  }

}
