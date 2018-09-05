<?php
$title = get_sub_field('title');
$intro = get_sub_field('intro');
$cta_text = get_sub_field('cta_text');
$cta_link = '';
if ( have_rows('cta_link') ) {
  while ( have_rows('cta_link') ) { the_row();
    $layout = get_row_layout();
    $cta_link .= 'page_picker' === $layout ? get_sub_field('page') : get_sub_field('url');
  }
}
$deep_link = get_sub_field('deep_link');
$anchor = $deep_link ? '<a id="'.$deep_link.'" class="anchor"></a>' : '';
?>

<?= $anchor ?>
<div class="section our-team">
  <div class="container">
    <?php if ( $title || $intro ) { ?>
      <div class="row">
        <div class="col-sm-10 center-align m-b-3">
          <?php if ( $title ) { echo '<h2>' . $title . '</h2>'; } ?>
          <?= $intro ?>
        </div>
      </div>
    <?php } ?>
    <div class="row">
      <?php
      if ( have_rows('team_members') ) {
        while ( have_rows('team_members') ) { the_row();
          $name = get_sub_field('name');
          $title = get_sub_field('title');
          $image = get_sub_field('image')['sizes']['medium'];
          $team_link = '';
          $team_link_target = '';
          if ( have_rows('link') ) {
            while ( have_rows('link') ) { the_row();
              $layout = get_row_layout();
              $team_link .= 'internal_link' === $layout ? get_sub_field('page') : get_sub_field('url');
              $team_link_target = 'external_link' === $layout ? '_blank' : '';
            }
          }
          ?>
          <div class="col-xs-6 col-md-3 team-member">
            <?php
            if ( $team_link ) { ?>
              <a href="<?= $team_link ?>" target="<?= $team_link_target ?>">
            <?php } ?>
            <?php
            if ( $image ) { ?>
            <img src="<?= $image ?>" class="team-member__image img-fluid" alt="<?= $name ?>">
            <?php } ?>
            <h4 class="m-t-1 m-b-0"><?= $name ?></h4>
            <?php if ( $team_link ) { ?>
              </a>
            <?php } ?>
            <?= $title ?>
          </div>
          <?php
        }
      }
      ?>
    </div>
    <?php
    if ( $cta_text && $cta_link ) { ?>
      <div class="row">
        <div class="center-align m-t-3">
          <a href="<?= $cta_link ?>" class="button"><?= $cta_text ?></a>
        </div>
      </div>
    <?php } ?>
  </div>
</div>
