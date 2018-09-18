<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class="box">
	<div class="tbl-ctrls">
		<h1>Channels using the <strong><?php echo $field_label ?></strong> field: </h1>
		<?php
			$this->embed('ee:_shared/table', $table);
		?>
	</div>
</div>
