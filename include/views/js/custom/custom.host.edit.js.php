<script type = "text/javascript" >
    jQuery(document).ready(function () {
        setServerCfg();
        getPartentHosts(jQuery("#groups").val());

        jQuery('#server_type').on('change', function () {
            setServerCfg();
        });

        jQuery('#groups').on('change', function () {
            var groupid = jQuery("#groups").val();
            getPartentHosts(groupid);
        });

        function setServerCfg() {
            var selected = jQuery("#server_type").find("option:selected").val();
            //如果是lbs server，则将app_server class的隐藏；
            if ('0' == selected) {
                jQuery('#label_server_cfg').removeClass('hidden');
                jQuery('.app_server').addClass('hidden');
                jQuery('.lbs_server').removeClass('hidden');
                jQuery("#parentid").prepend("<option value='0'>根节点</option>")
            } else if ('1' == selected) {
                jQuery('#label_server_cfg').removeClass('hidden');
                jQuery('.lbs_server').addClass('hidden');
                jQuery('#app_http_port').attr('style','width: 312px;');
                jQuery('.app_server').removeClass('hidden');
                jQuery("#parentid option[value='0']").remove();
            }
            else {
                jQuery('#label_server_cfg').addClass('hidden');
                jQuery('.app_server').addClass('hidden');
                jQuery('.lbs_server').addClass('hidden');
                jQuery("#parentid option[value='0']").remove();
            }
        }

        function getPartentHosts(groupid)
        {
            var server_type = jQuery('#server_type').val();
            var parentIdHidden = jQuery('#parentIdHidden').val();
            var hostIdHidden = jQuery('#hostIdHidden').val();
            var ajaxUrl = new Curl('hosts_custom.php');
            var ajaxRequest = jQuery.ajax({
                url: ajaxUrl.getUrl(),
                type: 'post',
                data: {groupid:groupid},
                success: function (data) {
                    jQuery('#parentid').empty();
                    var json = eval('(' + data + ')');
                    if(!jQuery.isEmptyObject(json) && data && data!='[]')
                    {
                        if(parentIdHidden==0)
                        {
                            jQuery('#parentid').append('<option selected="selected" value="0">根节点</option>');
                        }

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
                    }
                    else
                    {
                        if(server_type == '0')
                        {
                            jQuery('#parentid').append('<option selected="selected" value="0">根节点</option>');
                        }
                    }
                },
                error: function(xx) {
                    alert('查询父节点失败！');
                }
            });
        }
    });

</script >
