<script type = "text/javascript" >

    function undeploy(id)
    {
        var select = '#conditions_'+id;
        jQuery(select).remove();
        select = '#selectedMyApplicationids input[value="'+ id +'"]';
        jQuery(select).remove();
    }




jQuery(document).ready(function () {
        setServerCfg();
        //getPartentHosts(jQuery("#groups").val());

        jQuery('#server_type').on('change', function () {
            setServerCfg();
        });

        jQuery('#add_application').on('click', function () {
            var applicationItem = '';
            var applicationId ;
            var applicationName ;
            jQuery('#add_applications_  .selected  ul li').each(function()
            {
                applicationId = jQuery(this).attr('data-id');
                applicationName = jQuery(this).children(':first').text();
                applicationItem += '<tr id="conditions_'+ applicationId +'"><td>'+ applicationName +'</td><td><input type="button" value="反部署" onclick="javascript:undeploy('+ applicationId +')" class="input link_menu">&nbsp;</td></tr>';
                jQuery('#selectedMyApplicationids').append('<input type="hidden" name="selectedMyApplicationids[]" value='+applicationId+'>');
            });

            jQuery('#linkedApplicationTable tbody').append(applicationItem);
            jQuery('#add_applications_').html('<input type="text" class="input" value="" style="width: 313px; padding-top: 3px; padding-left: 0px;" placeholder="在此输入搜索"><div class="selected"><ul style="width: 317px; padding-bottom: 0px;" class=""></ul></div><div class="available" style="width: 318px; display: none; top: 20px;"><ul></ul></div>');

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
                jQuery('#tab_myapplicationsTab').addClass('hidden');
                jQuery('#tab_logTab').removeClass('hidden');
                jQuery("#parentid").prepend("<option value='0'>根节点</option>")
                jQuery('#parentid').removeClass('hidden');
                getPartentHosts(jQuery("#groups").val());
            } else if ('1' == selected ) {
                jQuery('#label_server_cfg').removeClass('hidden');
                jQuery('.lbs_server').addClass('hidden');
                jQuery('#app_http_port').attr('style','width: 312px;');
                jQuery('.app_server').removeClass('hidden');
                jQuery('#tab_myapplicationsTab').removeClass('hidden');
                jQuery('#tab_logTab').removeClass('hidden');
                jQuery("#parentid option[value='0']").remove();
                jQuery('#parentid').removeClass('hidden');
                getPartentHosts(jQuery("#groups").val());
            }
            else if ('2' == selected ) {
                jQuery('#label_server_cfg').addClass('hidden');
                jQuery('.app_server').addClass('hidden');
                jQuery('.lbs_server').addClass('hidden');
                jQuery('#tab_myapplicationsTab').addClass('hidden');
                jQuery("#parentid option[value='0']").remove();
                jQuery('#parentid').removeClass('hidden');
                getPartentHosts(jQuery("#groups").val());
            }
            else if ('3' == selected ) {
                jQuery('#label_server_cfg').addClass('hidden');
                jQuery('.app_server').addClass('hidden');
                jQuery('.lbs_server').addClass('hidden');
                jQuery('#tab_myapplicationsTab').addClass('hidden');
                jQuery('#tab_logTab').addClass('hidden');
                jQuery("#parentid option[value='0']").remove();
                jQuery('#parentid').empty();
                jQuery('#parentid').append('<option selected="selected" value="0">None</option>');
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

                    if(server_type == '0')
                    {
                        jQuery('#parentid').append('<option selected="selected" value="0">根节点</option>');
                    }
                    var json = eval('(' + data + ')');
                    if(!jQuery.isEmptyObject(json) && data && data!='[]')
                    {
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

                },
                error: function(xx) {
                    alert('查询父节点失败！');
                }
            });
        }




    });

</script >
