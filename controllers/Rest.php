<?php
namespace app\controllers;
use eo\models\Filterable;
use eo\base\EO;
use eo\models\database\Cmsusers;
use eo\models\database\Relation;
use machour\yii2\swagger\api\ApiController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use \yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;
use yii\web\BadRequestHttpException;

/**
 * EveryOffice API
 *
 * Retrieve data from EveryOffice
 *
 * @consumes application/json
 * @produces application/json
 * @package app\controllers
 * @host test.api2.count-it.nl
 * @schemes https
 * @contactEmail support@digizijn.nl
 * @version 0.0.1
 */
abstract class Rest extends ApiController {
    public $reservedParams = ['sort','q'];

	public function behaviors() {
		$behaviors = parent::behaviors();

		unset($behaviors['contentNegotiator']['formats']['application/xml']);
		$behaviors['contentNegotiator']['formats']['application/jsonp'] = \yii\web\Response::FORMAT_JSONP;

		$behaviors['corsFilter'] = [
				'class' => \yii\filters\Cors::className(),
		];

		if ($_SERVER['REMOTE_ADDR'] !== '23.22.16.221') { // Swagger validator
			$behaviors['authenticator'] = [
				'class' => HttpBasicAuth::className(),
				'auth' => function ($username, $password) {
					$user = Cmsusers::find()->where(['user_name' => $username, 'user_level' => 'api'])->one(); // user level
					if (!empty($user)) {
						if ($user->verifyPassword($password)) {
							$relation = Relation::find()->where(['user_id' => $user->user_id])->one();

//							$relation = $user->getRelationEo()->one();
							if (!empty($relation)) {
								EO::$app->params['company_id'] = $relation->company_id;
								return $user;
							}
						}
					}

					return null;
				},
			];
		}

		return $behaviors;
	}


	public function getSecurityDefinitions() {
		return [
			'default' => [
				'type' 	=> 'basic',
				'in'	=> 'header',
				'description' => 'Basic authentication'
			]
		];
	}


	/**
	 * @inheritdoc
	 */
	public function actions()	{
		$actions = parent::actions();

        // 'prepareDataProvider' is the only function that need to be overridden here
		if (method_exists($this, 'indexDataProvider')) {
        	$actions['index']['prepareDataProvider'] = [$this, 'indexDataProvider'];
		}

		$actions['nested-index'] = [
			'class' => 'tunecino\nestedrest\IndexAction', /* required */
			'modelClass' => $this->modelClass, /* required */
			'checkAccess' => [$this, 'checkAccess'], /* optional */
		];

		$actions['nested-view'] = [
			'class' => 'tunecino\nestedrest\ViewAction', /* required */
			'modelClass' => $this->modelClass, /* required */
			'checkAccess' => [$this, 'checkAccess'], /* optional */
		];

		$actions['nested-create'] = [
			'class' => 'tunecino\nestedrest\CreateAction', /* required */
			'modelClass' => $this->modelClass, /* required */
			'checkAccess' => [$this, 'checkAccess'], /* optional */
			/**
			 * the scenario to be assigned to the new model before it is validated and saved.
			 */
			'scenario' => 'default', /* optional */
			/**
			 * the scenario to be assigned to the model class responsible
			 * of handling the data stored in the juction table.
			 */
			'viaScenario' => 'default', /* optional */
			/**
			 * expect junction table related data to be wrapped in a sub object key in the body request.
			 * In the example we gave above we would need to do :
			 * POST {name: 'dribble', related: {level: 10}}
			 * instead of {name: 'dribble', level: 10}
			 */
			'viaWrapper' => 'related' /* optional */
		];

		$actions['nested-link'] = [
			'class' => 'tunecino\nestedrest\LinkAction', /* required */
			'modelClass' => $this->modelClass, /* required */
			'checkAccess' => [$this, 'checkAccess'], /* optional */
			/**
			 * the scenario to be assigned to the model class responsible
			 * of handling the data stored in the juction table.
			 */
			'viaScenario' => 'default', /* optional */
		];

		$actions['nested-unlink'] = [
			'class' => 'tunecino\nestedrest\UnlinkAction', /* required */
			'modelClass' => $this->modelClass, /* required */
			'checkAccess' => [$this, 'checkAccess'], /* optional */
		];

		$actions['nested-unlink-all'] = [
			'class' => 'tunecino\nestedrest\UnlinkAllAction', /* required */
			'modelClass' => $this->modelClass, /* required */
			'checkAccess' => [$this, 'checkAccess'], /* optional */
		];

		return $actions;
	}


	public function indexDataProvider() {
        $params = \Yii::$app->request->getQueryParams();

        $model = new $this->modelClass;
        // I'm using yii\base\Model::getAttributes() here
        // In a real app I'd rather properly assign
        // $model->scenario then use $model->safeAttributes() instead
        $modelAttr = $model->attributes;

        // this will hold filtering attrs pairs ( 'name' => 'value' )
        $search = [];

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                // In case if you don't want to allow wired requests
                // holding 'objects', 'arrays' or 'resources'
                if(!is_scalar($key) or !is_scalar($value)) {
                    throw new BadRequestHttpException('Bad Request');
                }

                // if the attr name is not a reserved Keyword like 'q' or 'sort' and
                // is matching one of models attributes then we need it to filter results
//                if (!in_array(strtolower($key), $this->reservedParams)
//                    && ArrayHelper::keyExists($key, $modelAttr, false)) {
                    $search[str_replace(':', '.', $key)] = $value;
//                }
            }
        }

        // you may implement and return your 'ActiveDataProvider' instance here.
        // in my case I prefer using the built in Search Class generated by Gii which is already
        // performing validation and using 'like' whenever the attr is expecting a 'string' value.
		if (true) { // TODO FIXME if model search class
			$query = call_user_func([$this->modelClass, 'find']);

			$query = $this->applySearch($query);
			if ($model instanceof Filterable) {
				$model->scenario = Filterable::SCENARIO;
				$query = $model->search($query, $search);
			}

			return new ActiveDataProvider([
				'query' => $query,
//				'pagination' => $this->getPagination(),
//				'sort' => $this->getSort()
			]);
		} else {
			$searchByAttr['GenericSearch'] = $search;
			$searchModel = new \app\models\GenericSearch();
			return $searchModel->search($searchByAttr);
		}
    }

	/**
	 * @param $query
	 * @return ActiveDataProvider
	 */
    public function applySearch($query) {
    	return $query;
	}

}