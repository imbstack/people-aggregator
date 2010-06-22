<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<div class="blog">
<?php
foreach($polls as $i => $poll) {
    ?>
    <div class="poll">
      <h3>
				<span class="poll_date">
					<?=PA::date($poll->changed, 'long');?>
				</span>:
				<span class="poll_title">
      		<?=$poll->title?>
				</span>
				<span class="poll_votes">(<?=$poll->total_votes?> votes)</span>
      </h3>

      <?php 
      foreach($poll->options as $option) {
        ?>
      	<div class="one_option">
      		<span class="option_text">
      			<?=$option['title'];?>
      		</span>
      		<span class="poll_bar">
      			<img src="<?=PA::$url?>/makebar.php?rating=<?=$option['percent']?>&amp;width=95&amp;height=10" border="0" />
      		</span>
      		<span class="count">
      		(<?=$option['count']?> votes)
      		</span>
      		<span class="percent">
      		<?=$option['percent']?>
      		%</span>
      		<br/>
      </div>
     <?php
    }
    // foreach option?>
    </div>
	<?php
}
// foreach poll?>

</div>
