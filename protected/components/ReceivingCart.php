<?php

if (!defined('YII_PATH'))
    exit('No direct script access allowed');

class ReceivingCart extends CApplicationComponent
{

    //private $quantity;

    private $session;

    //private $quantity=1;

    public function getSession()
    {
        return $this->session;
    }

    public function getDecimalPlace()
    {
        return Yii::app()->settings->get('system', 'decimalPlace') == '' ? 2 : Yii::app()->settings->get('system', 'decimalPlace');
    }

    public function setSession($value)
    {
        $this->session = $value;
    }

    /*public function getCart()
    {
        $this->setSession(Yii::app()->session);
        if (!isset($this->session['cartRecv'])) {
            $this->setCart(array());
        }
        return $this->session['cartRecv'];
    }*/

    public function setCart($cart_data)
    {
        $this->setSession(Yii::app()->session);
        $this->session['cartRecv'] = $cart_data;
        //$session=Yii::app()->session;
        //$session['cartRecv']=$cart_data;
    }

    /*
     * To get payment session
     * $return $session['payment']
     */

    public function getPayments()
    {
        $this->setSession(Yii::app()->session);
        if (!isset($this->session['recv_payments'])) {
            $this->setPayments(array());
        }
        return $this->session['recv_payments'];
    }

    public function setPayments($payments_data)
    {
        $this->setSession(Yii::app()->session);
        $this->session['recv_payments'] = $payments_data;
    }

    public function getComment()
    {
        $this->setSession(Yii::app()->session);
        return $this->session['recv_comment'];
    }

    public function setComment($comment)
    {
        $this->setSession(Yii::app()->session);
        $this->session['recv_comment'] = $comment;
    }

    public function clearComment()
    {
        $this->setSession(Yii::app()->session);
        unset($this->session['recv_comment']);
    }

    public function clearMode()
    {
        $this->setSession(Yii::app()->session);
        unset($this->session['recv_mode']);
    }

    public function getSupplier()
    {
        $this->setSession(Yii::app()->session);
        if (!isset($this->session['recv_supplier'])) {
            $this->setSupplier(null);
        }
        return $this->session['recv_supplier'];
    }

    public function setSupplier($supplier_data)
    {
        $this->setSession(Yii::app()->session);
        $this->session['recv_supplier'] = $supplier_data;
    }

    public function removeSupplier()
    {
        $this->setSession(Yii::app()->session);
        unset($this->session['recv_supplier']);
    }

    function getMode()
    {
        $this->setSession(Yii::app()->session);
        if (!isset($this->session['recv_mode'])) {
            $this->setMode('receive');
        }
        return $this->session['recv_mode'];
    }

    function setMode($mode)
    {
        $this->setSession(Yii::app()->session);
        $this->session['recv_mode'] = $mode;
    }

    /*public function addItem($item_id, $quantity = 1, $discount = '0', $cost_price = null, $unit_price = null, $description = null, $expire_date = null)
    {
        $this->setSession(Yii::app()->session);
        $item_number = $item_id;
        //Get all items in the cart so far...
        $items = $this->getCart();

        $models = Item::model()->getItemPriceTierWS($item_id, null);

        if (empty($models)) {
            $models = Item::model()->getItemPriceTierItemNumWS($item_id, null);
            foreach ($models as $model) {
                $item_id=$model["id"];
            }
        }

        if (!$models) {
            return false;
        }

        foreach ($models as $model) {
            $item_data = array((int)$item_id =>
                array(
                    'item_id' => $model["id"],
                    'currency_code' => $model["currency_code"],
                    'currency_id' => $model["currency_id"],
                    'currency_symbol' => $model["currency_symbol"],
                    'name' => $model["name"],
                    'item_number' => $model["item_number"],
                    'quantity' => $quantity,
                    'cost_price' => $cost_price != null ? round($cost_price, $this->getDecimalPlace()) : round($model["cost_price"], $this->getDecimalPlace()),
                    'unit_price' => $unit_price != null ? round($unit_price, $this->getDecimalPlace()) : round($model["unit_price"], $this->getDecimalPlace()),
                    'discount' => $discount,
                    'expire_date' => $expire_date,
                    'description' => $description != null ? $description : $model["description"],
                    'is_expire' => $model["is_expire"],
                )
            );
        }

        if (isset($items[$item_id])) {
            $items[$item_id]['quantity']+=$quantity;
        } else {
            $items += $item_data;
        }

        $this->setCart($items);
        return true;
    }*/

    public function addItem($item_id)
    {
        $userid=Common::getUserID();
        $employeeid=Common::getEmployeeID();
        Receiving::model()->addItem($item_id,1,null,$employeeid,$userid);
        return true;
    }

    public function getCart()
    {
        $userid=Common::getUserID();
        return Receiving::model()->getItem($userid);
    }

