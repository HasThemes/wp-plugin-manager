(function($) {
    "use strict";
    $(document).ready(function() {
        
        /**
         * Tab Navigation
         */
        $( document ).on('click', '.htpm-nav', function(e) {
            e.preventDefault();
            const $this = $( this );
            if(!$this.hasClass('htpm-nav-active')) {
                $this.addClass('htpm-nav-active').siblings().removeClass('htpm-nav-active')
                $($this.attr('href')).addClass('htpm-active').siblings().removeClass('htpm-active');
            }
        });
        
        /**
         * Accordion Activation
         */
        $( "#htpm_accordion" ).accordion({
            active: false ,
            collapsible: true,
            heightStyle: 'content'
        });

        /**
         * Select2 Activation
         */
        $('.htpm_select2_active').select2();

        /**
         * Plugin Disable event handler
         */
        $('.htpm_disable_plugin input[type="checkbox"]').on('click', function() {
            const $this = $(this),
                $fieldGroup = $this.closest('.htpm_field').siblings('.htpm_field_group'),
                $accordionHeader = $this.closest('.htpm_single_accordion').prev('.ui-accordion-header');
            $fieldGroup.stop(true, true).slideToggle(400, function () {
                $(this).css('display', $this.is(':checked') ? 'flex' : 'none');
            });
            $accordionHeader.toggleClass('htpm_is_disabled', $this.is(':checked'));
        });

        /**
         * Show and hide the info box tooltip for custom post type uri.
         * Page Type / URI Type Change Event
         */
        $('.htpm_uri_type select').each(function() {
            if($(this).val() !== 'page_post_cpt') {
                $(this).siblings('.htpm_field_info').hide()
            }
        }).on('change', function() {
            const $this = $(this),
                $value = $this.val(),
                $accordion = $this.closest('.htpm_single_accordion');
            if($value === 'page_post_cpt' || $value === 'custom') {
                proDialogBox();
                $accordion.find('.htpm_select_posts, .htpm_select_pages').addClass('htpm_field_disabled');
            } else {
                $accordion.find('.htpm_select_posts, .htpm_select_pages').removeClass('htpm_field_disabled');
            }
            $accordion.attr('data-htpm_uri_type', $value);
            $accordion.find('[data-uri_type]').each(function() {
                const $field = $(this),
                    $url_types = JSON.parse($field.attr('data-uri_type'));
                $field.addClass('htpm_field_hidden');
                if($value !== 'page_post_cpt' && $url_types.includes($value)) {
                    $field.removeClass('htpm_field_hidden');
                    $this.siblings('.htpm_field_info').hide()
                }
                if($value === 'page_post_cpt' && $url_types.includes($value)) {
                    const $selected_post_types = Array.from($accordion.find('.htpm_select_post_types input:checked')).map(input => input.value),
                        $post_type = $field.attr('data-post_types');
                    $field.removeClass('htpm_field_hidden');
                    if($post_type && !$selected_post_types.includes($post_type)) {
                        $field.addClass('htpm_field_hidden');
                    }
                    $this.siblings('.htpm_field_info').show(0, function() {
                        $(this).css('display', 'inline-block');
                    });
                }
            });
        });

        /**
         * Select Post Types Change Event
         */
        $('.htpm_select_post_types input').on('click', function () {
            const $this = $(this),
                $value = $this.val(),
                $accordion = $this.closest('.htpm_single_accordion');
            $accordion.find('[data-uri_type]').each(function() {
                const $field = $(this),
                    $post_type = $field.attr('data-post_types');
                if ($value === $post_type) {
                    if ($this.is(':checked')) {
                        $field.removeClass('htpm_field_hidden');
                    } else {
                        $field.addClass('htpm_field_hidden');
                    }
                }
            });
        });

        /**
         * ADD: Repeater field event handler
         */
        $('.htpm_field_repeater_add').on('click', function () {
            const $newRow = $(this).closest('tr').clone(true);
            $(this).closest('tr').after($newRow);
        });

        /**
         * REMOVE: Repeater field event handler
         */
        $('.htpm_field_repeater_remove').on('click', function () {
            const $tbody = $(this).closest('tbody');
            if ($tbody.children('tr').length > 1) {
                $(this).closest('tr').remove();
            }
        });

        $(document).on('click', '.htpm_field_disabled input[type="checkbox"], .htpm_field_repeater_disabled input[type="checkbox"]', function(e){
            e.preventDefault();
            proDialogBox();
        });
        $(document).on('mousedown', '.htpm_field_disabled select, .htpm_field_disabled .select2-container', function(e){
            e.preventDefault();
            proDialogBox();
        });
        function proDialogBox () {
            $( "#htpm_pro_notice" ).dialog({
                dialogClass: 'wp-dialog htpm_pro_notice',
                title: 'Buy Pro',
                modal: true,
                draggable: false,
                width: 450,
            });
        }
        $('.ui-widget-overlay').on('click', function() {
            $( "#htpm_pro_notice" ).dialog('close');
        });

    });
})(jQuery);