<?php

namespace damianbal\Restpal;

use Illuminate\Support\Facades\Validator;

class RestpalConfiguration
{
    protected $validators = [];

    /**
     * Check if there is validation for model and action
     *
     * @param string $model
     * @param string $action
     * @return boolean
     */
    public function hasValidator($model, $action = 'create')
    {
        return isset($this->validators[$model][$action]);
    }

    /**
     * Returns validator for Model and action
     *
     * @param string $model
     * @param string $action
     * @param array $data
     * @return Illuminate\Support\Facades\Validator
     */
    public function getValidator($model, $action =' create', $data = [])
    {
        if(!$this->hasValidator($model, $action))
        {
            return null;
        }

        return Validator::make($data, $this->validators[$model][$action]);
    }

    /**
     * Add validation for model and action
     *
     * @param string $model
     * @param string $action
     * @param array $validation
     * @return void
     */
    public function setValidation($model, $action = 'create', $validation = [])
    {
        if(!$this->hasValidator($model, $action))
        {
            $this->validators[$model][$action] = $validation;
        }
    }
}
