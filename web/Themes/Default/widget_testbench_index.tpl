<h1><?= __("peopleaggregator: widgetization testbench") ?></h1>

<h2><?= __("Select a module") ?>:</h2>
<ul>
<?
foreach ($modules as $module => $notes) {
  echo '<li><a href="widget_testbench.php?module='.$module.'">'.$module.'</a>';
  if (!empty($notes)) echo ' ('.$notes.')';
  echo '</li>';
}
?>
</ul>
