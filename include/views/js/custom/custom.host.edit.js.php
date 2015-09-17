<script type = "text/javascript" >
    jQuery(document).ready(function () {
        setServerCfg();
        getPartentHosts();

        jQuery('#server_type').on('change', function () {
            setServerCfg();
        });

        function setServerCfg() {
            var selected = jQuery("#server_type").find("option:selected").val();
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

        function getPartentHosts()
        {
            var parentIdHidden = jQuery('#parentIdHidden').val();
            var hostIdHidden = jQuery('#hostIdHidden').val();
            var ajaxUrl = new Curl('hosts_custom.php');
            var ajaxRequest = jQuery.ajax({
                url: ajaxUrl.getUrl(),
                type: 'post',
                data: {},
                success: function (data) {
                    jQuery('#parentid').empty();
                    if(parentIdHidden==0)
                    {
                        jQuery('#parentid').append('<option selected="selected" value="0">根节点</option>');
                    }

                    var json = eval('(' + data + ')');
                    for (var name in json) {//遍历json对象的每个key/value对,p为key
                        if(hostIdHidden != name)
                        {
                            if(parentIdHidden == name)
                            {
                                jQuery('#parentid').append('<option selected="selected" value="' + name + '">' + json[name] + '</option>');
                            }else
                            {
                                jQuery('#parentid').append('<option value="' + name + '">' + json[name] + '</option>');
                            }
                        }
                    }
                },
                error: function(xx) {
                   // alert('error'+xx);
                }
            });
        }
    });

</script >
