<div id="account-view">
    <div class="box-account table-responsive clearfix">
        <div class="box-head clearfix">
            <h3><?php _e("Recent Orders", "com_shop"); ?></h3>
        </div>
        <table class="data-table table" id="my-orders-table">

            <thead>
                <tr>
                    <th><?php _e('Order #', 'com_shop'); ?></th>
                    <th><?php _e('Date', 'com_shop'); ?></th>
                    <th><?php _e('Ship To', 'com_shop'); ?></th>
                    <th><span class="nobr"><?php _e('Order Total', 'com_shop'); ?></span></th>
                    <th><?php _e('Status', 'com_shop'); ?></th>
                    <th><?php _e('View Order', 'com_shop'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ((array) $this->orders as $order) { ?>
                    <tr>
                        <td><?php echo strings::htmlentities($order->ID); ?></td>
                        <td><span class="nobr"><?php echo $order->cdate; ?></span></td>
                        <td><?php echo strings::htmlentities($order->shipping_first_name . " " . $order->shipping_last_name); ?></td>
                        <td><span class="price"><?php echo $this->price->format($order->order_total, $order->currency_sign); ?></span></td>
                        <td><em><?php echo strings::htmlentities($order->order_status); ?></em></td>
                        <td class="a-center">
                            <a class="btn btn-small" href="<?php echo Route::get('component=shop&con=account&task=view_order&id=' . $order->ID . "&order_key=" . $order->post_password . "&customer_email=" . $order->billing_email); ?>"><span class="icon-search"></span></a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td  class="a-center" colspan="6">
                        <?php if (!empty($this->pagination)): ?>

                            <ul class="pagination pagination-sm">
                                <?php foreach ((array) $this->pagination as $k => $v) : ?> 

                                    <?php if (strings::stripos($v, "current") !== false && strings::stripos($v, "<a") === false) : ?>
                                        <li class="active"> <?php echo $v; ?></li>
                                    <?php else: ?>
                                        <li><?php echo $v; ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                            </ul>

                        <?php endif; ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="box-accoun table-responsive clearfixt">
        <div class="box-head clearfixd">
            <h3><?php _e('Available downloads', 'com_shop'); ?></h3>
        </div>
        <table class="data-table table" id="my-orders-table">
            <thead>
                <tr>
                    <th><?php _e('Order #', 'com_shop'); ?></th>
                    <th><?php _e('Item', 'com_shop'); ?></th>
                    <th><?php _e('File', 'com_shop'); ?></th>
                    <th><?php _e('Download', 'com_shop'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $c1 = 1;
                $c2 = 1;
                foreach ((array) $this->files as $file):
                    ?>
                    <tr>
                        <?php
                        if ($c1 == 1 || $c1 > $file->order_files_count) {
                            $c1 = 1;
                            ?>
                            <td rowspan="<?php echo (int) $file->order_files_count; ?>"><?php echo $file->order_id; ?></td>
                        <?php } ?>
                        <?php
                        if ($c2 == 1 || $c2 > $file->order_item_files_count) {
                            $c2 = 1;
                            ?>
                            <td rowspan="<?php echo (int) $file->order_item_files_count; ?>"><?php echo strings::htmlentities($file->product_name); ?></td>
                        <?php } ?>
                        <td><?php echo get_the_title($file->file_id); ?> </td>
                        <td class="a-center"><a class="btn btn-small" href="<?php echo home_url("?order_key=" . $file->order_key . "&file=" . $file->file_id) . "&item=" . $file->item_id; ?>"><span class="icon-download"></span></a></td>

                    </tr>
                    <?php
                    if ($file->order_files_count > 1)
                        $c1++;
                    if ($file->order_item_files_count > 1)
                        $c2++;

                endforeach;
                ?>
            </tbody>
            <tfoot>
            </tfoot>
            </table>
    </div>

    <form name="checkout"  method="post" class="checkout" action="<?php echo Route::get("component=shop&con=account"); ?>">
        <input type="hidden" name="task" value="save_account" />
        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-13 text-right">
                    <?php _e('Ship to billing address:', 'com_shop'); ?> 
                    <input <?php echo $this->ship_to_billing ? "checked=checked" : ''; ?> name="ship_to_billing" type="checkbox" value="1"  />
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <div class="row">
                        <div class="col-xs-12">
                            <h4><?php _e('Default Billing Address', 'com_shop'); ?></h4>
                        </div>
                    </div>
                    <div class="row">
                        <?php while ($field = $this->billing->get_field()) : ?>
                            <div class="col-xs-12 col-md-6">
                                <label class="control-label" for="<?php echo $field->get_name(); ?>">
                                    <?php echo $field->get_label(); ?>
                                    <?php if ($field->required()): ?>
                                        <span class="srequired">*</span>
                                    <?php endif; ?>
                                </label>

                                <?php if ($field instanceof state && $field->get_name() == 'billing_state') : ?>
                                    <span id='billing_states_container'><?php echo $field->render(); ?></span>
                                <?php else: ?>
                                    <?php echo $field->render(); ?>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <div class="row">
                        <div class="col-xs-12">
                            <h4><?php _e('Default Shipping Address', 'com_shop'); ?></h4>
                        </div>
                    </div>
                    <div class="row">
                        <?php while ($field = $this->shipping->get_field()): ?>

                            <div class="col-xs-12 col-md-6">
                                <label class="control-label" for="<?php echo $field->get_name(); ?>">
                                    <?php echo $field->get_label(); ?>
                                    <?php if ($field->required()): ?>
                                        <span class="srequired">*</span>
                                    <?php endif; ?>
                                </label>

                                <?php if ($field instanceof state && $field->get_name() == 'shipping_state') : ?>
                                    <span id='shipping_states_container'><?php echo $field->render(); ?></span>
                                <?php else: ?>
                                    <?php echo $field->render(); ?>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 text-right">
                    <button class="btn btn-success">
                        <span class="icon-ok"></span>
                        <?php _e("Save", "com_shop"); ?>      
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>