<?php

namespace WPWhales\Contracts\Validation;

use WPWhales\Validation\Validator;

interface ValidatorAwareRule
{
    /**
     * Set the current validator.
     *
     * @param  \WPWhales\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator(Validator $validator);
}
