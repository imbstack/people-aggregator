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
require_once 'api/Entity/Entity.php';
require_once 'api/User/UserPopularity.class.php';

class PointsEntity extends Entity {

    public static function search($points_attributes, $count=false, $sort_by=null, $page=1, $show=null, $search_type='LIKE') {
        $entity_params = array(
            'entity_service' => 'internal',
            'entity_type' => 'points'
        );
        $points_ent = array();
        try {
            if($count) {
              $points_ent = parent::search($entity_params, $points_attributes, $search_type);
            } else {
              $points_ent = parent::search($entity_params, $points_attributes, $search_type, $sort_by, $page, $show);
              foreach($points_ent as &$ent) {
                $ent['attributes'] = parent::load_attributes((int)$ent['id']);
              }
            }
        } catch (PAException $e) {
            throw $e;
        }
//echo "PENT<pre>" . print_r($points_ent, 1) . "</pre>";
        return (($count) ? count($points_ent) : $points_ent);
    }

    public static function get_cetegories() {
        $sql =
            "SELECT DISTINCT EAT.attribute_value AS category
             FROM {entities} AS E, {entityattributes} AS EAT
             WHERE E.id = EAT.id
             AND EAT.attribute_name = 'category'";
        $categories = array();
        $r = Dal::query($sql);
        while ($category = Dal::row_assoc($r)) {
            $categories[] = $category['category'];
        }
        return $categories;
     }


    public static function load($entity_id) {
        try {
            return parent::load('internal', 'points', $entity_id);
        } catch (PAException $e) {
            throw $e;
        }
    }

    public static function sync($entity) {
        $_ent = Entity::load($entity->entity_service, $entity->entity_type, $entity->entity_id);
        if(empty($_ent)) {
          self::update_user_points($entity->attributes);
        } else {
          self::update_user_points($entity->attributes, $_ent->attributes);
        }

        parent::sync($entity);
    }

    private static function update_user_points($points_data, $ent_attributes = null) {
      $curr_points = 0;
      $points_obj = UserPopularity::getUserPopularity((int)$points_data['user_id']);
      if(is_object($points_obj)) {
        $curr_points = $points_obj->get_popularity();
      }

      $new_rating = $points_data['rating']['value'];
      if(!empty($ent_attributes)) {
        $old_rating = $ent_attributes['rating']['value'];
        $curr_points = $curr_points - $old_rating;
      }

      $curr_points = $curr_points + $new_rating;
      if($curr_points < 0) $curr_points = 0;

      $upd_points = new UserPopularity();
      $upd_points->populateFromArray(array("user_id" => (int)$points_data['user_id'],
                                           "popularity" => (int)$curr_points,
                                           "time" => time()
                                          )
                                    );
      $upd_points->save_UserPopularity();
    }


    public static function users_points($uid) {
        $user_points = array();
        $r = Dal::query("SELECT *, attribute_value AS user_id
            FROM {entities} AS E
            LEFT OUTER JOIN {entityattributes} AS EA ON E.id = EA.id
            WHERE entity_service='internal' AND entity_type='points'
            AND attribute_name='user_id'
            AND attribute_value=?", array($uid));
        while ($points = Dal::row_assoc($r)) {
            $user_points[] = PointsEntity::load($points['entity_id']);
        }
        return $user_points;
    }

    public static function get_next_id_for_user($uid) {
        $entity_ids = array();
        $r = Dal::query("SELECT entity_id, attribute_value AS user_id
            FROM {entities} AS E
            LEFT OUTER JOIN {entityattributes} AS EA ON E.id = EA.id
            WHERE entity_service='internal' AND entity_type='points'
            AND attribute_name='user_id'
            AND attribute_value=?", array($uid));
        while ($points = Dal::row_assoc($r)) {
            $entity_ids[] = preg_replace("/^(\w+:)/", '', $points['entity_id']);
        }
        sort($entity_ids);
// echo "UID: $uid<pre>" . print_r($entity_ids, 1) . "</pre>";
        $last = array_pop($entity_ids)+1;
        return "$uid:" . (string)$last;
    }

}
?>