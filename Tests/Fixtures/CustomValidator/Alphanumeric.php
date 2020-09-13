<?php

namespace Jackai\Validator\Tests\Fixtures\CustomValidator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Alphanumeric extends Constraint
{
    public $message = 'The string "{{ string }}" contains an illegal character: it can only contain letters or numbers.';
}
