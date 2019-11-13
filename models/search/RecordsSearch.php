<?php

namespace app\models\search;

use app\models\RequestVideoUser;
use phpDocumentor\Reflection\Types\String_;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Records;

/**
 * SearchRecords represents the model behind the search form of `app\models\Records`.
 *
 * @property int $status
 */
class RecordsSearch extends Records
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'total_minutes', 'hours', 'minutes', 'status_build'], 'integer'],
            [['filename', 'new_name', 'duration', 'status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Records::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'total_minutes' => $this->total_minutes,
            'hours' => $this->hours,
            'minutes' => $this->minutes,
        ]);

        $query->andFilterWhere(['like', 'filename', $this->filename])
            ->andFilterWhere(['like', 'new_name', $this->new_name])
            ->andFilterWhere(['like', 'duration', $this->duration]);
           // ->andFilterWhere(['>=', 'minutes', 2]);

        return $dataProvider;
    }


    /**
     * @param $fileName
     * @return int
     */
    public static function getStatusForRequestVideoByCurrentUser($fileName): int
    {
        $userId = Yii::$app->user->identity->id;

        /** @var RequestVideoUser $requestVideoUser - существует, если юзер запросил эту запись */
        $requestVideoUser = RequestVideoUser::find()
            ->where(['filename' => $fileName])
            ->andWhere(['user_id' => $userId])
            ->one();

        return ($requestVideoUser) ? $requestVideoUser->status_id : -1;

    }
}