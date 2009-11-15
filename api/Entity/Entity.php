<?php
require_once 'db/Dal/Dal.php';

// This is the Entity bas class
// all entities should extend this one
// author: Martin Spernau, July 2008, Broadband Mechanics


class Entity extends DbObject {

  public static $columns = array(
  	"id" => "id",
    "entity_service" => "entity_service",
    "entity_type" => "entity_type",
    "entity_id" => "entity_id",
    "entity_name" => "entity_name",
    );

  
  public static function search($entity_params, $entity_attributes=null, $search_type='LIKE', $sort_by=null, $page=1, $show=null) {
		$args= array();
  	if (empty($entity_attributes)) {
			$sql = "SELECT * FROM {entities} AS E";
			if ($sort_by && $sort_by != 'name') {
				// sort by an attribute
				$sql = "SELECT E.*, EAS.attribute_value AS sort_by 
					 FROM {entities} AS E
					 LEFT JOIN {entityattributes} AS EAS ON (EAS.id = E.id AND EAS.attribute_name=?)";
				$args[] = $sort_by;
			}
			$pred = "WHERE";
			foreach ($entity_params as $k=>$v) {
				$sql .= " $pred $k=?";
				$args[] = $v;
				$pred = "AND";
			}
  	} else {
			$sql = "SELECT E.id AS id, 
			E.entity_service AS entity_service, 
			E.entity_type AS entity_type, 
			E.entity_id AS entity_id, 
			E.entity_name AS entity_name, 
			count(E.id) as counts 
				FROM {entities} AS E
				LEFT OUTER JOIN {entityattributes} AS EA ON EA.id = E.id
				WHERE ";
			if ($sort_by && $sort_by != 'name') {
				// sort by an attribute
				$sql = "SELECT EAS.attribute_value AS sort_by, 
				E.id AS id, E.entity_service AS entity_service, 
			E.entity_type AS entity_type, E.entity_id AS entity_id, 
			E.entity_name AS entity_name, 
			count(E.id) as counts 
				FROM {entities} AS E
				LEFT OUTER JOIN {entityattributes} AS EA ON EA.id = E.id
				LEFT JOIN {entityattributes} AS EAS ON (EAS.id = E.id AND EAS.attribute_name=?)
				WHERE ";
				$args[] = $sort_by;
			}
			$ct = 0;
			$pred = "";
			if (!empty($entity_params)) {
				$sql .= " (";
				foreach ($entity_params as $k=>$v) {
					$sql .= " $pred E.$k=?";
					$args[] = $v;
					$pred = "AND";
				}
				$sql .= ") AND";
			}
			$pred = "";
			$sql .= " (";
  		foreach ($entity_attributes as $ak=>$av) {
  			$sql .= " $pred (EA.attribute_name = ? AND EA.attribute_value $search_type ?)\n";
  			if ($search_type == 'LIKE') {
	  			$args[] = $ak; $args[] = "%$av%";
  			} else {
	  			$args[] = $ak; $args[] = $av;
  			}
  			$ct++;
  			$pred = "OR";
  		}
  		$sql .= ") GROUP BY E.id HAVING counts = $ct\n";
  	}
  	
  	// sorting
  	if ($sort_by) {
  		if ($sort_by == 'name') {
  			// sort by entity_name
  			$sql .= " ORDER BY E.entity_name ASC";
  		} else {
  			$sql .= " ORDER BY $sort_by";
  		}
  	}
 // 	echo "SQL = " . $sql . "<br>";
  	// paging
  	if ($show) {
			// add paging
			$start = ($show*$page)-$show;
			$sql .= " LIMIT $start, $show";
// $sql .= " nonsense at the end";
  	}
// debug crash the SQL
//  $sql .= " nonsense at the end";
		$r = Dal::query($sql, $args);
		if (!$r) return NULL;
		$entities = array();
		while ($row = Dal::row_assoc($r)) {
			$entities[] = $row;
		}

if ($show) {
// echo "<pre>".print_r($entities, 1)."</pre>";
}
		return $entities;
  }
  
  // Entity::sync
  // this is the single entry point for creating OR updating Entities
  public static function sync($e) {
    $entity = Entity::load($e->entity_service, $e->entity_type, $e->entity_id);
    if (empty($entity)) {
      // create
      Entity::create($e);
    } else {
      // sync
      $entity->update($e);
    }
  }

