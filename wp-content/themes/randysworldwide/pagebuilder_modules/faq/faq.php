<?php
$count = 1;
?>

<div class="section section--tan faq-section">
  <div class="container">
    <?php if ( have_rows('questions') ) { ?>
      <div id="accordion" role="tablist" aria-multiselectable="true" class="accordion">
        <?php while ( have_rows('questions') ) { the_row();
          $q = get_sub_field('question');
          $a = get_sub_field('answer');
          ?>
          <div class="card">
            <div class="card-header" role="tab" id="heading-<?= $count ?>">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse-<?= $count ?>" aria-expanded="true" aria-controls="collapse-<?= $count ?>" class="collapsed"><?= $q ?></a>
            </div>

            <div id="collapse-<?= $count ?>" class="collapse show" role="tabpanel" aria-labelledby="heading-<?= $count ?>">
              <div class="card-block">
                <?= $a ?>
              </div>
            </div>
          </div>
          <?php $count++;
        } ?>
      </div>
    <?php } ?>
  </div>
</div>
