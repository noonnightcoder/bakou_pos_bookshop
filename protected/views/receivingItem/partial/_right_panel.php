<div class="col-xs-12 col-sm-4 widget-container-col">
    <!-- #section:canel-cart.layout -->
    <?php foreach ($items as $item) $receive_id=$item['receive_id']; ?>
    <div class="row">
        <div id="cancel_cart">
            <?php if ($count_item <> 0) { ?>
                <?php
                $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
                    'id' => 'cancel_recv_form',
                    //'action' => Yii::app()->createUrl('receivingItem/cancelRecv/'),
                    'action' => array('cancelRecv', 'receive_id'=>$receive_id),
                    'layout' => TbHtml::FORM_LAYOUT_INLINE,
                ));
                ?>
                <div align="right">
                    <?php
                    echo TbHtml::linkButton(Yii::t('app', 'Save Transaction'), array(
                        'color' => TbHtml::BUTTON_COLOR_INFO,
                        'size' => TbHtml::BUTTON_SIZE_SMALL,
                        'icon' => 'ace-icon fa fa-save white',
                        'url' => array('CompleteRecv', 'receive_id'=>$receive_id,'trans_mode'=>Yii::app()->receivingCart->getMode(),'save_status'=>2),
                        'class' => 'suspend-sale',
                        'title' => Yii::t('app', 'Suspend Sale'),
                    ));
                    ?>

                    <?php
                    echo TbHtml::linkButton(Yii::t('app', 'Cancel'), array(
                        'color' => TbHtml::BUTTON_COLOR_DANGER,
                        'size' => TbHtml::BUTTON_SIZE_SMALL,
                        'icon' => '	glyphicon-remove white',
                        'url' => array('cancelRecv', 'receive_id'=>$receive_id),
                        'class' => 'cancel-receiving',
                        'id' => 'cancel_receiving_button',
                        'title' => Yii::t('app', 'Cancel Transaction'),
                    ));
                    ?>
                </div>
                <?php $this->endWidget(); ?>
            <?php } ?>
        </div>
    </div>
    <!-- #section:canel-cart.layout -->

    <div class="row">
        <div class="sidebar-nav" id="supplier_cart">
            <?php
            if ($trans_mode == 'physical_count') {
                $this->widget('yiiwheels.widgets.box.WhBox', array(
                    'title' => Yii::t('app', 'Count By') . ' : ' . ucwords(Yii::app()->session['emp_fullname']),
                    'headerIcon' => 'menu-icon fa fa-users',
                    'htmlHeaderOptions' => array('class' => 'widget-header-flat widget-header-small'),
                    'content' => $this->renderPartial('partial/_employee',
                        array('model' => $model, 'count_item' => $count_item, 'trans_mode' => $trans_mode), true)
                ));
            } else {
                if (isset($supplier)) {
                    $this->widget('yiiwheels.widgets.box.WhBox', array(
                        'title' => Yii::t('app', 'Supplier Info'),
                        'headerIcon' => 'menu-icon fa fa-info-circle',
                        'htmlHeaderOptions' => array('class' => 'widget-header-flat widget-header-small'),
                        'content' => $this->renderPartial('partial/_supplier_selected',
                            array('model' => $model, 'supplier' => $supplier, 'trans_mode' => $trans_mode), true),
                    ));
                } else {
                    $this->widget('yiiwheels.widgets.box.WhBox', array(
                        'title' => Yii::t('app', 'Select Supplier (Optional)'),
                        'headerIcon' => 'menu-icon fa fa-users',
                        'htmlHeaderOptions' => array('class' => 'widget-header-flat widget-header-small'),
                        'content' => $this->renderPartial('partial/_supplier',
                            array('model' => $model, 'count_item' => $count_item, 'trans_mode' => $trans_mode),
                            true)
                    ));
                }
            }
            ?>
        </div>
    </div>

    <div class="row">
        <div id="task_cart">
            <?php $box = $this->beginWidget('yiiwheels.widgets.box.WhBox', array(
                'title' => Yii::t('app', 'Total Quantity') . ' : ' . $count_item,
                'headerIcon' => 'menu-icon fa fa-tasks',
                'htmlHeaderOptions' => array('class' => 'widget-header-flat widget-header-small'),
            )); ?>

            <table class="table table-bordered table-condensed">
                <tbody>
                <tr>
                    <td><?php echo Yii::t('app', 'Item in Cart'); ?> :</td>
                    <td><?php echo $count_item; ?></td>
                </tr>
                <?php foreach (Receiving::model()->getDiscountAmount(@$receive_id)as $discount) {?>
                    <?php if (@$discount['discount_amount'] > 0) { ?>
                        <tr>
                            <td><?php echo Yii::t('app', 'Sub Total'); ?> :</td>
                            <td><span class="badge badge-info bigger-120"><?php echo '៛' . number_format(@$discount['sub_total'],Common::getDecimalPlace(), '.', ','); ?></span></td>
                        </tr>
                        <tr>
                            <td><?php echo Yii::t('app', 'Discount'); ?> :</td>
                            <td><span class="badge badge-info bigger-120"><?php echo '៛' . number_format($discount['discount_amount'],2, '.', ','); ?></span></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                <?php foreach ($total_mc as $id => $totalmc): ?>
                    <tr>
                        <td><?php echo Yii::t('app', 'Total in ') . $totalmc["currency_id"]; ?> :</td>
                        <td>
                                            <span class="badge badge-primary bigger-120">
                                                <?php echo $totalmc['currency_symbol']. number_format(@$totalmc["total"],Common::getDecimalPlace(), '.', ',');?>
                                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($count_item <> 0) { ?>
                <div align="right">

                    <?php echo TbHtml::linkButton(Yii::t('app', 'Done'), array(
                        'color' => TbHtml::BUTTON_COLOR_SUCCESS,
                        'size' => TbHtml::BUTTON_SIZE_SMALL,
                        'icon' => 'glyphicon-off white',
                        'url' => array('CompleteRecv', 'receive_id'=>$receive_id,'trans_mode'=>Yii::app()->receivingCart->getMode(),'save_status'=>0),
                        'class' => 'complete-recv',
                        'title' => Yii::t('app', 'Complete'),
                    )); ?>
                </div>
            <?php } ?>
            <?php $this->endWidget(); ?> <!--/endtaskwidget-->
        </div>
    </div>
</div>