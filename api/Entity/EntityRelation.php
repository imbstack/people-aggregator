<?php

// EntityRelation base class
// Martin Spernau
// Copyright (C) 2008 Broadband Mechanics, Inc


class EntityRelation extends DbObject {

  public static $columns = array("subject_service", "subject_type", "subject_id", "object_service", "object_type", "object_id", "relation_type", "start_time", "end_time");

  public static function sync($r) {
		$match = array(
			'subject_service' => $r['subject_service'],
			'subject_type' => $r['subject_type'],
			'subject_id' => $r['subject_id'],
			'relation_type' => $r['relation_type'],
			'object_service' => $r['object_service'],
			'object_type' => $r['object_type'],
			'object_id' => $r['object_id'],
		);
    $relations = EntityRelation::load_match($match);
    if (empty($relations[0])) {
      // create
      $id = EntityRelation::create($r);
    } else {
      // sync
      $relations[0]->update($r);
      $id = $relations[0]->id;
    }
    return $id;
  }

  private static function create($r) {
    $relation_attributes = $r['attributes'];
    $inserts = array();
    $args = array();
    foreach (EntityRelation::$columns as $i => $k) {
      if (empty($r[$k])) {
      	if ($k == 'end_time') { // end_time may be null
      		continue;
      	} else {
      		throw new Exception("Missing entityrelations table column '$k'");
      	}
      }
      $inserts[] = "$k=?";
      $args[] = $r[$k];
    }
    // create it
    Dal::query("INSERT INTO {entityrelations} SET ".implode(",", $inserts), $args);

    // get the id
    $id = Dal::query_first("SELECT id FROM {entityrelations} 
    	WHERE ".implode(" AND ", $inserts), $args);

		// deal with attributes
    $relation_attributes['created'] = time();
    $relation_attributes['updated'] = time();
		foreach ($relation_attributes as $ak=>$d) {
			if (!is_array($d)) {
				$ad = array('value'=>$d);
			} else {
				$ad = (array)$d;
			}
			// create it
			Dal::query("INSERT INTO {entityrelationattributes} 
			SET id=?, attribute_name=?, attribute_value=?, attribute_permission=?",
			array($id, $ak, $ad['value'], @$ad['permission']));
		}
		return $id;
  }

  public function update($r) {
    $relation_attributes = $r['attributes'];
    $updates = array();
    $args = array();
    $where = " WHERE 1 ";
    foreach (EntityRelation::$columns as $i => $k) {
      if (!empty($r[$k]) && ($this->{$k} != $r[$k])) {
      	$this->{$k} = $r[$k];
      	$updates[] = " $k=? ";
      	$args[] = $r[$k];
      }
      if (!in_array($k, array('start_time', 'end_time', 'attributes'))) {
	    	$where .= " AND $k='".$this->{$k}."'";
      }
    }
    if (count($updates)) {
      Dal::query("UPDATE {entityrelations} SET ".implode(",", $updates)." $where ", $args);
    }
    
    // now deal with attributes
    $relation_attributes['modified'] = time();
    foreach ($relation_attributes as $ak=>$d) {
    	if (!is_array($d)) {
    		$ad = array('value'=>$d);
    	} else {
    		$ad = (array)$d;
    	}
    	// does this attribute exist yet?
    	if (empty($this->attributes[$ak])) {
    		// create it
    		Dal::query("INSERT INTO {entityrelationattributes} 
    		SET id=?, attribute_name=?, attribute_value=?, attribute_permission=?",
    		array($this->id, $ak, $ad['value'], @$ad['permission']));
    	} else if ($this->attributes[$ak] != $ad) {
    	// it has changed
    		Dal::query("UPDATE {entityrelationattributes} SET attribute_value=?, attribute_permission=?
    		 WHERE id=? AND attribute_name=?", 
    		 array($ad['value'], 
    		 	@$ad['permission'], 
    		 	$this->id, 
    		 	$ak));
      }
      
      // also deal with possible attribute removals here
      foreach ($this->attributes as $ak=>$av) {
      	if (empty($relation_attributes[$ak])) {
      		Dal::query("DELETE FROM {entityrelationattributes} WHERE id=? AND attribute_name=?", array($this->id, $ak));
      	}
      }
      // update the $this EntityRelation
      $this->attributes[$ak] = $ad;
    }
    
    return $this->id;
  }
	
	// load relations that match the passed params
	// this can be zero, one (exact match)
	// or many (all of one type etc)
  public static function load_match($r, $load_attrs=true) {
    $where = " WHERE 1 ";
    $args = array();
    foreach ($r as $k=>$v) {
    	$where .= " AND $k=?";
    	$args[] = $v;
    }
    $relations = self::load_many_from_query("EntityRelation", "SELECT * FROM {entityrelations} $where", $args);
    if ($load_attrs) {
			// get attributes
			foreach ($relations as $relation) {
				$relation->attributes = EntityRelation::load_attributes($relation->id);
			}
    }
    return $relations;
  }

  public static function delete_match($r) {
    $where = " WHERE 1 ";
    $args = array();
    foreach ($r as $k=>$v) {
    	$where .= " AND $k=?";
    	$args[] = $v;
    }
    $relations = self::load_many_from_query("EntityRelation", "SELECT * FROM {entityrelations} $where", $args);
    foreach ($relations as $relation) {
	    // delete attributes
    	Dal::query("DELETE FROM {entityrelationattributes} WHERE id=?",
    	array($relation->id));
    	// delete the relation
    	Dal::query("DELETE FROM {entityrelations} WHERE id=?",
    	array($relation->id));
    }
    return $relations;
  }

  public static function load_attributes($id) {
    $r = Dal::query("SELECT * FROM {entityrelationattributes}
    	WHERE id=?", array($id));
    
    if (!$r) return NULL;
    // get attributes
    $relation_attributes = array();
    while ($row = Dal::row_assoc($r)) {
    	$relation_attributes[$row['attribute_name']] =
    		array('value'=>$row['attribute_value'],
    			'permission'=>$row['attribute_permission']);
    }
    return $relation_attributes;
  }  
  
  

}
?>