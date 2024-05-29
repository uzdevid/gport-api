<?php

namespace common\Model;

use common\Behavior\DateTimeBehavior;

class Sharing extends Base\Sharing {
    /**
     * {@inheritdoc}
     */
    public function rules(): array {
        return [
            [['user_id', 'access_id', 'client_id', 'protocol', 'remote', 'local', 'active', 'is_active'], 'required'],
            [['user_id', 'access_id'], 'string'],
            [['active'], 'default', 'value' => null],
            [['active'], 'integer'],
            [['is_active'], 'boolean'],
            [['created_time'], 'safe'],
            [['client_id', 'remote'], 'string', 'max' => 32],
            [['protocol'], 'string', 'max' => 12],
            [['local'], 'string', 'max' => 64],
        ];
    }

    /**
     * @return array
     */
    public function behaviors(): array {
        $behaviors = parent::behaviors();

        $behaviors['DateTimeBehavior'] = [
            'class' => DateTimeBehavior::class,
            'attributes' => [
                self::EVENT_BEFORE_INSERT => ['created_time'],
            ]
        ];

        return $behaviors;
    }
}