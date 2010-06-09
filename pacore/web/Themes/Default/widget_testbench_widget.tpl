<h1>peopleaggregator: widgetization testbench</h1>

<h2><?= __("Module") ?>: <?= $module_name ?></h2>

<h3><?= __("Request") ?></h3>

<div><code><?= nl2br(htmlspecialchars($post)) ?></code></div>

<h3><?= __("Response") ?></h3>

<div><code><?= nl2br(htmlspecialchars($response_raw)) ?></code></div>

<h3><?= __("Rendered response") ?></h3>

<?
if ($response == NULL) {
  echo "<p>".__("An unknown error occurred - no response available.")."</p>";
} else if (!empty($response->error)) {
  echo "<p>ERROR: ".htmlspecialchars($response->error)."</p>";
} else {
  foreach ($response->modules as $module) {
    ?>
    <h4>Module: <?= htmlspecialchars($module->name) ?></h4>
    <div style="border: solid 1px black;"><?= $module->html ?></div>
    <?
  }
}
?>

<hr>

<p><?= __("Note: do not enable widgetization on public facing webservers as it allows operations to be performed as arbitrary users! Access must be restricted to trusted front-end systems.") ?></p>
