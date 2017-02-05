<?php
namespace bl\cms\translate\models;
use bl\cms\translate\Translation;
use yii\base\Model;

/**
 * Model for translations filtering form
 * @author Albert Gainutdinov <xalbert.einsteinx@gmail.com>
 */
class FilterModel extends Model
{
    /**
     * @var integer
     */
    public $category;

    /**
     * @var integer
     */
    public $languageId;

    /**
     * @var string
     */
    public $translation;

    public function rules()
    {
        return [
            [['category', 'translation'], 'string'],
            ['languageId', 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'category' => Translation::t('main', 'Category'),
            'languageId' => Translation::t('main', 'Language'),
            'translation' => Translation::t('main', 'Translation'),
        ];
    }
}