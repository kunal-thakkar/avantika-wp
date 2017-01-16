<?php if (!empty($this->row->attributes)) { ?>
    <div class='product-attributes'>
        <h2><?php _e('Select Options', 'com_shop'); ?></h2>
        <?php foreach ((array) $this->row->attributes as $att) { ?>
            <div class="att-container" id="att-<?php echo $att->attribute_id; ?>">
                <h4 class='attribute-name'><?php echo strings::htmlentities($att->attribute_name); ?></h4>
                <select name='property[<?php echo $att->attribute_id; ?>]' class='form-control property <?php echo $att->attribute_required == 'yes' ? "required" : ""; ?>'  onchange="shop_helper.recalc_price(<?php echo $this->row->product->id; ?>);">
                    <option></option>
                    <?php
                    if (!empty($att->properties)) {
                        foreach ((array) $att->properties as $prop) {
                            ?>
                            <option value='<?php echo $prop->property_id; ?>' <?php echo (in_array($prop->property_id, $this->row->selected_props)) ? 'selected="selected"' : ''; ?> ><?php echo strings::htmlentities($prop->property_name); ?></option>
                        <?php } ?>
                    </select>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
<?php } ?>