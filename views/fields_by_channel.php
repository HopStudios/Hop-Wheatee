<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class="box">
	<div class="tbl-ctrls">
		<h1>Fields assigned to the <strong><?php echo $channel_title ?></strong> Channel: </h1>

		<?php if (!empty($ungroupedTable['data'])) {
			echo "<h2>Ungrouped Fields</h2></br>";
			$this->embed('ee:_shared/table', $ungroupedTable); 
		}
		
		if (!empty($groupedTable['data'])) {
			echo "<h2>Grouped Fields</h2></br>";
			$this->embed('ee:_shared/table', $groupedTable); 
		}
		?>

	</div>
</div>
