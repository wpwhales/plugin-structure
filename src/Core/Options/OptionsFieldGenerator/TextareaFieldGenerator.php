<?php

namespace WPWCore\Options\OptionsFieldGenerator;

class TextareaFieldGenerator extends BaseFieldGenerator
{
    /**
     * The default configs.
     * @var array
     */
    protected $defaultConfigs = [
        'attributes' => [
            'cols' => 60,
            'rows' => 5,
        ],
        'defaultValue' => '',
    ];

    /**
     * Generate the field markup.
     *
     * @return string
     */
    final public function generate()
    {
        $allAttributes = static::convertToAttributesString(
            array_merge($this->config('attributes'), ['name' => $this->fieldName])
        );
        $innerHTML = $this->fieldValue;

        return "<textarea {$allAttributes}>{$innerHTML}</textarea>" . $this->generateDescription();
    }

    /**
     * An option could be potentially set to a value before the field saving its value to the database for the first time.
     * This method will be automatically called with the current value of the field if the field has a value.
     * If the value of the current option is a valid value for the field, return `true`, if it's not, return `false`.
     * `$this->validateWithErrorMessage` also use this method to validate the field value.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateFieldValue($value)
    {
        return is_string($value);
    }
}
