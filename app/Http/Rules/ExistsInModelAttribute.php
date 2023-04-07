<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExistsInModelAttribute implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($model, $attribute, $attributeKey = null)
    {
        $this->model = $model;
        $this->modelAttribute = $attribute;
        $this->modelAttributeKey = $attributeKey;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->modelAttributeKey
                ? in_array($value, $this->model::${$this->modelAttribute}[$this->modelAttributeKey])
                : in_array($value, $this->model::${$this->modelAttribute});
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute value is invalid.';
    }
}
