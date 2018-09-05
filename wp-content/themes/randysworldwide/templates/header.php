<header class="banner clearfix" role="banner">
  <div class="container">
    <div class="navbar-header">
      <a class="brand" href="<?= esc_url(home_url('/')); ?>"><span class="hidden-xs-up"><?php bloginfo('name'); ?></span></a>
      <div class="navbar-header__mega mega">
        <span class="hidden-sm-down header-meta">1-866-631-0196 <span class="divider">|</span> <a href="/contact-us/#newsletter">Get the Newsletter</a></span>
        <div class="search-form-wrapper hidden-sm-down">
          <?php get_template_part('templates/search-form'); ?>
        </div>
        <div class="account-box">
          <?php
          $contents = 0 < WC()->cart->get_cart_contents_count() ? 'items' : 'empty';
          ?>
          <a href="/my-account" class="hidden-sm-down"> My Account</a> <span class="divider hidden-md-down">|</span> <a href="<?php echo WC()->cart->get_cart_url(); ?>" class="header-cart-button"><span class="hidden-md-down">View Cart </span><i class="fa fa-shopping-cart" aria-hidden="true"></i><div class="cart-count <?= $contents ?>"><?php echo WC()->cart->get_cart_contents_count(); ?></div></a>

        </div>
      </div>
      <button type="button" class="navbar-toggler hidden-md-up pull-xs-right collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-controls="navbar-collapse" aria-expanded="false" aria-label="Toggle navigation"><span class="line"></span><span class="line"></span><span class="line"></span></button>
    </div>

    <nav class="collapse navbar-toggleable-sm" id="navbar-collapse" role="navigation">
      <span class="search-container hidden-md-up">
        <?php get_template_part('templates/search-form'); ?>
      </span>
      <?php
      if (has_nav_menu('primary_navigation')) :
        wp_nav_menu(['theme_location' => 'primary_navigation', 'walker' => new bs4Navwalker(), 'menu_class' => 'nav navbar-nav pull-xs-right']);
      endif;
      ?>
    </nav>
  </div>
</header>

