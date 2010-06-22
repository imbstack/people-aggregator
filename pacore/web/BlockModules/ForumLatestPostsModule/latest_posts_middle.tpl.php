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
<?php
?>
<div class="blog">
 <div style="padding:8px">
  <table border='0' style="text-align: left;">
    <thead>
      <tr>
        <th><?=__('Date')?></th>
        <th><?=__('Forum Name')?></th>
        <th><?=__('Thread Title')?></th>
        <th><?=__('User')?></th>
        <th><?=__('Last Post')?></th>
        <th><?=__('Replies')?></th>
      <tr>
    </thead>
    <tbody>
    <?php foreach($threads as $thread) { : $user = $thread->statistics['user']?>
      <tr>
        <td ><?=PA::datetime(strtotime($thread->get_created_at()), 'long', 'short')?></td>
        <td >
          <a href="<?=$forums_url."&forum_id=".$thread->forum->get_id()?>"><?php echo $thread->forum->get_title()?></a>        </td>
        <td >
          <a href="<?=$forums_url."&thread_id=".$thread->get_id()?>"><?php echo $thread->get_title()?></a>
        </td>
        <td >
           <a href="<?=PA::$url.PA_ROUTE_USER_PUBLIC.'/'.$user->login_name?>"><?=$user->login_name?></a>
        </td>
        <td >
          <?php if(!empty($thread->statistics['last_post'])) { : $post = $thread->statistics['last_post'];
    }
}
$post_id = $post->get_id()?>
          <a href="<?=$forums_url."&thread_id=".$post->get_thread_id()."&post_id=$post_id#p_$post_id"?>">
             <?=$post->get_title(24)?>
          </a>
          <?php else { :?>
            <?=__('No posts')?>
          <?php endif;
}?>
        </td>
        <td style="text-align: center;"><?=$thread->statistics['posts']?></td>
      </tr>
    <?php endforeach;?>
    </tbody>
  </table>
 </div>
</div>
