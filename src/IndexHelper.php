<?php

namespace dynamikaweb\index;

use Yii;
use yii\helpers\FileHelper;

class IndexHelper
{
    /**
     * @return array
     */
    public static function listClass($alias)
    {
        $root = Yii::getAlias("@root");
        $path = Yii::getAlias($alias);
        $files = FileHelper::findFiles($path, ['fileTypes' => ['php'], 'recursive' => false]);

        $models = array_map(function ($file) use ($root) {
            return str_replace([$root, '.php'], '', $file);
        },
            $files
        );

        $models = array_map(function ($model) {
            return str_replace('/', '\\', $model);
        },
            $models
        );

        $models = array_filter($models, 
            function ($model) {
                return Verify::hasIndexBehavior($model);
            }
        );

        return $models;
    }

    /**
     * @return array
     */
    public static function listNames($alias)
    {
        $root = Yii::getAlias("@root");
        $path = Yii::getAlias($alias);
        $path = str_replace($root, '', $path);
        $path = str_replace('/', '\\', $path);
        
        $models = array_map(function ($model) use ($path) {
            return str_replace([$path, '\\'], '', $model);
        },
            self::listClass($alias)
        );

        return $models;
    }

    /**
     * @return void
     */
    public static function updateOne($model)
    {
        $behaviors = array_filter($model->behaviors, function ($behavior) {
            return $behavior instanceof IndexBehavior;
        });
        $behavior = current($behaviors);
        $behavior->index([]);
    }

    /**
     * @return void
     */
    public static function updateAll($alias)
    {
        array_map(function ($class) {
            $class = new $class;
            array_map(function ($model) {
                self::updateOne($model);
            },
                $class::find()->all()
            );
        },
            self::listClass($alias)
        );
    }
}
