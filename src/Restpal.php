<?php

/**
 * Restpal
 *
 * @author Damian Balandowski (balandowski@icloud.com)
 */

namespace damianbal\Restpal;

use Illuminate\Support\Facades\Schema;

class Restpal
{
    /**
     * Returns full name of Policy for model
     *
     * @param string $model
     * @return void
     */
    public function getPolicyNameForModel($model)
    {
        return "App\\Policies\\" . $model . "Policy";
    }

    /**
     * Create new model with data
     *
     * @param string $model
     * @param array $data
     * @return mixed
     */
    public function createModel($model, $data = [], $validator = null)
    {
        if($validator == null)
        {
            $m = "App\\" . $model;
            return $m::create($data);
        }
        else
        {
            if($validator->fails())
            {
                return null;
            }
            else {
                $m = "App\\" . $model;
                return $m::create($data);
            }
        }
    }

    /**
     * Update existing model with new data
     *
     * @param string $model
     * @param integer $id
     * @param array $data
     * @return mixed
     */
    public function updateModel($model, $id, $data = [], $validator = null)
    {
        if($validator != null)
        {
            if ($validator->fails()) {
                return false;
            }
            else
            {
                $m = "App\\" . $model;
                return $m::find($id)->update($data);
            }
        }
        else
        {
            $m = "App\\" . $model;
            return $m::find($id)->update($data);
        }
    }

    /**
     * Delete model
     *
     * @param string $model
     * @param integer $id
     * @return mixed
     */
    public function deleteModel($model, $id)
    {
        $m = "App\\" . $model;

        if($m::find($id) == null) {
            return ['success' => true];
        }

        if($m::find($id)->delete()) {
            return ['success' => true];
        }
        else {
            return ['success' => false];
        }
    }

    /**
     * Return model by ID
     *
     * @param string $model
     * @param integer $id
     * @return mixed
     */
    public function getModelById($model, $id = 1)
    {
        $m = "App\\" . $model;
        return $m::find($id);
    }

    /**
     * Return models
     *
     * @param string $model
     * @param string $sortBy
     * @param string $order
     * @param integer $perPage
     * @return mixed
     */
    public function getModels($model, $sortBy = 'created_at', $order = 'DESC', $perPage = 5)
    {
        $m = "App\\" . $model;

        $order = strtoupper($order);

        if($perPage == 0)
        {
            return ['data' => $m::orderBy($sortBy, $order)->get()];
        }

        return $m::orderBy($sortBy, $order)->paginate($perPage);
    }

    /**
     * Returns full namespace path to Resource
     *
     * @param string $model
     * @return void
     */
    public function getModelResource($model)
    {
        $modelStr = "App\\Http\\Resources\\" . $model . "Resource";

        return $modelStr;
    }
}