  private static function create($e) {
  	$entity_attributes = @$e->attributes;
    $inserts = array();
    $args = array();
    foreach ($e as $k => $v) {
      if ('attributes' == $k) continue;
      $col = @Entity::$columns[$k];
      if (empty($col)) throw new Exception("Unknown entities table column '$k'");

      $inserts[] = "$col=?";
      $args[] = $v;
    }
    // create it
    Dal::query("INSERT INTO {entities} SET ".implode(",", $inserts), $args);
    // get the id
    $id = Dal::query_first("SELECT id FROM {entities} 
    	WHERE ".implode(" AND ", $inserts), $args);

		// deal with attributes
    $entity_attributes['created'] = time();
    $entity_attributes['updated'] = time();
		foreach ($entity_attributes as $ak=>$d) {
			if (!is_array($d)) {
				$ad = array('value'=>$d);
			} else {
				$ad = (array)$d;
			}
			// create it
			Dal::query("INSERT INTO {entityattributes} 
			SET id=?, attribute_name=?, attribute_value=?, attribute_permission=?",
			array($id, $ak, $ad['value'], @$ad['permission']));
		}
  }

  public function update($e) {
  	$entity_attributes = @$e->attributes;
    $updates = array();
    $args = array();
    foreach ($e as $k => $v) {
      if ('attributes' == $k) continue;
      $col = @Entity::$columns[$k];
      if (empty($col)) throw new Exception("Unknown entities table column '$k'");
      // we want to avoid unneccesary updates here
      if ($this->$col != $v) {
      	$this->$col = $v;
      	$updates[] = "$col=?";
      	$args[] = $v;
      }
    }
    if (count($updates)) {
      $args[] = $this->entity_id;
      Dal::query("UPDATE {entities} SET ".implode(",", $updates)." WHERE entity_id=?", $args);
    }
    // now deal with attributes
    $entity_attributes['modified'] = time();
    foreach ($entity_attributes as $ak=>$d) {
    	if (!is_array($d)) {
    		$ad = array('value'=>$d);
    	} else {
    		$ad = (array)$d;
    	}
    	// does this attribute exist yet?
    	if (empty($this->attributes[$ak])) {
    		// create it
    		Dal::query("INSERT INTO {entityattributes} 
    		SET id=?, attribute_name=?, attribute_value=?, attribute_permission=?",
    		array($this->id, $ak, $ad['value'], @$ad['permission']));
    	} else if ($this->attributes[$ak] != $ad) {
	    	// it has changed
    		Dal::query("UPDATE {entityattributes} SET attribute_value=?, attribute_permission=?
    		 WHERE id=? AND attribute_name=?", 
    		 array($ad['value'], 
    		 	@$ad['permission'], 
    		 	$this->id, 
    		 	$ak));
      }
      // update the $this Entity
      $this->attributes[$ak] = $ad;
    }
		// also deal with possible attribute removals here
		foreach ($this->attributes as $ak=>$av) {
			switch ($ak) {
				case 'created':
				case 'updated':
				case 'modified':
					continue; // these are ALWAYS to be retained
				break;
				default:
					if (empty($entity_attributes[$ak])) {
						Dal::query("DELETE FROM {entityattributes} WHERE id=? AND attribute_name=?", array($this->id, $ak));
					}
				break;
			}
		}    
		// update cache
    $cache_key = 
    	"entity:$this->entity_service:$this->entity_type:$this->entity_id";
    Cache::setExtCache(0, $cache_key, $this);
  }

	// load any one Entity idetified by the three params
	// try the cache first, load from SQL query if not cached
  public static function load($entity_service, $entity_type, $entity_id) {
    // cached already?  
    $cache_key = "entity:$entity_service:$entity_type:$entity_id";
    $entity = Cache::getExtCache(0, $cache_key);
    if (!empty($entity)) return $entity;
		
		// load from DB otherwise
    $r = Dal::query_one_assoc("SELECT * FROM {entities}
    	WHERE entity_service=? AND entity_type=? AND entity_id=?", 
    	array($entity_service, $entity_type, $entity_id));
    
    if (!$r) return NULL;
    
    $entity = new Entity();
    $entity->load_from_row($r);
    // get attributes
    $entity->attributes = Entity::load_attributes($entity->id);
    Cache::setExtCache(0, $cache_key, $entity);
    return $entity;
  }
	// load EntityAttributes
	// try the cache first, load from SQL query if not cached
	// NOTE: the $id here is the DB keym not the entity_id
  public static function load_attributes($id) {
    // cached already?  
    $cache_key = "entity_attributes:$id";
    $entity_attributes = Cache::getExtCache(0, $cache_key);
    if (!empty($entity_attributes)) return $entity_attributes;
		
		// load from DB otherwise
    $r = Dal::query("SELECT * FROM {entityattributes}
    	WHERE id=?", array($id));
    
    if (!$r) return NULL;
    // get attributes
    $entity_attributes = array();
    while ($row = Dal::row_assoc($r)) {
    	$entity_attributes[$row['attribute_name']] =
    		array('value'=>$row['attribute_value'],
    			'permission'=>$row['attribute_permission']);
    }
    Cache::setExtCache(0, $cache_key, $entity_attributes);
    return $entity_attributes;
  }
	
	public static function delete($entity_service, $entity_type, $entity_id) {
		// get the DB key for the entity 
    $id = Dal::query_first("SELECT id FROM {entities} 
    	WHERE entity_service=? AND entity_type=? AND entity_id=?", array($entity_service, $entity_type, $entity_id));
		if(empty($id)) return false;
		// delete the entity
		Dal::query("DELETE FROM {entities} 
    	WHERE entity_service=? AND entity_type=? AND entity_id=?", array($entity_service, $entity_type, $entity_id));
		// delete entity attributes
		Dal::query("DELETE FROM {entityattributes} 
    	WHERE id=?", array($id));
		// delete cache for entity AND entity_attribute
		Cache::flushExtCache(0, "entity:$entity_service:$entity_type:$entity_id");
		Cache::flushExtCache(0, "entity_attributes:$id");
		return true;
	}

  public static function delete_match($r) {
    $where = " WHERE 1 ";
    $args = array();
    foreach ($r as $k=>$v) {
    	$where .= " AND $k=?";
    	$args[] = $v;
    }
    $entites = self::load_many_from_query("Entity", "SELECT * FROM {entities} $where", $args);
    foreach ($entites as $entity) {
	    // delete attributes
    	Dal::query("DELETE FROM {entityattributes} WHERE id=?",
    	array($entity->id));
    	// delete the relation
    	Dal::query("DELETE FROM {entities} WHERE id=?",
    	array($entity->id));
    }
    return $entities;
  }
  
	public function get_profile_fields() {
		return array();
	}

}
?>