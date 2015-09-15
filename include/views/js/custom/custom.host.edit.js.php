<script type = "text/javascript" >
    jQuery(document).ready(function () {
        setServerCfg();

        jQuery('#serverType').on('change', function () {
            setServerCfg();
        });

        function setServerCfg() {
            var selected = jQuery("#serverType").find("option:selected").val();
            //如果是lbs server，则将app_server class的隐藏；
            if ('0' == selected) {
                jQuery('#label_server_cfg').removeClass('hidden');
                jQuery('.app_server').addClass('hidden');
                jQuery('.lbs_server').removeClass('hidden');
            } else if ('1' == selected) {
                jQuery('#label_server_cfg').removeClass('hidden');
                jQuery('.lbs_server').addClass('hidden');
                jQuery('#app_http_port').attr('style','width: 312px;');
                jQuery('.app_server').removeClass('hidden');
            }
            else {
                jQuery('#label_server_cfg').addClass('hidden');
                jQuery('.app_server').addClass('hidden');
                jQuery('.lbs_server').addClass('hidden');
            }
        }
    });

</script >
