<?php

namespace backend\controllers;

use Yii;
use common\models\Event;
use common\models\Team;
use common\models\EventSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

/**
 * EventController implements the CRUD actions for Event model.
 */
class EventController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
			'access' => [
				'class' => \yii\filters\AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'actions' => ['admin','index', 'create', 'update', 'clone'],
						'roles' => ['administrator'],
					],
					[
						'allow' => true,
						'actions' => ['index', 'view'],
						'roles' => ['?', '@'],
					],
				],
			],
        ];
    }

    /**
     * Lists all active Event models.
     * @return mixed
     */
    public function actionIndex()
    {
		$dataProvider = new \yii\data\ActiveDataProvider([
			'query' => Event::find()->where(['active' => true]),
			'pagination' => false,
		]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAdmin()
    {
		$dataProvider = new \yii\data\ActiveDataProvider([
			'query' => Event::find(),
			'pagination' => false,
		]);

        return $this->render('admin', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Event model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		$dp = new ActiveDataProvider([
			'query' => Team::find()->where(['event_id' => $id]),
			'pagination' => false,
		]);

        return $this->render('view', [
            'model' => $this->findModel($id),
			'dataProvider' => $dp,
        ]);
    }

    /**
     * Creates a new Event model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Event();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Event model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Event model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

	public function actionClone($id)
	{
		$old_event = $this->findModel($id);

		//Clone event
		$new_event = new Event();
		$new_event->attributes = $old_event->attributes;
		$new_event->formStart = $old_event->formStart;
		$new_event->formEnd = $old_event->formEnd;

		//Clone teams
		$old_teams = $old_event->teams;
		foreach($old_teams as $old_team)
		{
			$new_team = new Team();
			$new_team->attributes = $old_team->attributes;
			$new_team->event_id = $new_event->id;
			$new_team->save();

			//Clone shifts
			$old_shifts = $old_team->shifts;
			foreach($old_shifts as $old_shift)
			{
				$new_shift = new Shift();
				$new_shift->attributes = $old_shift->attributes;
				$new_shift->team_id = $new_team->id;
				$new_shift->save();
			}
		}

        return $this->redirect(['view', 'id' => $new_event->id]);
	}

    /**
     * Finds the Event model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Event the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Event::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
