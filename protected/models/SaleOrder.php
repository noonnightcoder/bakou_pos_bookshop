<?php

/**
 * This is the model class for table "sale_order".
 *
 * The followings are the available columns in table 'sale_order':
 * @property integer $id
 * @property string $sale_time
 * @property integer $client_id
 * @property integer $employee_id
 * @property integer $location_id
 * @property double $sub_total
 * @property string $payment_type
 * @property string $status
 * @property string $remark
 * @property string $discount_amount
 * @property string $discount_type
 * @property integer $giftcard_id
 * @property integer $empty_flag
 */
class SaleOrder extends CActiveRecord
{
    private $active_status = 1;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'sale_order';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('sale_time', 'required'),
            array(
                'client_id, desk_id, zone_id, group_id, employee_id, location_id, giftcard_id, empty_flag',
                'numerical',
                'integerOnly' => true
            ),
            array('sub_total', 'numerical'),
            array('payment_type', 'length', 'max' => 255),
            array('status', 'length', 'max' => 20),
            array('discount_amount', 'length', 'max' => 15),
            array('discount_type', 'length', 'max' => 2),
            array('remark', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array(
                'id, sale_time, client_id, desk_id, zone_id, group_id, employee_id, location_id, sub_total, payment_type, status, remark, discount_amount, discount_type, giftcard_id, empty_flag',
                'safe',
                'on' => 'search'
            ),
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
            'id' => 'ID',
            'sale_time' => 'Sale Time',
            'client_id' => 'Client',
            'desk_id' => 'Desk',
            'zone_id' => 'Zone',
            'group_id' => 'Group',
            'employee_id' => 'Employee',
            'location_id' => 'Location',
            'sub_total' => 'Sub Total',
            'payment_type' => 'Payment Type',
            'status' => 'Status',
            'remark' => 'Remark',
            'discount_amount' => 'Discount Amount',
            'discount_type' => 'Discount Type',
            'giftcard_id' => 'Giftcard',
            'empty_flag' => 'Empty Flag',
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return SaleOrder the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function getOrderCart()
    {

        $sql = "SELECT item_id,currency_code,currency_symbol,`name`,item_number,quantity,
                 price,price_kh,price_kh price_verify,rate to_val,discount_amount discount,(price_kh*quantity)-IFNULL(discount_amount,0) total,
                 NULL description,sale_type
                FROM v_order_cart
                WHERE user_id=:user_id
                AND location_id=:location_id";

        return Yii::app()->db->createCommand($sql)->queryAll(true, array(
                ':user_id' => Common::getUserID(),
                ':location_id' => Common::getCurLocationID()
            )
        );
    }

    public function getOrderToKitchen($sale_id, $location_id, $category_id)
    {
        $sql = "SELECT t1.item_number,t1.item_id,t1.`name`,(t1.quantity-IFNULL(t2.`quantity`,0)) quantity,
                t1.price,t1.discount_amount discount,t1.total,t1.client_id,t1.desk_id,t1.zone_id,t1.employee_id,t1.qty_in_stock,t1.topping,t1.item_parent_id
                FROM v_order_cart t1 LEFT JOIN
                        (SELECT t2.sale_id,t2.item_id,t2.item_parent_id ,t2.quantity
                         FROM sale_order_item_print t2 , item t3
                         WHERE t3.id=t2.item_id
                         AND t3.category_id=:category_id
                        ) t2
                    ON t2.sale_id=t1.`sale_id`
                    AND t2.item_id=t1.item_id
                    AND t2.item_parent_id=t1.item_parent_id
                WHERE t1.sale_id=:sale_id and t1.location_id=:location_id
                AND t1.status=:status
                AND (t1.quantity-IFNULL(t2.quantity,0)) > 0
                AND t1.category_id=:category_id
                ORDER BY t1.path,t1.modified_date";

            return Yii::app()->db->createCommand($sql)->queryAll(true, array(
                ':sale_id' => $sale_id,
                ':location_id' => $location_id,
                ':category_id' => $category_id,
                ':status' => Yii::app()->params['num_one']
            )
        );
    }

    public function getOrderCartTopping($desk_id, $group_id, $location_id)
    {
        $sql = "SELECT item_id,`name`,quantity,price,discount_amount discount,total,
                client_id,desk_id,zone_id,employee_id,qty_in_stock
                FROM v_order_cart
                WHERE desk_id=:desk_id AND group_id=:group_id and location=:location_id
                AND status=:status
                AND topping=1
                ORDER BY modified_date desc";


        return Yii::app()->db->createCommand($sql)->queryAll(true, array(
            ':desk_id' => (int)$desk_id,
            ':group_id' => $group_id,
            ':location_id' => $location_id,
            ':status' => Yii::app()->params['num_one']
        ));
    }

    //public function getAllTotal($desk_id, $group_id, $location_id)
    public function getAllTotal($desk_id,$group_id, $location_id)
    {
        $quantity = 0;
        $sub_total = 0;
        $total = 0;
        $discount_amount = 0;

        $sql="SELECT sale_id,sum(quantity) quantity,
                    SUM(price*quantity) sub_total,
                    SUM(price*quantity) - (SUM(price*quantity)*IFNULL(so.discount_amount,0)/100) total,
                    SUM(price*quantity)*IFNULL(so.discount_amount,0)/100 discount_amount
                FROM v_order_cart oc JOIN sale_order so
                            ON so.id=oc.sale_id 
                            and so.desk_id=oc.desk_id
                            and so.group_id=oc.group_id
                            and so.location_id=oc.location_id
                WHERE oc.desk_id=:desk_id 
                AND oc.group_id=:group_id
                AND oc.location_id=:location_id
                AND oc.status=:status
                AND ISNULL(oc.deleted_at)
                GROUP BY sale_id";


        $result = Yii::app()->db->createCommand($sql)->queryAll(true, array(
            ':desk_id' => $desk_id,
            ':group_id' => $group_id,
            ':location_id' => $location_id,
            ':status' => Yii::app()->params['num_one']
        ));

        if ($result) {
            foreach ($result as $record) {
                $quantity = $record['quantity'];
                $sub_total = $record['sub_total'];
                $total = $record['total'];
                $discount_amount = $record['discount_amount'];
            }
        }

        return array($quantity, $sub_total, $total, $discount_amount);
    }

    public function orderAdd($item_id,$quantity,$price, $discount_amount)
    {

        $sql = "SELECT func_order_add(:item_id,:item_number,:quantity,:price_tier_id,:price,:location_id,:client_id,:employee_id,:user_id,:discount_amount,:discount_type,:sale_type) sale_id";

        $result = Yii::app()->db->createCommand($sql)->queryAll(true,
            array(
                ':item_id' => $item_id,
                ':item_number' => $item_id,
                ':quantity' => $quantity,
                ':price' => $price,
                ':price_tier_id' => Common::getPriceTierID(),
                ':location_id' => Common::getCurLocationID(),
                ':client_id' => Common::getCustomerID(),
                ':employee_id' => Common::getEmployeeID(),
                ':employee_id' => Common::getUserID(),
                ':discount' => $discount_amount,
                ':discount_type' => '%',
                ':sale_type' =>  Common::getSaleType()
            )
        );

        foreach ($result as $record) {
            $id = $record['sale_id'];
        }

        return $id;
    }

    public function orderEdit($sale_id, $item_id, $quantity, $price, $discount, $discount_type)
    {
        $sql = "SELECT func_order_edit(:sale_id,:location_id,:item_id,:quantity,:price,:discount,:discount_type,:employee_id,:user_id) result_id";

        $result = Yii::app()->db->createCommand($sql)->queryAll(true,
            array(
                ':sale_id' => $sale_id,
                ':item_id' => $item_id,
                ':quantity' => $quantity,
                ':price' => $price,
                ':discount' => $discount,
                ':discount_type' => $discount_type,
                ':location_id' => Common::getCurLocationID(),
                ':employee_id' => Common::getEmployeeID(),
                ':user_id' => Common::getUserID()
            )
        );

        foreach ($result as $record) {
            $result_id = $record['result_id'];
        }

        return $result_id;
    }

    public function orderDel($sale_id,$item_id)
    {
        $sql = "SELECT func_order_del(:sale_id,:location_id,:item_id,:employee_id,:user_id) result_id";

        $result = Yii::app()->db->createCommand($sql)->queryAll(true,
            array(
                ':sale_id' => $sale_id,
                ':location_id' => Common::getCurLocationID(),
                ':item_id' => $item_id,
                ':employee_id' => Common::getEmployeeID(),
                ':user_id' => Common::getUserID()
            )
        );

        foreach ($result as $record) {
            $result_id = $record['result_id'];
        }

        return $result_id;
    }

    public function orderSave($sale_id)
    {
        $sql="SELECT func_order_save(:sale_id,:location_id,:employee_id,:user_id) sale_id";

        $result = Yii::app()->db->createCommand($sql)->queryAll(true, array(
                ':sale_id' => $sale_id,
                ':location_id' => Common::getCurLocationID(),
                ':employee_id' => Common::getEmployeeID(),
                ':user_id' => Common::getUserID()
            )
        );

        foreach ($result as $record) {
            $sale_id = $record['sale_id'];
        }

        return $sale_id;

    }

    public function newOrdering()
    {
        $sql="SELECT so.desk_id,d.`name` desk_name, concat(hour(so.sale_time), ':',minute(so.sale_time)) sale_time
                FROM sale_order so JOIN desk d ON d.id = so.desk_id
                WHERE so.location_id = :location_id
                AND so.sale_time >= CURDATE()
                AND so.`status`=:status
                AND temp_status <> :str_zero
                AND employee_id <> :employee_id
                ORDER BY so.sale_time desc";

        $result = Yii::app()->db->createCommand($sql)->queryAll(true, array(
            ':location_id' => Common::getCurLocationID(),
            ':status' => Yii::app()->params['num_one'],
            ':str_zero' => Yii::app()->params['str_zero'],
            ':employee_id' => Common::getEmployeeID()
        ));

        return $result;
    }

    public function getSaleOrderByDeskId()
    {
        $sale_order = SaleOrder::model()->find('desk_id=:desk_id and group_id=:group_id and location_id=:location_id and status=:status',
            array(
                ':desk_id' => Common::getTableID(),
                ':group_id' => Common::getGroupID(),
                ':location_id' => Common::getCurLocationID(),
                ':status' => Yii::app()->params['num_one']
            ));

        return isset($sale_order) ? $sale_order : null;
    }

    public function getSaleOrderById($sale_id,$location_id)
    {
        $sale_order = SaleOrder::model()->find('id=:sale_id and location_id=:location_id and status=:status',
            array(
                ':sale_id' => $sale_id,
                ':location_id' => $location_id,
                ':status' => Yii::app()->params['num_one']
            ));

        return isset($sale_order) ? $sale_order : null;
    }

    public function updateSaleOrderTempStatus($status)
    {
        $model = $this->getSaleOrderByDeskId();
        if ($model !== null) {
            $model->temp_status = $status;
            $model->save();
        }
    }

}
