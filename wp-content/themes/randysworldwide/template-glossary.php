<?php
/**
 * Template Name: Glossary Template
 */

use Roots\Sage\Titles;

$page_title = Titles\title();
$image = get_the_post_thumbnail_url( null, 'hero' );
$count = 0;
$alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
$usedLetters = [];
if ( have_rows('letter') ) {
  while ( have_rows('letter') ) { the_row();
    $letter = get_sub_field('letter');
    $usedLetters[$count] = $letter;
    $count++;
  }
}
?>

<?php while (have_posts()) : the_post(); ?>
  <div class="hero" style="background-image: url('<?= $image ?>');">
    <h1> <?= $page_title ?></h1>
  </div>

  <div class="section glossary-section section--tan">
    <div class="container">
      <form>
      <?php
      $i = 0;
      foreach ( $alphabet as $letter ) {
        $active = 0 === $i ? 'active' : '';
        $children = in_array($letter, $usedLetters) ? 'children' : '';
        $row_num = in_array($letter, $usedLetters) ? array_search($letter, $usedLetters) : '';
        echo '<span class="'.$children.' '.$active.'" data-row="'.$row_num.'" data-page-id="'.get_the_ID().'">'.$letter.'</span>';
        $i++;
      } ?>
      <input type="hidden" name="glossary_nonce" id="glossary_nonce" value="<?php echo wp_create_nonce( 'glossary_nonce' ); ?>" />
      </form>
    </div>
  </div>
  <div class="section glossary-content">
    <div class="container">
      <?php
      $rows = get_field('letter');
      $glossary_row = $rows[0]; // get the first row
      $glossary_row_terms = $glossary_row['terms'];

      echo $glossary_row_terms;
      ?>
    </div>
  </div>
<?php endwhile; ?>
