<button id="secret-generator">
  <?php echo __('Generate Secret', null, 'li_os') ?>
</button>

<script type="text/javascript">
  $(document).ready(function(){
    var randomString = function(n)
    {
      var r="";
      while ( n-- )
        r += String.fromCharCode((r=Math.random()*62|0,r+=r>9?(r<36?55:61):48));
      return r;
    }
    
    $('#secret-generator').click(function(){
      $('[name="os_application[secret_new]"]')
        .val(randomString(parseInt(Math.random()*20)+10) );
      return false;
    }).appendTo('.sf_admin_form_field_secret_new .widget');
  });
</script>
