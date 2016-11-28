<?php
/**
 * Created by PhpStorm.
 * User: Max
 * Date: 15.02.2016
 * Time: 19:00
 */

namespace bl\cms\translate\controllers;

use bl\cms\translate\models\entities\Message;
use bl\cms\translate\models\entities\SourceMessage;
use bl\multilang\entities\Language;
use Yii;
use yii\data\Pagination;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;

class MessageController extends Controller
{
    public function actionIndex()
    {
        $language = Language::findOrDefault(Yii::$app->request->get('languageId'));
        $category = SourceMessage::findOne(Yii::$app->request->get('categoryId'));
        $message = SourceMessage::find();
//        if(!empty($language)) {
//            $message->with(['messages' => function($query) use($language) {
//                $query->andWhere(['language' => $language->lang_id]);
//            }]);
//        }
        if(!empty($category)) {
            $message->where(['category' => $category->category]);
        }

        $messages_count = clone $message;
        $pages = new Pagination(['totalCount' => $messages_count->count(), 'defaultPageSize' => 50]);
        $messages = $message->offset($pages->offset)
            ->orderBy(['id' => SORT_DESC])
            ->limit($pages->limit)
            ->all();

        return $this->render('index', [
            'allLanguages' => Language::find()->all(),
            'languages' => Language::find()->where(['default' => false])->all(),
            'allCategories' => SourceMessage::find()->select(['id as id', 'category as category'])->groupBy(['category', 'id'])->orderBy(['category' => SORT_ASC])->all(),
            'sourceMessages' => $messages,
            'pages' => $pages,
            'addModel' => new SourceMessage(),
            'selectedCategory' => $category->id,
            'selectedLanguage' => $language
        ]);
    }

    public function actionAdd(){
        $addModel = new SourceMessage();
        $messageCategory = SourceMessage::find()->where(['id' => Yii::$app->request->post('categoryId')])->one();
        if($addModel->load(Yii::$app->request->post()) && $addModel->validate()){
            if(!empty($messageCategory))
                $addModel->category = $messageCategory->category;
            if(empty($addModel->category)) {
                Yii::$app->session->setFlash('error', 'Category empty');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $addModel->save();
            Yii::$app->session->setFlash('success', 'Data success created.');
        }
        else
            Yii::$app->session->setFlash('error', Html::errorSummary($addModel));
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionEdit($categoryId = null, $languageId = null)
    {
        if(Yii::$app->request->isPost){
            $source_message = SourceMessage::find()->where(['id' => Yii::$app->request->post("SourceMessage")['id']])->one();
            $message_language = Yii::$app->request->post("Message");
            $source_message->load(Yii::$app->request->post());
            $source_message->save();
            if(!empty($message_language['language'])){
                $message = Message::find()->where(['id' => $source_message->id, 'language' => $message_language['language']])->one();
                if(empty($message))
                    $message = new Message();
                $message->load(Yii::$app->request->post());
                $message->id = $source_message->id;
                $message->save();
            }
            return $this->redirect(Url::toRoute(['/translation/message', 'categoryId' => $source_message->id, 'languageId' => Language::find()->where(['lang_id' => $message->language])->one()->id]));
        } else {
            $language = Language::findOne($languageId);
            $category = SourceMessage::find()->where(['id' => $categoryId])->one();
            if ($language->lang_id != Yii::$app->sourceLanguage) {
                $message = Message::find()->where(['id' => $category->id, 'language' => $language->lang_id])->one();
                if (empty($message))
                    $message = new Message();
                return $this->render('source-message/edit',
                    [
                        'source_message' => $category,
                        'message' => $message,
                        'categories' => SourceMessage::find()->all(),
                        'languages' => Language::find()->all(),
                        'language' => $language
                    ]);
            } else {
                return $this->render('message/edit', ['model' => $category, 'language' => $language]);
            }
        }
    }

    public function actionDelete($categoryId = null, $languageId = null){
        $language = Language::find()->where(['id' => $languageId])->one();
        if(Message::find()->where(['id' => $categoryId])->count() == 0)
            SourceMessage::find()->where(['id' => $categoryId])->one()->delete();
        else {
            $message = Message::find()->where(['id' => $categoryId, 'language' => $language->lang_id])->one();
            if(!empty($message))
                $message->delete();
            else
                Yii::$app->session->setFlash('success', 'This category linked data , such a category can not be deleted.');
        }

        return $this->redirect(Yii::$app->request->referrer);
    }
}
