<header class="banner">
    <nav class="nav-primary navbar navbar-default">
      <div class="container">
        <a class="brand" href="<?= esc_url(home_url('/')); ?>"><b>Kirchenkreis Melsungen</b></a>
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#top-menu-collapse" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <?php
        if (has_nav_menu('primary_navigation')) :
          wp_nav_menu(['theme_location' => 'primary_navigation',
                       'menu_class' => 'nav navbar-nav',
                       'container_class' => 'collapse navbar-collapse pull-right',
                       'container_id' => 'top-menu-collapse'
          ]);
        endif;
        ?>
    </nav>
</header>
