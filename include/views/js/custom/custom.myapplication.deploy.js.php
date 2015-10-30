<script type = "text/javascript" >

    function undeploy(id)
    {
        var select = '#conditions_'+id;
        jQuery(select).remove();
        select = '#selectedMyApplicationids input[value="'+ id +'"]';
        jQuery(select).remove();
    }

jQuery(document).ready(function () {

    jQuery('#add_application').on('click', function () {
            var applicationItem = '';
            var applicationId ;
            var applicationName ;
            jQuery('#add_applications_  .selected  ul li').each(function()
            {
                applicationId = jQuery(this).attr('data-id');
                applicationName = jQuery(this).children(':first').text();
                applicationItem += '<tr id="conditions_'+ applicationId +'"><td>'+ applicationName +'</td><td><input type="button" value="反部署"  class="input link_menu">&nbsp;</td></tr>';
                jQuery('#selectedMyApplicationids').append('<input type="hidden" name="selectedMyApplicationids[]" value='+applicationId+'>');
            });

            jQuery('#linkedApplicationTable tbody').append(applicationItem);
            jQuery('#add_applications_').html('<input type="text" class="input" value="" style="width: 313px; padding-top: 3px; padding-left: 0px;" placeholder="在此输入搜索"><div class="selected"><ul style="width: 317px; padding-bottom: 0px;" class=""></ul></div><div class="available" style="width: 318px; display: none; top: 20px;"><ul></ul></div>');

        });





    });

</script >
