<?php

namespace common\Model\Base;

use Yii;

/**
 * This is the model class for table "sharing".
 *
 * @property int $id
 * @property string $user_id
 * @property string $access_id
 * @property string $client_id
 * @property string $protocol
 * @property string $remote
 * @property string $local
 * @property int $active
 * @property bool $is_active
 * @property string $created_time
 *
 * @property Access $access
 * @property Traffic[] $traffics
 */
class Sharing extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sharing';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'access_id', 'client_id', 'protocol', 'remote', 'local', 'active', 'is_active', 'created_time'], 'required'],
            [['user_id', 'access_id'], 'string'],
            [['active'], 'default', 'value' => null],
            [['active'], 'integer'],
            [['is_active'], 'boolean'],
            [['created_time'], 'safe'],
            [['client_id', 'remote'], 'string', 'max' => 32],
            [['protocol'], 'string', 'max' => 12],
            [['local'], 'string', 'max' => 64],
            [['access_id'], 'exist', 'skipOnError' => true, 'targetClass' => Access::class, 'targetAttribute' => ['access_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'access_id' => 'Access ID',
            'client_id' => 'Client ID',
            'protocol' => 'Protocol',
            'remote' => 'Remote',
            'local' => 'Local',
            'active' => 'Active',
            'is_active' => 'Is Active',
            'created_time' => 'Created Time',
        ];
    }

    /**
     * Gets query for [[Access]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccess()
    {
        return $this->hasOne(Access::class, ['id' => 'access_id']);
    }

    /**
     * Gets query for [[Traffics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraffics()
    {
        return $this->hasMany(Traffic::class, ['sharing_id' => 'id']);
    }
}
