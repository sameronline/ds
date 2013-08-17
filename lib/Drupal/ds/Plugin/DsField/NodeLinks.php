<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsField\NodeLinks.
 */

namespace Drupal\ds\Plugin\DsField;

use Drupal\Core\Annotation\Translation;
use Drupal\ds\Annotation\DsField;

/**
 * Plugin that renders the links of the node entity.
 *
 * @DsField(
 *   id = "node_links",
 *   title = @Translation("Links"),
 *   entity_type = "node",
 *   module = "ds"
 * )
 */
class NodeLinks extends DsFieldBase {

}
