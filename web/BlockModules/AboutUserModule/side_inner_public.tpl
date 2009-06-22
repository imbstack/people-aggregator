<?php
  // global var $_base_url has been removed - please, use PA::$url static variable

?>
<div class="module_icon_list">
  <ul class="members">
    <li> <?= uihelper_resize_mk_user_img($user->picture, 35, 35, 'alt="'.__("User image.").'"'); ?>
    <span>
      <b><?php $name_string = $user->first_name.' '.$user->last_name;
            $name_string = chop_string($name_string,17);
            echo $name_string;?></b>
      <?php if (!empty($user_data_general['city'])) { echo $user_data_general['city'];}?><br />
      <?php if (!empty($user_data_general['country'])) { echo $user_data_general['country']; }?>
    </span>
    </li>
  </ul>
  </div>
  <div class="module_more_info">
  <ul>
    <li class="color"><a href="#" onclick="javascript:show_hide_network_categories('id_6'); return false;"><?= __("More Detail") ?></a>
    <ul id="id_6" class="display_true">
    <?php if (!empty($user_data_general)) { ?>

      <?php if (!empty($user_data_general['sex'])) { ?>
        <li><b><?= __("Sex") ?></b><br /><?php print $user_data_general['sex']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_general['dob']) && ($user_data_general['dob'] <> '1933-1-1')) { ?>
        <li><b><?= __("Date of birth") ?></b><br /><?php print $user_data_general['dob']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_general['state'])) { ?>
        <li><b><?= __("State") ?></b><br /><?php print $user_data_general['state']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_general['city'])) { ?>
        <li><b><?= __("City") ?></b><br /><?php print $user_data_general['city']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_general['postal_code'])) { ?>
        <li><b><?= __("ZIP code") ?></b><br /><?php print $user_data_general['postal_code']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_general['country'])) { ?>
        <li><b><?= __("Country") ?></b><br /><?php print $user_data_general['country']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_general['blog_url'])) { ?>
        <li><b><a href="<?=$user_data_general['blog_url'];?>" target="_blank"><?= __("Blog") ?></b><br />
        <?php if (!empty($user_data_general['blog_title'])) { ?>
          <?php print $user_data_general['blog_title']; ?>
        <?php } else { ?>
          <?php print $user_data_general['blog_url']; ?>
        <?php } ?></li>
      <? } ?>

      <?php if (!empty($user_data_general['flickr'])) { ?>
        <li><b><?= __("Flickr") ?></b><br /><?php print $user_data_general['flickr']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_general['delicious'])) { ?>
        <li><b><?= __("Delicious") ?></b><br /><?php print $user_data_general['delicious']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_general['user_tags'])) { ?>
        <li><b><?= __("User Tags (Interests)") ?></b><br /><?php print $user_data_general['user_tags']; ?></li>
      <? } ?>
    <? } ?>
    <?if( empty($user_data_general['user_tags']) && empty($user_data_general['delicious']) && empty($user_data_general['flickr']) && empty($user_data_general['blog_url']) && empty($user_data_general['country']) && empty($user_data_general['postal_code']) && empty($user_data_general['city']) && empty($user_data_general['state']) && empty($user_data_general['dob']) && empty($user_data_general['sex'])) { ?>
    <li><?= __("No details entered yet.") ?></li>
    <? } ?>
    </ul></li>
    <li><a href="#" onclick="javascript:show_hide_network_categories('id_3'); return false;"><?= __("Professional") ?></a>
    <ul id="id_3" class="display_false">
      <?php if (!empty($user_data_professional)) { ?>
      <?php if (!empty($user_data_professional['headline'])) { ?>
        <li><b><?= __("Headline") ?></b><br /><?php print $user_data_professional['headline']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_professional['industry'])) { ?>
        <li><b><?= __("Industry") ?></b><br /><?php print $user_data_professional['industry']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_professional['company'])) { ?>
        <li><b><?= __("Company") ?></b><br /><?php print $user_data_professional['company']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_professional['title'])) { ?>
        <li><b><?= __("Title") ?></b><br /><?php print $user_data_professional['title']; ?></li>
      <? } ?>



      <?php if (!empty($user_data_professional['website'])) { ?>
        <li><b><?= __("Website") ?></b><br /><?php print $user_data_professional['website']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_professional['career_skill'])) { ?>
        <li><b><?= __("Career skill") ?></b><br /><?php print $user_data_professional['career_skill']; ?></li>
      <? } ?>


      <?php if (!empty($user_data_professional['prior_company'])) { ?>
        <li><b><?= __("Prior Company") ?></b><br /><?php print $user_data_professional['prior_company'] ;?></li>
      <? } ?>

      <?php if (!empty($user_data_professional['degree'])) { ?>
        <li><b><?= __("Degree") ?></b><br /><?php print $user_data_professional['degree']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_professional['languages'])) { ?>
        <li><b><?= __("Languages") ?></b><br /><?php print $user_data_professional['languages']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_professional['awards'])) { ?>
        <li><b><?= __("Awards") ?></b><br /><?php print $user_data_professional['awards']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_professional['summary'])) { ?>
        <li><b><?= __("Summary") ?></b><br /><?php print $user_data_professional['summary']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_professional['user_cv'])) { ?>
        <li><b><?= __("Download CV") ?></b><br /><a href="<?= htmlspecialchars(Storage::getURL($user_data_professional['user_cv'])) ?>" ><?= __("Download") ?></a></li>
      <? } ?>

    <? } ?>
    <?php if (empty($user_data_professional['headline']) && empty($user_data_professional['industry']) && empty($user_data_professional['company']) && empty($user_data_professional['title']) && empty($user_data_professional['website']) && empty($user_data_professional['career_skill']) && empty($user_data_professional['prior_company']) && empty($user_data_professional['degree']) && empty($user_data_professional['languages']) && empty($user_data_professional['awards'])) { ?>
        <li><?= __("No details entered yet.") ?></li>
    <? } ?>
    </ul></li>
    <li class="color"><a href="#" onclick="javascript:show_hide_network_categories('id_4'); return false;"><?= __("Personal") ?></a>
    <ul id="id_4" class="display_false">
      <?php if (!empty($user_data_personal)) { ?>
      <?php if (!empty($user_data_personal['ethnicity'])) { ?>
        <li><b><?= __("Ethnicity") ?></b><br /><?php print $user_data_personal['ethnicity']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_personal['religion'])) { ?>
        <li><b><?= __("Religion") ?></b><br /><?php print $user_data_personal['religion']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_personal['political_view'])) { ?>
        <li><b><?= __("Political view") ?></b><br /><?php print $user_data_personal['political_view']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_personal['passion'])) { ?>
        <li><b><?= __("Passion") ?></b><br /><?php print $user_data_personal['passion']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_personal['activities'])) { ?>
        <li><b><?= __("Activities") ?></b><br /><?php print $user_data_personal['activities']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_personal['books'])) { ?>
        <li><b><?= __("Books") ?></b><br /><?php print $user_data_personal['books']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_personal['movies'])) { ?>
        <li><b><?= __("Movies") ?></b><br /><?php echo $user_data_personal['movies'];?></li>
      <? } ?>

      <?php if (!empty($user_data_personal['music'])) { ?>
        <li><b><?= __("Music") ?></b><br /><?php print $user_data_personal['music']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_personal['cusines'])) { ?>
        <li><b><?= __("Cuisines") ?></b><br /><?php print $user_data_personal['cusines']; ?></li>
      <? } ?>

      <?php if (!empty($user_data_personal['tv_shows'])) { ?>
        <li><b><?= __("TV shows") ?></b><br /><?php print $user_data_personal['tv_shows']; ?></li>
      <? } ?>

    <? } ?>
    <?php if (empty($user_data_personal['ethnicity']) && empty($user_data_personal['religion']) && empty($user_data_personal['political_view']) && empty($user_data_personal['passion']) && empty($user_data_personal['activities']) && empty($user_data_personal['books']) && empty($user_data_personal['movies']) && empty($user_data_personal['music'])) { ?>
      <li><?= __("No details entered yet.") ?></li>
      <?}?>
    </ul></li>
  </ul>
  </div>