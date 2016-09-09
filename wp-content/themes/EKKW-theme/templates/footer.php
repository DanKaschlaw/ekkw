<footer class="content-info full-width-bcgr">
  <div class="container">
    <div class="container-footer-widgets clearfix">
      <?php dynamic_sidebar('sidebar-footer'); ?>
    </div>
  </div>
  <div class="copyright-bar">
    <div class="container">
      <div class="row">
        <div class="hear-icon"><img src="<?php bloginfo('template_url') ?>/dist/images/footer-heart.gif"></div>
        <div class="col-xs-12 col-sm-6 col-md-9">Copyright <?php echo date('Y')?> Kirchenkreis Melsungen</div>
        <div class="col-xs-12 col-sm-6 col-md-3">
          <?php dynamic_sidebar('copyright-bar'); ?>
        </div>
      </div>
    </div>
  </div>
</footer>
