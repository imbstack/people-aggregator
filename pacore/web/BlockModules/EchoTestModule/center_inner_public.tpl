<p><?= __("This is the echo test module.") ?></p>

<? if (!empty($this->test_blob)) { ?>

<p>Your stored data: <b><?= htmlspecialchars($this->test_blob) ?></b>.</p>

<? } else { ?>

<p>You haven't entered any data here yet.</p>

<? } ?>

<p<?= __(">Update:") ?></p>

<!-- the following form tag will post to the current page when running
through the web interface, or to something defined by $this->mod->post_url
when widgetized. -->
<?= $this->mod->start_form("blob_edit_form", "post") ?>

<!-- the following input tag will be called 'op' when running through
the web interface, or will be prefixed by this->mod->param_prefix when
widgetized. -->
<?= $this->mod->input_tag("hidden", "op", "update_blob") ?>

<?= $this->mod->textarea_tag("blob", $this->test_blob) ?>

<p><?= $this->mod->submit_tag("Save") ?></p>

</form>