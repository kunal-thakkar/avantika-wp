<?php
defined('_VALID_EXEC') or die('access denied');
?>


<div class="com_shop">

    <form name='adminForm' method='post' action='<?php echo admin_url('admin.php?page=component_com_shop-shipping'); ?>'>
        <input type='hidden' name='task' value='delete_rates' />
        <input type='hidden' name='carrier' value='<?php echo $this->carrier->pk(); ?>' />

        <table class="wp-list-table widefat fixed shipping-rates">
            <thead>
                <tr>
                    <th style='padding:0; width:24px;'>
                        <input type="checkbox" id="toggle" value=""  />
                    </th>
                    <th>
                        <?php _e('Name', 'com_shop'); ?>		    </th>


                    <th>
                        <?php _e('Priority', 'com_shop'); ?>		    </th>
                </tr>
            </thead>


            <?php
            $c = 0;
            while ($o = $this->rows->nextObject()):
                $c++;
                ?>


                <tr class="row<?php echo (int) (bool) $c % 2; ?>">
                    <td>
                        <input type="checkbox"  name="ids[]" value="<?php echo $o->shipping_rate_id; ?>"  />
                    </td>

                    <td>

                        <button onclick="window.location.href = '<?php echo admin_url('admin.php?page=component_com_shop-shipping&task=add_rate&id=' . $o->shipping_rate_id); ?>';
                                return false;" class="btn btn-small">
                            <span class="icon-edit">
                            </span>
                            <?php echo strings::htmlentities($o->shipping_rate_name); ?>
                        </button>
                    </td>


                    <td align="left">
                        <?php echo $o->shipping_rate_priority; ?>

                    </td>

                </tr>

            <?php endwhile; ?>

        </table>
    </form>

    <?php echo $this->pagination->getPagesLinks(); ?>
</div>