<?php

namespace Drupal\localgov_openreferral\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\TypedData\TypedDataInternalPropertiesHelper;
use Drupal\localgov_directories\LocalgovDirectoriesFacetsInterface;

/**
 * Normalizes/denormalizes Drupal content entities into an array structure.
 */
class LocalgovFacetNormalizer extends ContentEntityNormalizer {

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = ['openreferral_json'];

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = LocalgovDirectoriesFacetsInterface::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $attributes = parent::normalize($entity, $format, $context);
    $attributes['vocabulary'] = $entity->bundle();
    return $attributes;
  }

}
