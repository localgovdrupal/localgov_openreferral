<?php

namespace Drupal\localgov_openreferral\Normalizer;

use Drupal\taxonomy\TermInterface;

/**
 * Normalizes/denormalizes Drupal content entities into an array structure.
 */
class TaxonomyTermNormalizer extends ContentEntityNormalizer {

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = ['openreferral_json'];

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = TermInterface::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $attributes = parent::normalize($entity, $format, $context);
    // @todo when there are controlled vocabularies override the 'id' and
    // 'vocabulary' with the correct data from the ontology.
    $attributes['vocabulary'] = $entity->bundle();
    return $attributes;
  }

}
