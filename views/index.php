<?php
echo form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=easy_pw_change');

$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
	array('data' => lang('preferences'), 'style' => 'width:50%;'),
	lang('value')
);

foreach ($settings as $key => $val) {
	$this->table->add_row(lang($key, $key), $val);
}
echo $this->table->generate();
$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
	array('data' => lang('user_groups'), 'style' => 'width:50%;'),
	lang('value')
);

foreach ($groups as $val) {
	$this->table->add_row($val[0], $val[1]);
}

echo $this->table->generate();
?>
<p><?php echo form_submit('submit', lang('submit'), 'class="submit"'); ?></p>
<?php $this->table->clear(); ?>
<?= form_close() ?>

<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/easy_pw_change/views/index.php */