    /*public function editItem($item_id, $quantity, $discount, $cost_price, $unit_price, $description, $expire_date)
    {
        $items = $this->getCart();
        if (isset($items[$item_id])) {
            $items[$item_id]['quantity'] = $quantity !=null ? $quantity : $items[$item_id]['quantity'];;
            $items[$item_id]['discount'] = $discount !=null ? $discount : $items[$item_id]['discount'];;
            $items[$item_id]['cost_price'] = $cost_price !=null ? round($cost_price, $this->getDecimalPlace()) : $items[$item_id]['cost_price'];
            $items[$item_id]['unit_price'] = $unit_price !=null ? round($unit_price, $this->getDecimalPlace()) : $items[$item_id]['unit_price'];
            $items[$item_id]['expire_date'] = $expire_date !=null ? $expire_date : $items[$item_id]['expire_date'];
            $items[$item_id]['description'] = $description;
            $this->setCart($items);
        }

        return false;
    }*/

    public function editItem($receive_id,$item_id, $quantity, $discount, $cost_price, $unit_price, $description = null, $expire_date = null)
    {
        Receiving::model()->editItem($receive_id,$item_id, $quantity, $discount, $cost_price, $unit_price);
        return false;
    }
    
    /*public function deleteItem($item_id)
    {
        $items = $this->getCart();
        unset($items[$item_id]);
        $this->setCart($items);
    }*/

    public function deleteItem($receive_id,$item_id)
    {
        Receiving::model()->deleteItem($receive_id,$item_id);
        return false;
    }

    public function cancelItem($receive_id='',$receive_status_ch='',$receive_status='')
    {
        Receiving::model()->cancelItem($receive_id,$receive_status_ch,$receive_status);
    }

    public function outofStock($item_id)
    {
        $item = Item::model()->findbyPk($item_id);

        if (!$item)
            return false;

        $quanity_added = $this->getQuantityAdded($item_id);

        if ($item->quantity - $quanity_added < 0) {
            return true;
        }

        return false;
    }

    protected function getQuantityAdded($item_id)
    {
        $items = $this->getCart();
        $quanity_already_added = 0;
        foreach ($items as $item) {
            if ($item['item_id'] == $item_id) {
                $quanity_already_added+=$item['quantity'];
            }
        }

        return $quanity_already_added;
    }

    protected function emptyCart()
    {
        $this->setSession(Yii::app()->session);
        unset($this->session['cartRecv']);
    }

    /*
     * To add payment to payment session $_SESSION['payment']
     * @param string $payment_id as payment type, float $payment_amount amount of payment 
     */

    public function addPayment($payment_id, $payment_amount)
    {
        $this->setSession(Yii::app()->session);
        $payments = $this->getPayments();
        $payment = array($payment_id =>
            array(
                'payment_type' => $payment_id,
                'payment_amount' => $payment_amount
            )
        );

        //payment_method already exists, add to payment_amount
        if (isset($payments[$payment_id])) {
            $payments[$payment_id]['payment_amount'] += $payment_amount;
        } else {
            //add to existing array
            $payments += $payment;
        }

        $this->setPayments($payments);
        return true;
    }

    public function deletePayment($payment_id)
    {
        $payments = $this->getPayments();
        unset($payments[$payment_id]);
        $this->setPayments($payments);
    }

    protected function emptyPayment()
    {
        $this->setSession(Yii::app()->session);
        unset($this->session['payments']);
    }

    public function getSubTotal()
    {
        $subtotal = 0;
        $items = $this->getCart();
        foreach ($items as $id => $item) {
            /*if (substr($item['discount'], 0, 1) == '$') {
                $subtotal+=($item['cost_price'] * $item['quantity'] - substr($item['discount'], 1));
            } else {
                $subtotal+=($item['cost_price'] * $item['quantity'] - $item['cost_price'] * $item['quantity'] * $item['discount'] / 100);
            }*/
            $subtotal+= Common::calTotalAfterDiscount($item['discount'],$item['cost_price'],$item['quantity']);
        }
        return round($subtotal, $this->getDecimalPlace());
    }

    /**
     * Returns total price for all units of the position
     * @param bool $withDiscount
     * @return float
     *
     */
    public function getTotal()
    {
        $total = 0;
        foreach ($this->getCart() as $item) {
            /*if (substr($item['discount'], 0, 1) == '$') {
                $total+=round($item['cost_price'] * $item['quantity'] - substr($item['discount'], 1), Common::getDecimalPlace(), PHP_ROUND_HALF_DOWN);
            } else {
                $total+=round($item['cost_price'] * $item['quantity'] - $item['cost_price'] * $item['quantity'] * $item['discount'] / 100, Common::getDecimalPlace(), PHP_ROUND_HALF_DOWN);
            }*/
            $total+= Common::calTotalAfterDiscount($item['discount'],$item['cost_price'],$item['quantity']);
        }

        //$total = $total - Common::calDiscountAmount($this->getTotalDiscount(),$total);

        /* Have to calculate with tax if there is a tax */
        /*
          foreach($this->getCart() as $tax)
          {
          $total+=$tax;
          }
         *
         */

        return round($total, $this->getDecimalPlace());
    }

