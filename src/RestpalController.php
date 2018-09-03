<?php

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
            return ['message' => 'Model does not exist!'];
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
                return ['data' => $m];
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
        $validator = $this->restpalConfig->getValidator($model, 'create', $request->all());

        // if there is Policy for that model then check if user can post to that model
        if(class_exists($this->restpal->getPolicyNameForModel($model)))
        {
            if(auth('api')->user() == null) {
                return ['error' => true, 'message' => 'Not signed in!'];
            }

            if(auth('api')->user()->can('create', "App\\" . $model))
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
        $validator = $this->restpalConfig->getValidator($model, 'update', $request->all());


        // if there is Policy for that model then check if user can post to that model
        if (class_exists($this->restpal->getPolicyNameForModel($model)))
        {
            if (auth('api')->user() == null) {
                return ['error' => true, 'message' => 'Not signed in!'];
            }


            if (auth('api')->user()->can('update', $this->restpal->getModelById($model, $id))) {
                $model = $this->restpal->updateModel($model, $id, $request->all(), $validator);

                if ($model != false)
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
                    return $this->restpal->updateModel($model, $id, $request->all());
                }
            }
            else // no validator so update without validation
            {
                return ['success' => $this->restpal->updateModel($model, $id, $request->all())];
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
        if (class_exists($this->restpal->getPolicyNameForModel($model)))
        {
            if (auth('api')->user() == null) {
                return ['error' => true, 'message' => 'Not signed in!'];
            }

            if (auth('api')->user()->can('delete', "App\\" . $model))
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
