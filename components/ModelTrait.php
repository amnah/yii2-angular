<?php

namespace app\components;

use Yii;

/**
 * ModelTrait
 * This file contains helper functions for Model classes
 */
trait ModelTrait
{
    /**
     * Load post data into model
     * @param string $formName
     * @return bool
     * @see \yii\base\Model::load()
     */
    public function loadPost($formName = "")
    {
        return $this->load(Yii::$app->request->post(), $formName);
    }

    /**
     * Load post data into model and validate
     * Returns null if no post data is loaded. Otherwise returns validation result
     * @param string $formName
     * @param array $attributeNames
     * @return bool|null
     */
    public function loadPostAndValidate($formName = "", $attributeNames = null)
    {
        if (!$this->loadPost($formName)) {
            return null;
        }
        return $this->validate($attributeNames);
    }

    /**
     * Load post data into model and save (with validation)
     * Returns null if no post data is loaded. Otherwise returns save result
     * @param string $formName
     * @param array $attributeNames
     * @return bool
     */
    public function loadPostAndSave($formName = "", $attributeNames = null)
    {
        if (!$this->loadPost($formName)) {
            return null;
        }
        return $this->save(true, $attributeNames);
    }
}