<?php

/**
 * Restpal
 *
 * @author Damian Balandowski (balandowski@icloud.com)
 */

namespace damianbal\Restpal;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class RestpalController extends BaseController
{
    protected $restpal = null;
    protected $restpalConfig = null;

    /**
     * Construct
     *
     * @param Restpal $restpal
     * @param RestpalConfiguration $restpalConfiguration
     */
    public function __construct(Restpal $restpal, RestpalConfiguration $restpalConfiguration)
    {
        $this->restpal = $restpal;
        $this->restpalConfig = $restpalConfiguration;
    }

    /**
     * Relational get, returns relation data from model with ID
     *
     * @param Request $request
     * @param string $model
     * @param integer $id
     * @param string $relation
     * @return mixed
     */
    public function restRelationGet(Request $request, $model, $id, $relation)
    {
        if (!class_exists("App\\" . $model)) {
            return ['error' => true, 'message' => 'Model does not exist!'];
        }

        $rc = new \ReflectionClass("App\\".$model);

        if(!$rc->hasMethod($relation))
        {
            return ['error' => true, 'message' => 'Relation does not exist!'];
        }

        $sortBy = $request->input('sortBy', 'created_at');
        $sortOrder = $request->input('sortOrder', 'DESC');
        $perPage = $request->input('perPage', config('restpal.default_perPage', 5));

        $m = $this->restpal->getModelById($model, $id);
        $relatedModel = explode('\\', get_class($m->{$relation}()->getRelated()))[1];


        $m = $this->restpal->getModelById($model, $id);
        //$rd = $m->{$relation}()->get();
        $rd = $m->{$relation}()->orderBy($sortBy, $sortOrder)->paginate($perPage);
        $res = $this->restpal->getModelResource($model);

        // if page is set to -1 then return all items
        if($request->input('page') == -1)
        {
            return ['data' => $m->{$relation}()->orderBy($sortBy, $sortOrder)->get()];
        }

        return $rd;
    }

    /**
     * Relational post, create data on relation from model
     *
     * @param Request $request
     * @param string $model
     * @param integer $id
     * @param string $relation
     * @return mixed
     */
    public function restRelationPost(Request $request, $model, $id, $relation)
    {
        if (!class_exists("App\\" . $model)) {
            return ['error' => true, 'message' => 'Model does not exist!'];
        }

        $rc = new \ReflectionClass("App\\".$model);

        if(!$rc->hasMethod($relation))
        {
            return ['error' => true, 'message' => 'Relation does not exist!'];
        }

        $data = $request->all();

        $m = $this->restpal->getModelById($model, $id);
        $rel = $m->{$relation}();


        $relatedModel = explode('\\', get_class($m->{$relation}()->getRelated()))[1];

        // Check if user can create it
        if(class_exists($this->restpal->getPolicyNameForModel($relatedModel)))
        {
            if(auth('api')->user() == null)
            {
                return ['error' => true, 'message' => 'Not signed in!'];
            }

            if(!auth('api')->user()->can('createResource', "App\\" . $relatedModel))
            {
                return ['error' => true, 'message' => 'Not authorized!'];
            }
        }

        $validator = $this->restpalConfig->getValidator($relatedModel, 'create', $request->all());

        if($validator != null)
        {
            if($validator->fails())
            {
                return ['error' => true, 'message' => 'Validation error!'];
            }
            else
            {
                return $rel->create($data);
            }
        }
        else
        {
            return $rel->create();
        }
    }

    /**
     * GET
     *
     * @param Request $request
     * @param string $model
     * @param integer $id
     * @return mixed
     */
    public function restGet(Request $request, $model, $id = null)
    {
        $sortBy = $request->input('sortBy', 'created_at');
        $sortOrder = $request->input('sortOrder', 'DESC');
        $perPage = $request->input('perPage', config('restpal.default_perPage', 5));

        if (!class_exists("App\\" . $model))
        {
            return ['error' => true, 'message' => 'Model does not exist!'];
        }

        if ($id != null)
        {
            // return single item
            $m = $this->restpal->getModelById($model, $id);
            $res = $this->restpal->getModelResource($model);

            // use Resource if it exists, if not then return without resource
            if (class_exists($res))
            {
                return new $res($m);
            }
            else
            {
                return ['data' => $m];
            }
        }
        else
        {
            $m = $this->restpal->getModels($model, $sortBy, $sortOrder, $perPage);
            $res = $this->restpal->getModelResource($model);

            if (class_exists($res))
            {
                return $res::collection($m);
            }
            else
            {
                return $m;
            }
        }

        return [];
    }

