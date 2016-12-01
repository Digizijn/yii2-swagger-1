<?php

namespace app\models;

use eo\models\database\RecreationEventsComposition;
use eo\models\database\RecreationEventsFacility;
use eo\models\database\RecreationEventsProducts;

/**
 * Class RecreationEventCreateRequest
 *
 * @package app\models
 *
 * @required
 * @property array $compositions
 * @property integer $block_id Event id used for blocking/option
 * @required
 * @property integer $object_id Object id
 * @required
 * @property string $arrival Arrival date
 * @required
 * @property string $departure Departure date
 * @required
 * @property integer $relation_id Relation accounting this booking
 * @property array $facilities Facilities
 * @property array $extras Extra product lines
 * @property boolean $preferable Preferable booking object
 * @property boolean $generate_invoices Automagically generate invoices
 */
class RecreationEventCreateRequest {
}