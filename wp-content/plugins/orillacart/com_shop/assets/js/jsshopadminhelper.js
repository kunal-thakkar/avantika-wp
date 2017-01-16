;
(function (window, $) {

    window.jsShopAdminHelper = new function () {

        this.data = {};

        this.shipping = new function () {

            this.loadClassOptions = function (name, carrier_id) {

                jQuery.post(
                        ajaxurl + "?action=ajax-call-admin&component=shop&con=shipping&task=get_class_options", {
                            "class": name,
                            "carrier_id": carrier_id
                        },
                function (data) {
                    document.getElementById('class_options').innerHTML = data;
                });
            }

        }

        this.payment = new function () {

            this.loadClassOptions = function (name) {

                jQuery.post(
                        ajaxurl + "?action=ajax-call-admin&component=shop&con=payment&task=get_class_options", {
                            "class": name
                        },
                function (data) {
                    document.getElementById('class_options').innerHTML = data;
                });
            }

        }

        this.loadStatesByCountries = function (sl, id) {

            var vals = jQuery(sl).val() || [];
            jQuery.post(
                    ajaxurl + "?action=ajax-call-admin&component=shop&con=shipping&task=load_states", {
                        "countries": vals
                    },
            function (data) {
                document.getElementById(id).innerHTML = data;
            });
        }

        this.loadStates = function (c, id) {

            jQuery.ajax({
                type: "get",
                url: ajaxurl + "?action=ajax-call-admin&component=shop&con=country&task=ajax_get_states&country=" + c,
                success: function (data, text) {
                    document.getElementById(id).innerHTML = data;

                },
                error: function (request, status, error) {
                    jQuery("#ajax_sys_msg" + id).html(request.responseText);

                }

            });

        }

        this.user = new function () {

            this.get_country_states = function (c, id, type) {

                jQuery.ajax({
                    type: "get",
                    url: ajaxurl + "?action=ajax-call-admin&component=shop&con=user&task=country_states&country=" + c + "&type=" + type,
                    success: function (data, text) {
                        document.getElementById(id).innerHTML = data;

                    },
                    error: function (request, status, error) {
                        jQuery("#ajax_sys_msg" + id).html(request.responseText);

                    }

                });

            }

        }

        this.attribute = new function () {

            this.saved = true;
            this.type = '';

            this.stockRoom = function (id, obj, type) {

                this.type = type;

                if (!document.getElementById(type + id + '_stock_room')) {

                    room = document.createElement('div');
                    room.innerHTML = "<div id='ajax_sys_msg" + id + "' style='color:red; text-align:center;'></div><div>" + document.getElementById('stock_rooms').innerHTML + "<div/> <table id='" + type + id + "_stock_rooms_container'></table>";
                    room.setAttribute('id', type + id + '_stock_room');
                    room.setAttribute('style', 'display:none');
                    obj.parentNode.appendChild(room);
                    jQuery(room).find('#stock_room_selector').bind('change', function () {

                        jsShopAdminHelper.attribute.addStock(this, id);

                    });
                } else {

                    jQuery('#' + type + id + '_stock_room').find('#selector_container').html(document.getElementById('stock_rooms').innerHTML);

                    jQuery('#' + type + id + '_stock_room').find('#stock_room_selector').bind('change', function () {

                        jsShopAdminHelper.attribute.addStock(this, id);

                    });
                }

                this.loadStocks(obj, id);

                jQuery('#' + type + id + '_stock_room').dialog({
                    //autoOpen: false,
                    resizable: false,
                    modal: true,
                    draggable: false,
                    width: 400,
                    height: 450,
                    overlay: {
                        backgroundColor: "#000",
                        opacity: 0.5
                    },
                    buttons: {
                        "apply": function () {
                            jsShopAdminHelper.attribute.post(id, this);
                        }
                    },
                    close: function (ev, ui) {
                    }
                });

                return true;

            }

            this.loadStocks = function (o, id) {

                var parent = this;
                jQuery.ajax({
                    type: "get",
                    url: ajaxurl + "?action=ajax-call-admin&component=shop&con=attributes&task=get_stock&type=" + jsShopAdminHelper.attribute.type + "&id=" + id,
                    success: function (data, text) {

                        if (data.length > 0) {

                            var table = (jQuery("#" + parent.type + id + "_stock_rooms_container")[0]);

                            table.innerHTML = '';

                            for (var c = 0; c < data.length; c++) {

                                tr = document.createElement('tr');
                                tr.innerHTML = "<td>" + data[c].stockroom_name + "</td><td> <input style='display:block;' type='text' id='stockroom" + data[c].property_id + data[c].stockroom_id + "' name='" + data[c].stockroom_id + "' value='" + data[c].stock + "' /></td><td><input type='button' value='X' onclick='jQuery(this.parentNode.parentNode).remove();' /></td>";

                                table.appendChild(tr);

                            }
                        }

                    },
                    error: function (request, status, error) {
                        jQuery("#ajax_sys_msg" + id).html(request.responseText);

                    }
                });

            }

            this.post = function (id, o) {

                data = {};

                data['id'] = id;
                data['values'] = {};

                jQuery('input:text', o).each(function (i, v) {

                    data['values'][v.name] = v.value;
                });

                jQuery.post('admin.php?page=component_com_shop&con=attributes&task=save_stock&object=' + this.type, data, function (result, status) {
                    jQuery("#ajax_sys_msg" + id).html(result.msg);
                });
            }

            this.addStock = function (o, att) {

                if (!o.value || document.getElementById('stockroom' + att + o.value))
                    return false;

                var table = (jQuery("#" + this.type + att + "_stock_rooms_container")[0]);

                tr = document.createElement('tr');

                tr.innerHTML = "<td>" + jQuery("option:selected", o.parentNode).text() + "</td><td> <input style='display:block;' type='text' id='stockroom" + att + o.value + "' name='" + o.value + "' value='' /></td><td><input type='button' value='X' onclick='jQuery(this.parentNode.parentNode).remove();' /></td>";

                table.appendChild(tr);

            }

            this.create_variation = function () {

                jQuery('#variations_manager_container').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff ',
                        opacity: 0.6
                    }

                });


                jQuery.ajax({
                    type: "post",
                    url: ajaxurl + "?action=ajax-call-admin&component=shop&con=products&task=create_variation&parent=" + jQuery("input#post_ID").val(),
                    data: jQuery('input#variation_sku,input#variation_title,input#variation_price,select.property').serialize(),
                    success: function (data, text) {
                        alert(data.msg);
                        jQuery("#variations_manager_container").html(data.variations);
                        jQuery('#variations_manager_container').unblock();

                    }

                });

                return false;
            },
                    this.delete_variation = function (id) {

                        jQuery('#variations_manager_container').block({
                            message: null,
                            overlayCSS: {
                                background: '#fff ',
                                opacity: 0.6
                            }

                        });

                        jQuery.ajax({
                            type: "get",
                            url: ajaxurl + "?action=ajax-call-admin&component=shop&con=products&task=delete_variation&id=" + id,
                            success: function (data, text) {
                                alert(data.msg);
                                jQuery("#variations_manager_container").html(data.variations);
                                jQuery('#variations_manager_container').unblock();

                            }

                        });

                    }

        }

        this.products = new function () {

            this.addAttributeSet = function (o) {

                if (!o.value || document.getElementById('attribute_bank' + o.value))
                    return false;

                var table = document.getElementById('attribute_bank_assocs');
                tr = document.createElement('tr');

                tr.innerHTML = "<td>" + jQuery("option:selected", o).text() + "</td><td align='center'> <input type='hidden' id='attribute_bank" + o.value + "' name='attribute_bank_assoc[]' value='" + o.value + "' />	  <button class='btn btn-danger btn-small' onclick='jQuery(this.parentNode.parentNode).remove(); return false;' ><span class='icon-trash'></span></button></td>";

                table.appendChild(tr);
            }

            this.addStock = function (o) {

                if (!o.value || document.getElementById('stockroom' + o.value))
                    return false;

                var table = document.getElementById('stockRoomContainer');

                div = document.createElement('div');

                div.innerHTML = "<li><label for='stockroom" + o.value + "'>" + jQuery("option:selected", o.parentNode).text() + "</label><input type='text' id='stockroom" + o.value + "' name='stock_assoc[" + o.value + "]' value='' /><button onclick='jQuery(this.parentNode).remove();' class='btn btn-danger btn-small'><span class='icon-trash'></span></button></li>";

                jQuery(div).insertBefore("ul li#stockRoomsDelimiter");

            }

            this.addProductToCat = function (c) {

                var form = document.getElementById('post');

                switch (c.checked) {

                    case true:

                        if (document.getElementById("#cat_" + c.value))
                            return true;

                        var el = document.createElement('input');
                        el.type = 'hidden';
                        el.name = "cats[" + c.value + "]";
                        el.id = "cat_" + c.value;
                        el.value = c.value;
                        form.appendChild(el);
                        return true;
                        break;

                    case false:

                        element = document.getElementById("cat_" + c.value);
                        element.parentNode.removeChild(element);

                        return true;

                        break;

                    default:
                        return false;
                        break;

                }

            }

        }

        this.changeAttributeSetState = function (id, dom) {

            jQuery.post(
                    ajaxurl + "?action=ajax-call-admin&component=shop&con=attributes&task=changestate", {
                        "id": id

                    },
            function (r) {

                if (r.status) {

                    if (r.error)
                        throw r.errormsg;
                    else {

                        switch (r.row.published) {

                            case 'yes':

                                jQuery("i", dom).removeClass('icon-delete').addClass('icon-checkmark');
                                jQuery(dom).addClass('active');

                                break;

                            case 'no':
                                jQuery("i", dom).removeClass('icon-checkmark').addClass('icon-delete');
                                jQuery(dom).removeClass('active');

                                break;

                            default:

                                throw "unknown publish state";

                                break;
                        }

                    }

                } else {
                    throw " communication error! ";
                }
            });
        }

        this.changeStockRoomState = function (id, dom) {

            jQuery.post(
                    ajaxurl + "?action=ajax-call-admin&component=shop&con=stockroom&task=changestate", {
                        "id": id

                    },
            function (r) {

                if (r.status) {

                    if (r.error)
                        throw r.errormsg;
                    else {

                        switch (r.row.published) {

                            case 'yes':

                                jQuery("i", dom).removeClass('icon-delete').addClass('icon-checkmark');
                                jQuery(dom).addClass('active');

                                break;

                            case 'no':
                                jQuery("i", dom).removeClass('icon-checkmark').addClass('icon-delete');
                                jQuery(dom).removeClass('active');

                                break;

                            default:

                                throw "unknown publish state";

                                break;
                        }

                    }

                } else {
                    throw " communication error! ";
                }
            });
        }

        this.changeProductState = function (id, dom) {

            jQuery.post(
                    ajaxurl + "?action=ajax-call-admin&component=shop&con=products&task=changestate", {
                        "id": id

                    },
            function (r) {

                r.status = parseInt(r.status);

                if (r.status) {

                    jQuery("i", dom).removeClass('icon-delete').addClass('icon-checkmark');
                    jQuery(dom).addClass('active');

                } else {
                    jQuery("i", dom).removeClass('icon-checkmark').addClass('icon-delete');
                    jQuery(dom).removeClass('active');
                }

            });
        }
    }
})(window, jQuery)
