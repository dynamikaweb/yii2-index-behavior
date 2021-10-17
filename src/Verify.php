<?php

namespace dynamikaweb\index;

use Yii;
use Closure;
use yii\db\BaseActiveRecord;
use yii\behaviors\TimestampBehavior;

class Verify
{
    /**
     * @return object
     */
    private function construct($class)
    {
        return is_string($class)? new $class: $class;
    }

    /**
     * @return boolean
     */
    public static function isClosure($class)
    {
        return is_callable($class) || static::construct($class) instanceof Closure;   
    }

    /**
     * @return boolean
     */
    public static function isActiveRecord($class)
    {
        if (empty($class)) {
            return false;
        }

        return static::construct($class) instanceof BaseActiveRecord;
    }

    /**
     * @return boolean
     */
    public static function isDate($class)
    {
        if (is_string($class) || is_null($class)) {
            return true;
        }
        if ($class instanceof \yii\db\Expression) {
            return true;
        } 
        if ($class instanceof \DateTime) {
            return true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public static function hasTimestampBehavior($class)
    {
        $behaviors = array_filter(static::construct($class)->behaviors(), 
            function($behavior) {
                return $behavior['class'] == TimestampBehavior::classname();
            }   
        );

        return !empty($behaviors);
    }
}
