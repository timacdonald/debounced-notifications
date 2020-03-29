<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable as NotifiableTrait;

/**
 * Attributes.
 *
 * @property int $id
 */
class Notifiable extends Model
{
    use NotifiableTrait;
}