    public function getTotalMC($receive_id='')
    {
        $currency_type = CurrencyType::model()->getActiveCurrency();
        $total_mc = array();

        foreach ($currency_type as $i=>$currency) {
            $total_=0;
            foreach (Receiving::model()->getTotalAmount($receive_id) as $item) {
                if ( $item['currency_code'] == $currency->code ) {
                    $total_ =$item['total'];
                }
            }

            //$total_ = $total_ - $total_*$this->getTotalDiscount()/100;
            $total_data= array((int)$currency->code=>
                array(
                    'currency_code' => $currency->code,
                    'currency_id' => $currency->currency_id,
                    'currency_symbol' => $currency->currency_symbol,
                    'total' => $total_, //. $currency->currency_id,
                )
            );

            $total_mc += $total_data;
        }

        return $total_mc;
    }

    //Alain Multiple Payments
    public function getPaymentsTotal()
    {
        $subtotal = 0;
        foreach ($this->getPayments() as $payments) {
            $subtotal+=$payments['payment_amount'];
        }
        return $subtotal;
    }

    //Alain Multiple Payments
    public function getAmountDue()
    {
        //$amount_due=0;
        $sales_total = $this->getTotal();
        $payment_total = $this->getPaymentsTotal();
        $amount_due = number_format((float) ($sales_total - $payment_total), 2, '.', '');
        return $amount_due;
    }

    //get Total Quatity
    public function getQuantityTotal()
    {
        $qtytotal = 0;
        foreach ($this->getCart() as $line => $item) {
            $qtytotal+=$item['quantity'];
        }
        return $qtytotal;
    }

    public function copyEntireReceiving($receiving_id)
    {
        $this->clearAll();
        $receiving = Receiving::model()->findbyPk($receiving_id);
        $receiving_item = ReceivingItem::model()->getReceivingItem($receiving_id);

        //$payments= ReceivSalePayment::model()->getPayment($sale_id);

        foreach ($receiving_item as $row) {
            $item_expire = ItemExpire::model()->findByAttributes(array('item_id' => $row->item_id, 'receiving_id' => $receiving_id));
            $expire_date = null;

            if ($item_expire)
                $expire_date = $item_expire->expire_date;

            if ($row->discount_type == '$') {
                $discount_amount = $row->discount_type . $row->discount_amount;
            } else {
                $discount_amount = $row->discount_amount;
            }
            $this->addItem($row->item_id, $row->quantity, $discount_amount, $row->price, $row->description, $expire_date);
        }

        /*
          foreach($payments as $row)
          {
          $this->addPayment($row->payment_type,$row->payment_amount);
          }
         * 
         */

        $this->setSupplier($receiving->supplier_id);
        $this->setComment($receiving->remark);
    }

    public function copyEntireSuspendSale($sale_id)
    {
        $this->clearAll();
        $sale = SaleSuspended::model()->findbyPk($sale_id);
        $sale_item = SaleSuspendedItem::model()->getSaleItem($sale_id);
        $payments = SaleSuspendedPayment::model()->getPayment($sale_id);

        foreach ($sale_item as $row) {
            if ($row->discount_type == '$') {
                $discount_amount = $row->discount_type . $row->discount_amount;
            } else {
                $discount_amount = $row->discount_amount;
            }

            $this->addItem($row->item_id, $row->quantity, $discount_amount, $row->price, $row->description);
        }
        foreach ($payments as $row) {
            $this->addPayment($row->payment_type, $row->payment_amount);
        }

        $this->setCustomer($sale->client_id);
        $this->setComment($sale->remark);
    }

    /*public function getTotalDiscount()
    {
        $this->setSession(Yii::app()->session);
        if (!isset($this->session['recv_totaldiscount'])) {
            $this->setTotalDiscount(null);
        }
        return $this->session['recv_totaldiscount'];
    }*/

    /*public function setTotalDiscount($data)
    {
        $this->setSession(Yii::app()->session);
        $this->session['recv_totaldiscount'] = $data;
    }*/

    public function setTotalDiscount($receive_id,$discount_amount,$discount_type,$user_id)
    {
        return Receiving::model()->receiveDiscount($receive_id,$discount_amount,$discount_type,$user_id);
    }

    public function clearTotalDiscount()
    {
        $this->setSession(Yii::app()->session);
        unset($this->session['recv_totaldiscount']);
    }

    public function clearAll()
    {
        $this->emptyCart();
        $this->emptyPayment();
        $this->removeSupplier();
        $this->clearComment();
        $this->clearMode();
        $this->clearTotalDiscount();
    }

}

?>
