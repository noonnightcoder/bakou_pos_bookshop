<?php

/**
 * This is the model class for table "currency_type".
 *
 * The followings are the available columns in table 'currency_type':
 * @property integer $code
 * @property string $currency_id
 * @property string $currency_name
 * @property string $currency_symbol
 * @property integer $sort_order
 */
class CurrencyType extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'currency_type';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('currency_id, currency_name', 'required'),
            array('sort_order', 'numerical', 'integerOnly' => true),
            array('currency_id, currency_symbol', 'length', 'max' => 3),
            array('currency_name', 'length', 'max' => 70),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('code, currency_id, currency_name, currency_symbol, sort_order', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'code' => 'Code',
            'currency_id' => 'Currency',
            'currency_name' => 'Currency Name',
            'currency_symbol' => 'Currency Symbol',
            'sort_order' => 'Sort Order',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('code', $this->code);
        $criteria->compare('currency_id', $this->currency_id, true);
        $criteria->compare('currency_name', $this->currency_name, true);
        $criteria->compare('currency_symbol', $this->currency_symbol, true);
        $criteria->compare('sort_order', $this->sort_order);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CurrencyType the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    protected function getCurrencyInfo()
    {
        return Yii::t('app', $this->currency_name) . ' - ' . $this->currency_symbol;
    }

    // For setting we save symbol instead of code
    public function getCurrency()
    {
        $model = CurrencyType::model()->findAll(array(
            'condition' => 'status=:status',
            'params' => array(':status' => '1'),
            'order' => 'sort_order',
        ));
        $list = CHtml::listData($model, 'code', 'CurrencyInfo');
        return $list;
    }

    public function getCurrencyCode()
    {
        $model = CurrencyType::model()->findAll(array('order' => 'sort_order'));
        $list = CHtml::listData($model, 'code', 'CurrencyInfo');
        return $list;
    }

    public function getActiveCurrency()
    {
        $currency_type = CurrencyType::model()->findAll(array(
            'condition' => 'status=:status',
            'params' => array(':status' => '1'),
            'order' => 'sort_order',
        ));

        return $currency_type;
    }

    public function getActiveCurArr()
    {
        $sql = "SELECT `code`
                FROM `currency_type`
                WHERE `status`=:status
                ORDER BY sort_order";

        $result = Yii::app()->db->createCommand($sql)->queryAll(true, array(':status' => '1'));

        return $result;
    }

    public function getDefaultCurr()
    {
        $currency_type = CurrencyType::model()->find(array(
            'condition' => 'status=:status',
            'params' => array(':status' => '1'),
            'order' => 'sort_order',
            'limit' => '1',
        ));

        return $currency_type;
    }

    public function getSelectedCurrency($code)
    {
        $currency_type = CurrencyType::model()->find(array(
            'condition' => 'code=:code and status=:status',
            'params' => array(':code' => $code, ':status' => '1',
            ),
        ));

        return $currency_type;
    }
}
