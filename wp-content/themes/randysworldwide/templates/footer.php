<footer class="content-info">
  <div class="container">
    <div class="row">
      <div class="col-lg-3 flex-desktop-one-mobile-four">
        <div class="content-info__col-wrapper content-info__col-wrapper--last-item-desktop">
          <div class="content-info__wrapper">
            <div class="content-info__section-header">Connect with us</div>
            <ul class="content-info__social list-unstyled">
              <?php if( get_field('youtube', 'option') ): ?>
                <li><a href="<?php the_field('youtube', 'option'); ?>" target="_blank" class="content-info__sub-link content-info__sub-link--has-icon"><i class="content-info__icon fa fa-youtube" aria-hidden="true"></i>Youtube</a></li>
              <?php endif; ?>
              <?php if( get_field('facebook', 'option') ): ?>
                <li><a href="<?php the_field('facebook', 'option'); ?>" target="_blank" class="content-info__sub-link content-info__sub-link--has-icon"><i class="content-info__icon fa fa-facebook" aria-hidden="true"></i>Facebook</a></li>
              <?php endif; ?>
              <?php if( get_field('twitter', 'option') ): ?>
                <li><a href="<?php the_field('twitter', 'option'); ?>" target="_blank" class="content-info__sub-link content-info__sub-link--has-icon"><i class="content-info__icon fa fa-twitter" aria-hidden="true"></i>Twitter</a></li>
              <?php endif; ?>
              <?php if( get_field('instagram', 'option') ): ?>
                <li><a href="<?php the_field('instagram', 'option'); ?>" target="_blank" class="content-info__sub-link content-info__sub-link--has-icon"><i class="content-info__icon fa fa-instagram" aria-hidden="true"></i>Instagram</a></li>
              <?php endif; ?>
            </ul>
          </div>
          <div class="content-info__mobile-divider"></div>
          <div class="content-info__wrapper">
            <a id="newsletter" class="anchor"></a>
            <div class="content-info__section-header">Subscribe to our Newsletter</div>
          </div>
          <div class="content-info__wrapper" id="gf_1">
            <?php gravity_form(1, false, false, false, false, true, 12); ?>
          </div>
          <div class="content-info__mobile-divider"></div>
        </div>
      </div>
      <div class="col-xs-6 col-lg-3 flex-all-two">
        <div class="content-info__col-wrapper">
          <?php wp_nav_menu(array('menu' => 'Footer Nav-list One', 'menu_class' => 'content-info__nav-list list-unstyled')); ?>
        </div>
      </div>
      <div class="col-xs-6 col-lg-3 flex-all-three">
        <div class="content-info__col-wrapper">
          <?php wp_nav_menu(array('menu' => 'Footer Nav-list Two', 'menu_class' => 'content-info__nav-list list-unstyled')); ?>
        </div>
      </div>
      <div class="col-lg-3 flex-desktop-four-mobile-one">
        <div class="content-info__mobile-divider hidden-lg-up"></div>
        <div class="content-info__col-wrapper">
          <div class="content-info__wrapper">
            <a class="content-info__brand" href="<?php echo get_site_url(); ?>"><span class="hidden-xs-up">Randys Worldwide</span></a>
            <div class="content-info__copyright hidden-lg-up">Copyright &copy; <?php echo date('Y'); ?> RANDYS Worldwide</div>
          </div>
          <div class="content-info__wrapper hidden-md-down">
            <div class="content-info__copyright">Copyright &copy; <?php echo date('Y'); ?> RANDYS Worldwide</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>
