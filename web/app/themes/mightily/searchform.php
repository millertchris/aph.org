<?php
  $search_term = '';
  if(isset($_GET['fwp_search_term'])) {
    $search_term = $_GET['fwp_search_term'];
  }
?>

<form class="site-search" method="get" role="search" action="<?php echo home_url('/search-results'); ?>">
  <div class="search-close">
    <button class="close" type="button" aria-label="Close modal" name="button"></button>
  </div>
  <div class="input-wrapper text-input-field">
    <label for="search-field" class="screen-reader-text">Start your search.</label>
    <input type="text" class="field search-field" name="fwp_search_term" id="search-field" aria-label="What would you like to search for?">
  </div>
  <input type="submit" class="search-submit" value="<?php echo esc_attr_x('Search', 'submit button') ?>" />
</form>
