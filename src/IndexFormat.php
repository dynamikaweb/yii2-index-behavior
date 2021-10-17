<?php

namespace dynamikaweb\index;

use Yii;
use yii\helpers\Url;
use yii\helpers\Inflector;
use yii\base\Exception;

class IndexFormat
{
    const TYPE_FILTER = 'filter';
    const TYPE_STRING = 'string';
    const TYPE_UNIQUE = 'unique';
    const TYPE_DIRTY = 'dirty';
    const TYPE_DATE = 'date';
    const TYPE_URL = 'url';
    const TYPE_URL_BASELESS = 'url_baseless';

    /**
     * @throws Exception
     * @return string
     */
    public static function toString($value, $format)
    {
        switch ($format)
        {
            case self::TYPE_UNIQUE:
            case self::TYPE_STRING:
                return self::fromString($value);
            
            case self::TYPE_DIRTY:
                return self::fromDirty($value);

            case self::TYPE_DATE:
                return self::fromDate($value);

            case self::TYPE_URL: 
                return self::fromUrl($value);

            case self::TYPE_URL_BASELESS: 
                return preg_replace('#'.Yii::$app->request->baseUrl.'#', '', self::fromUrl($value), 1);

            default:
                throw new Exception("type '{$format}' not exists");
        }
    }

    /**
     * @return string
     */
    public static function fromString($value)
    {
        if(!is_string($value) && !is_null($value)) {
            throw new Exception("'value' must be a string");
        }

        return (string) $value;
    }

    /**
     * @return string
     */
    public static function fromDirty($value)
    {
        if (is_array($value)) {
            $value = implode(' ', array_filter($value, 'is_string'));
        }
        if(!is_string($value)) {
            throw new Exception("'value' must be a string or array");
        }

        $value = strip_tags($value);
        $value = Inflector::slug($value, ' ');

        return $value;
    }

    /**
     * @return mixed
     */
    public static function fromDate($value)
    {
        if (!Verify::isDate($value)){
            throw new Exception("'value' must be class of \yii\db\Expression or \DateTime, and also can be string");
        }

        return $value;
    }

    /**
     * @return string
     */
    public static function fromUrl($value)
    {
        if (!is_array($value) && !is_string($value)) {
            throw new Exception("'value' must be a string or array");
        }

        return Url::to($value);
    }
}