    /**
     * POST
     *
     * @param Request $request
     * @param string $model
     * @return mixed
     */
    public function restPost(Request $request, $model)
    {
        if (!class_exists("App\\" . $model))
        {
            return ['error' => true, 'message' => 'Model does not exist!'];
        }

        $validator = $this->restpalConfig->getValidator($model, 'create', $request->all());

        // if there is Policy for that model then check if user can post to that model
        if(class_exists($this->restpal->getPolicyNameForModel($model)))
        {
            if(auth('api')->user() == null) {
                return ['error' => true, 'message' => 'Not signed in!'];
            }

            if(auth('api')->user()->can('createResource', "App\\" . $model))
            {
                $model = $this->restpal->createModel($model, $request->all(), $validator);

                if($model != null)
                {
                    return $model;
                }
                else
                {
                    return ['error' => true, 'message' => 'Validation error!'];
                }
            }
            else
            {
                return ['error' => true, 'message' => 'Not authorized!'];
            }
        }
        else // anybody can post to it as there is no Policy for it
        {
            $model = $this->restpal->createModel($model, $request->all(), $validator);

            if ($model != null)
            {
                return $model;
            }
            else
            {
                return ['error' => true, 'message' => 'Validation error!', 'errors' => $validator->errors()];
            }
        }
    }

    /**
     * PATCH
     *
     * @param Request $request
     * @param string $model
     * @param integer $id
     * @return mixed
     */
    public function restPatch(Request $request, $model, $id)
    {
        if (!class_exists("App\\" . $model))
        {
            return ['error' => true, 'message' => 'Model does not exist!'];
        }

        $validator = $this->restpalConfig->getValidator($model, 'update', $request->all());


        // if there is Policy for that model then check if user can post to that model
        if (class_exists($this->restpal->getPolicyNameForModel($model)))
        {
            if (auth('api')->user() == null) {
                return ['error' => true, 'message' => 'Not signed in!'];
            }


            if (auth('api')->user()->can('updateResource', $this->restpal->getModelById($model, $id))) {
                $model = $this->restpal->updateModel($model, $id, $request->all(), $validator);

                if ($model != false || $model > 0)
                {
                    return ['success' => true];
                }
                else
                 {
                    return ['error' => true, 'message' => 'Validation error!', 'errors' => $validator->errors()];
                }
            } else {
                return ['error' => true, 'message' => 'Not authorized!'];
            }
        }
        else // anybody can update as there is no Policy for it
        {
            if ($validator != null) {
                if ($validator->fails())
                {
                    return ['error' => true, 'message' => 'Validation error!', 'errors' => $validator->errors()];
                }
                else
                {
                    $r = $this->restpal->updateModel($model, $id, $request->all());
                    return ['success' => ($r != false || $r > 0) ? true : false];
                }
            }
            else // no validator so update without validation
            {
                $r = $this->restpal->updateModel($model, $id, $request->all());
                return ['success' => ($r != false || $r > 0) ? true : false];
            }

        }
    }

    /*
    public function restPut()
    {
        //
    }*/

    /**
     * DELETE
     *
     * @param Request $request
     * @param string $model
     * @param integer $id
     * @return mixed
     */
    public function restDelete(Request $request, $model, $id)
    {
        if (!class_exists("App\\" . $model))
        {
            return ['error' => true, 'message' => 'Model does not exist!'];
        }

        if (class_exists($this->restpal->getPolicyNameForModel($model)))
        {
            if (auth('api')->user() == null) {
                return ['error' => true, 'message' => 'Not signed in!'];
            }

            if (auth('api')->user()->can('deleteResource', "App\\" . $model))
            {
                return $this->restpal->deleteModel($model, $id);
            }
            else
            {
                return ['error' => true, 'message' => 'Not authorized!'];
            }
        }
        else
        {
            return $this->restpal->deleteModel($model, $id);
        }
    }
}
