<?php

namespace dynamikaweb\index;

use Yii;
use yii\helpers\ArrayHelper;
use yii\db\BaseActiveRecord;
use yii\base\Exception;
use yii\base\InvalidConfigException;

class IndexBehavior extends \yii\base\Behavior
{
    public $modelclass;

    public $searchFields;

    /**
     * Check configuration before a task
     *
     * @throws InvalidConfigException
     */
    public function verify()
    {
        if (!Verify::isClosure($this->searchFields)) {
            throw new InvalidConfigException("'searchFields' must be instance of 'Closure'");
        }
        if (!Verify::isActiveRecord($this->modelclass)) {
            throw new InvalidConfigException("'modelclass' must be class of 'BaseActiveRecord'");
        }
        if (!Verify::hasTimestampBehavior($this->modelclass)) {
            throw new InvalidConfigException("Class {$this->modelclass} must be contain 'TimestampBehavior' injected dependency.");
        }
    }

    /**
	 * @inheritdoc
	 */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_INSERT => 'index',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'index',
        ];
    }

    public function index($event)
    {
        self::verify();
        $searchFields = call_user_func($this->searchFields, $this->owner);
        $model = self::findModel($searchFields);

        foreach($searchFields as $field)
        {
            $attribute = $field['name'];
            $type = ArrayHelper::getValue($field, 'type', IndexFormat::TYPE_STRING);
            $value = IndexFormat::toString($field['value'], $type);
            $model->setAttribute($attribute, $value);
        }


        if (!$model->save()) {
            throw new Exception("An error occurred while indexing: ".current($model->firstErrors));
        }
    }

    protected function findModel($fields)
    {
        $model = new $this->modelclass;

        $fields = array_filter($fields, function($field) {
            return !empty($field['type']) && $field['type'] == IndexFormat::TYPE_UNIQUE;
        });
    
        $model = $model::findOne(array_combine(
            array_map(function ($field) {return $field['name'];}, $fields),
            array_map(function ($field) {return $field['value'];}, $fields)
        ));
        
        return empty($model)? new $this->modelclass: $model;
    }
}
