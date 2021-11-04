<?php

require_once 'DBManager.lib.php5';

/**
 * Extended DBEntry represents a many to many relation table.
 * A manager class can use concrete implementations of such entities to simplify fetching involved DBEntry entities.
 *
 * @access   	public
 * @author   	Marco Pfeifer <m.pfeifer@stollvongati.com>
 *
 * @since    	PHP 5.3
 * @version		1.0
 * @copyright 	Copyright (c) 2011 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class DBRelationEntry extends DBEntry
{

	/**
	 * The name of the database table.
	 * @var string
	 */
	protected $dbTableName = null;

	/**
	 * The column name for relations to entityA.
	 * @var string
	 */
	protected $columnNameEntityA = null;

	/**
	 * The column name for relations to entityB.
	 * @var string
	 */
	protected $columnNameEntityB = null;

	/**
	 * The current EntityA instance.
	 *
	 * @var DBEntry
	 */
	protected $entityA = null;

	/**
	 * The current EntityB instance.
	 *
	 * @var DBEntry
	 */
	protected $entityB = null;

	/**
	 * Clone this entity.
	 *
	 * @see DBManager-2.0/DBEntry::__clone()
	 */
	public function __clone()
	{
		// reset pkey only
		parent::__clone();
	}

	/**
	 * Create an instance of entity A, which is the 1st entity to be set in relation with 2nd entity B.
	 * @param DBManager $db 	the DBManager to be used
	 * @return DBEntry a new instance representing EntityA
	 */
	public abstract function CreateEntityA(DBManager &$db);

	/**
	 * Create an instance of entity B, which is the 2nd entity to be set in relation with 1st entity A.
	 * @param DBManager $db 	the DBManager to be used
	 * @return DBEntry a new instance representing EntityB
	 */
	public abstract function CreateEntityB(DBManager &$db);

	/**
	 * Get the attribute(s) of this relation as associative array (array ('attribute' => value)).
	 *
	 * @return array  	the attributes array
	 */
	public function GetRelationAttributes()
	{
		return array();
	}

	/**
	 * Set the attributes of this relation as associative array (array ('attribute' => value)).
	 *
	 * @param array  	the attributes array
	 */
	public function SetRelationAttributes(array $attributes)
	{
		// do nothing!
	}

	/**
	 * Initialize table configuration.
	 * A child class can define $dbTableName and both relation column names ($columnNameEntityA, $columnNameEntityB) by setting the according properties.
	 * If column names or the table name are not set, they are derived from entity A and entity B as described below (@see CreateEntity methods).
	 * IMPORTANT: The relation columns are always added to the DBConfig row-arrays. So never add the relation columns to the DBConfig to avoid duplication
	 * - just set the column names if defaults are not sufficient. The table name stored at $dbConfig is also always overwritten with the current $dbTableName value to be consistent!
	 *
	 * The names of the relation columns and the table name are retrieved from entity A and entity B as follows (if not already set (!= null)):
	 * Assumptions:
	 * Table name of entity A is: "Activity"
	 * Table name of entity B is: "Hazard"
	 * $dbTableName = Table name of entity A + '_' + Table name of entity B, e.g.: "Activity_Hazard"
	 * $columnNameEntityA = strtolower(Table name of entity A) + "_rel", e.g.: "activity_rel"
	 * $columnNameEntityB = strtolower(Table name of entity B) + "_rel", e.g.: "hazard_rel"
	 *
	 * @param DBManager $db			the Database Manager to use.
	 * @param DBConfig $dbConfig 	the Database configuration.
	 * @param bool $isUnique 		relation columns are unique (compound key) - default is <code>true</code>.
	 * @access public
	 */
	public function DBRelationEntry(DBManager &$db, DBConfig &$dbConfig, $isUnique=true)
	{
		// set up the entities so these database tables are present and we are able to determine table names.
		$this->entityA = $this->CreateEntityA($db);
		$this->entityB = $this->CreateEntityB($db);

		// determine table name if not already set.
		if ($this->dbTableName == null)
		{
			$this->dbTableName = $this->entityA->GetTableName().'_'.$this->entityB->GetTableName();
		}
		// determine column names if not already set
		if ($this->columnNameEntityA == null && $this->columnNameEntityB == null)
		{
			$this->columnNameEntityA = strtolower($this->entityA->GetTableName()).'_rel';
			$this->columnNameEntityB = strtolower($this->entityB->GetTableName()).'_rel';
		}

		// update database config
		$dbConfig->tableName = $this->dbTableName;
		$rowNamesToInsert = array($this->columnNameEntityA, $this->columnNameEntityB);
		$rowParamsToInsert = array("BIGINT", "BIGINT");
		$rowIndexToInsert = array($this->columnNameEntityA, $this->columnNameEntityB);
		if ($isUnique)
		{
			$rowIndexToInsert[] = array('columns' => array($this->columnNameEntityA, $this->columnNameEntityB), 'unique' => true);
		}
		$dbConfig->InsertRowsAt($rowNamesToInsert, $rowParamsToInsert, $rowIndexToInsert);

		parent::__construct($db, $dbConfig);
	}

	/**
	 * @see DBManager-1.0/DBEntry::BuildDBArray()
	 * Errorcodes:
	 * 		-91 		EntityA is not a stored entity
	 * 		-92 		EntityB is not a stored entity
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if ($this->entityA->GetPKey() > 0)
		{
			$rowName[] = $this->columnNameEntityA;
			$rowData[] = $this->entityA->GetPKey();
		}
		else
		{
			return -91;
		}

		if ($this->entityB->GetPKey() > 0)
		{
			$rowName[] = $this->columnNameEntityB;
			$rowData[] = $this->entityB->GetPKey();
		}
		else
		{
			return -92;
		}

		return true;
	}

	/**
	 * @see DBManager-1.0/DBEntry::BuildFromDBArray()
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Load both referenced entities if required.
		if ($data[$this->columnNameEntityA] > 0 && ($this->entityA == null || $this->entityA->GetPKey() != $data[$this->columnNameEntityA]))
		{
			$this->entityA = $this->CreateEntityA($db);
			$this->entityA->Load($data[$this->columnNameEntityA], $db);
		}

		if ($data[$this->columnNameEntityB] > 0 && ($this->entityB == null || $this->entityB->GetPKey() != $data[$this->columnNameEntityB]))
		{
			$this->entityB = $this->CreateEntityB($db);
			$this->entityB->Load($data[$this->columnNameEntityB], $db);
		}

		return true;
	}

	/**
	 * Get the a entity A instance - this may be a empty prototype instance of the entity or a persistent entity if the relation was fetched from DB.
	 *
	 * @return DBEntry  a instance of entity A.
	 */
	public function GetEntityA()
	{
		return $this->entityA;
	}

	/**
	 * Set the entity A relation (by reference).
	 * ATTENTION: A saved DBEntry (pkey > 0) is required to store this relation.
	 *
	 * @param DBEntry $value
	 */
	protected function SetEntityA(DBEntry &$value)
	{
		$this->entityA = $value;
	}

	/**
	 * Get the a entity B instance - this may be a empty prototype instance of the entity or a persistent entity if the relation was fetched from DB.
	 *
	 * @return DBEntry  a instance of entity B.
	 */
	public function GetEntityB()
	{
		return $this->entityB;
	}

	/**
	 * Set the entity B relation (by reference).
	 * ATTENTION: A saved DBEntry (pkey > 0) is required to store this relation.
	 *
	 * @param DBEntry $value
	 */
	protected function SetEntityB(DBEntry &$value)
	{
		$this->entityB = $value;
	}

	/**
	 * Get the name of the table column for relations to entity A.
	 *
	 * @return String  column name for relations to entity A.
	 */
	public function GetColumnNameEntityA()
	{
		return $this->columnNameEntityA;
	}

	/**
	 * Get the name of the table column for relations to entity B.
	 *
	 * @return String  column name for relations to entity B.
	 */
	public function GetColumnNameEntityB()
	{
		return $this->columnNameEntityB;
	}

	/**
	 * Get the name of the relation table.
	 *
	 * @return String  - the table name.
	 */
	public function GetRelationTableName()
	{
		return $this->dbTableName;
	}

}
//DBRelationEntry

/**
 * The base class of DBRelationHandler and EntityDBRealtionHandler - don't use this class until you know what you are doing!.
 *
 * @access   	public
 * @author   	Marco Pfeifer <m.pfeifer@stollvongati.com>
 *
 * @since    	PHP 5.3
 * @version		1.0
 * @copyright 	Copyright (c) 2011 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class BaseDBRelationHandler
{

	/**
	 * A prototype instance of the relation to manage.
	 *
	 * @var DBRelationEntry
	 */
	private $relationPrototype = null;

	/**
	 * The current EntityA instance - used to find all other EntityB instances.
	 *
	 * @var DBEntry
	 */
	private $entityA = null;

	/**
	 * The current EntityB instance - used to find all other EntityA instances.
	 *
	 * @var DBEntry
	 */
	private $entityB = null;

	/**
	 * Constructor.
	 *
	 * @param DBRelationEntry $relationPrototype  	A prototype instnce of the relation to manage.
	 */
	public function BaseDBRelationHandler(DBRelationEntry $relationPrototype)
	{
		$this->relationPrototype = $relationPrototype;
	}

	/**
	 * Create a new relation instance ready to be stored.
	 * ATTENTION: Due to the fact we can create relations in both directions the implementation of this method
	 * have to check the type of $fromEntity (or $toEntity) since it could be both types (type of EntityA or EntityB).
	 *
	 * @param DBManager $db
	 * @param DBEntry $fromEntity  	the 1st entity of the relation (source entity)
	 * @param DBEntry $toEntity  	the 2nd entity of the relation (target entity)
	 * @param array  $attributes 	the attributes for the new relation (optional, default is <code>null</code>)
	 * @return DBRelationEntry a new fully initialized relation instance ready to be stored.
	 */
	protected abstract function CreateRelation(DBManager &$db, DBEntry &$fromEntity, DBEntry &$toEntity, &$attributes=null);

	/**
	 * Delete all relation entities to current EntityA (without deleting EntityA or EntityB).
	 *
	 * @param DBManager $db
	 */
	public function DeleteAllRelationsWithEntityA(DBManager &$db)
	{
		if ($this->GetEntityA()->GetPKey() > -1)
		{
			$db->Delete($this->relationPrototype->GetRelationTableName(), $this->relationPrototype->GetColumnNameEntityA().' = '.$this->GetEntityA()->GetPKey());
		}
	}

	/**
	 * Delete all relation entities to current EntityB (without deleting EntityA or EntityB).
	 *
	 * @param DBManager $db
	 */
	public function DeleteAllRelationsWithEntityB(DBManager &$db)
	{
		if ($this->GetEntityB()->GetPKey() > -1)
		{
			$db->Delete($this->relationPrototype->GetRelationTableName(), $this->relationPrototype->GetColumnNameEntityB().' = '.$this->GetEntityB()->GetPKey());
		}
	}

	/**
	 * Get property relationPrototype.
	 *
	 * @return DBRelationEntry  	the prototype instance for the relations managed by this handler.
	 */
	protected function GetRelationPrototype()
	{
		return $this->relationPrototype;
	}

	/**
	 * Get the current entity A instance - used to find all other entity B instances.
	 *
	 * @return DBEntry  a instance of entity A.
	 */
	protected function GetEntityA()
	{
		return $this->entityA;
	}

	/**
	 * Set the current entity A instance - used to find all other entity B instances.
	 *
	 * @param DBEntry $entity 	the entity to set
	 */
	protected function SetEntityA(DBEntry &$entity)
	{
		$this->entityA = $entity;

		// set other side with prototype instance to be sure it is set
		if ($this->entityB == null)
		{
			$this->entityB = $this->relationPrototype->GetEntityB();
		}
	}

	/**
	 * Get the current entity B instance - used to find all other entity A instances.
	 *
	 * @return DBEntry  a instance of entity B.
	 */
	protected function GetEntityB()
	{
		return $this->entityB;
	}

	/**
	 * Set the current entity B instance - used to find all other entity A instances.
	 *
	 * @param DBEntry $entity 	the entity to set
	 */
	protected function SetEntityB(DBEntry &$entity)
	{
		$this->entityB = $entity;

		// set other side with prototype instance to be sure it is set
		if ($this->entityA == null)
		{
			$this->entityA = $this->relationPrototype->GetEntityA();
		}
	}

	/**
	 * Add $entity and $attributes to array $entityList - the $entity is stored if not already done and DBManager is not <code>null</code>.
	 *
	 * @param array $entityList 		the array to add the entity
	 * @param DBEntry $entity				the entity to add
	 * @param array $attributes				the attributes (optional)
	 * @param DBManager $db 				the DBManager to store unsaved entities (default is <code>null</code> - do not store entities)
	 * @param bool $ignoreMultipleEntities 		when <code>true</code> entities with same pkeys are not added more than once.
	 */
	protected function AddToEntityList(array &$entityList, DBEntry &$entity, $attributes=null, DBManager &$db=null, $ignoreMultipleEntities=false)
	{
		$key = $entity->GetPKey();
		if ($db != null && $key == -1)
		{
			$entity->Store($db);
			$key = $entity->GetPKey();
		}

		if (isset($entityList[$key]) === false)
		{
			$entityList[$key] = array('entity' => $entity, 'count' => 1, 'attributes' => array($attributes));
		}
		else if ($ignoreMultipleEntities === false)
		{
			// update relation counter and attributes
			$entityList[$key]['count'] += 1;
			// add attributes
			$entityList[$key]['attributes'][] = $attributes;
		}
	}

	/**
	 * Synchronize the references between given Entity[] (as $entityList) with OtherEntity $otherEntity.
	 * This will remove all relations to any Entity not in $entityList (including referenced Entity if $removeEntities is <code>true</code>).
	 * The respective "FindAllEntitiesLinkedWith..."-method will return the same Entity[] as provided as $entityList.
	 *
	 * @param DBManager $db
	 * @param array $entityList 			Array with all DBEntry instances and attributes to be linked with $otherEntity (@see AddToEntityList()) .
	 * @param String $entityColumnName 		The name of the column referencing the Entities at the relation table.
	 * @param DBEntry $otherEntity 			The OtherEntity
	 * @param String $otherEntityColumnName The name of the column referencing the OtherEntities at the relation table.
	 * @param boolean $removeEntities 		If <code>true</code> any referenced Entity not in $entity_array will be deleted with the relation. Default is <code>false</code>.
	 * @param string $entityTableName 		The table name to delete entities from if $removeEntities is <code>true</code>.
	 */
	protected function SynchronizeRelations(DBManager &$db, &$entityList, $entityColumnName, &$otherEntity, $otherEntityColumnName, $removeEntities=false, $entityTableName=null)
	{
		// fetch exisiting relation data to $otherEntity.
		$relationRawData = $this->FetchAllRelationsByEntityPkey($db, $otherEntityColumnName, $otherEntity->GetPkey());

		/*
		 * Update all existing relations with actual relation data as provided as $entityList.
		 * Array $entityList contains all new relations when for loop is finished.
		 */
		for ($i = 0; $i < count($relationRawData); $i++)
		{
			$entityPkey = $relationRawData[$i][$entityColumnName];
			if (isset($entityList[$entityPkey]))
			{
				// relation already exist
				// get the first attributes set
				$newAttributes = array_shift($entityList[$entityPkey]['attributes']);
				$entityList[$entityPkey]['count'] -= 1;
				//now update the attributes
				if ($newAttributes != null && count($newAttributes) > 0)
				{
					// the relation has attributes
					// restore the DBRelationEntry to be able to update the attributes!
					$rel = $this->CreateRelation($db, $entityList[$entityPkey]['entity'], $otherEntity);
					$state = $rel->LoadFromArray($relationRawData[$i], $db);
					if ($state === true)
					{
						$existingAttributes = $rel->GetRelationAttributes();

						// check attributes
						$attributesChanged = false;
						if (count($existingAttributes) == count($newAttributes))
						{
							// check all values
							foreach ($existingAttributes as $key => $value)
							{
								if ($newAttributes[$key] != $value)
								{
									$attributesChanged = true;
									break;
								}
							}
						}
						else
						{
							// attribute count different
							$attributesChanged = true;
						}

						if ($attributesChanged === true)
						{
							/* update attributes, they have changed - this relation may be the wrong one,
							 * so in worst case all attributes have changed!
							 */
							$rel->SetRelationAttributes($newAttributes);
							$rel->Store($db);
						}
					}
				}

				if ($entityList[$entityPkey]['count'] == 0)
				{
					// now remove the pkey from $entityList - all relations regarding current $entityPkey are up to date.
					unset($entityList[$entityPkey]);
				}
			}
			else
			{
				// relation does not exist any more - remove it
				$relationTable = $this->GetRelationPrototype()->GetRelationTableName();
				$db->DeleteByPkey($relationTable, $relationRawData[$i]['pkey']);
				if ($removeEntities === true && $entityTableName !== null)
				{
					// delete linked entity if no other relation exist.
					$checkResult = $db->Select('SELECT count(pkey) FROM '.$relationTable.' WHERE '.$entityColumnName.' = '.$entityPkey);
					if ($checkResult[0][0] == 0)
					{
						$db->DeleteByPkey($entityTableName, $entityPkey);
					}
				}
			}
		}
		// check new relations
		if (count($entityList) > 0)
		{
			// create the new relations
			foreach ($entityList as $element)
			{
				$currentEntity = $element['entity'];
				if ($currentEntity != null && $currentEntity instanceof DBEntry)
				{
					if ($currentEntity->GetPkey() === -1)
					{
						// DBEntry is not saved yet, so store it!
						$currentEntity->Store($db);
					}

					$relationCount = $element['count'];
					for ($j = 0; $j < $relationCount; $j++)
					{

						// create the new relation with attributes
						$newRelation = $this->CreateRelation($db, $currentEntity, $otherEntity, array_shift($element['attributes']));

						$result = $newRelation->Store($db);
						if ($result !== true)
						{
							// error occured during store so abort sync and return the error code.
							return $result;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Reorganize the DBEntry[] $entities so that the keys represent the pkey of each DBEntry.
	 * This function is used to synchronize relations, @see SynchronizeRelationsToEntityA(), SynchronizeRelationsToEntityB()
	 *
	 * @param DBEntry[] $entities
	 * @param array $attributes 			The attributes to be stored with the relations (default is <code>null</code> - not set).
	 * @return array  - The reorganized array with pkey of the $entities (DBEntry->GetPkey()) as keys: array (pkey => array ('entity' => $entities[i], 'attributes' => $attributes[i]) ).
	 */
	protected function ReorganizeDBEntryArray($entities, $attributes=null)
	{
		$result = array();
		if (is_array($entities))
		{
			foreach ($entities as $key => $entity)
			{
				if ($entity instanceof DBEntry)
				{
					$attrib = null;
					if ($attributes != null)
					{
						$attrib = $attributes[$key];
					}

					$result[$entity->GetPKey()] = array('entity' => $entity, 'attributes' => $attrib);
				}
			};
		}
		return $result;
	}

	/**
	 * Find raw data of all relations linked with an Entity (with pkey of $pkeyEntity).
	 *
	 * @param DBManager $db 			the DBManager to use.
	 * @param string $columnNameEntity 	the name of the column referencing the entity to find.
	 * @param int $pkeyEntity 			the pkey (ID) of the entity to find.
	 * @param string $columnNameOtherEntity  	the name of the column referencing the other entities of the relations.
	 * @param string $orderBy  					order by clause if sorting of entities is required (default is '')
	 * @return array with pkey of linked OtherEntity
	 */
	protected function FetchAllRelationsByEntityPkey(DBManager &$db, $columnNameEntity, $pkeyEntity, $columnNameOtherEntity=null, $orderBy='')
	{
		$orderByClause = '';
		if (strlen($orderBy) > 0)
		{
			$orderByClause = ' ORDER BY '.$orderBy;
		}

		return $db->SelectAssoc('SELECT * FROM '.$this->GetRelationPrototype()->GetRelationTableName().' WHERE '.$columnNameEntity.' = '.$pkeyEntity.$orderByClause.';');
	}

	/**
	 * Find raw data of all linked entities based on the raw relation data <code>$relationData</code> and the column name of the linked entity (<code>$columnNameOtherEntity</code>).
	 *
	 * @param DBManager $db 			the DBManager to use.
	 * @param array $relationData 		raw data of relations table.
	 * @param string $columnNameLinkedEntity 	the name of the column referencing the linked entities.
	 * @param string $tableNameLinkedEntities 	the name of the table with data of OtherEntity (result data).
	 * @return array with raw data of all linked entities
	 */
	protected function FetchAllEntitiesByRelationData(DBManager &$db, &$relationData, $columnNameLinkedEntity, $tableNameLinkedEntities)
	{
		$result = array();
		if (count($relationData) > 0)
		{
			// extract the pkeys to fetch
			$pkeys = $relationData[0][$columnNameLinkedEntity];
			for ($i = 1; $i < count($relationData); $i++)
			{
				$pkeys .= ', '.$relationData[$i][$columnNameLinkedEntity];
			}
			// do the query
			$result = $db->SelectAssoc('SELECT * FROM '.$tableNameLinkedEntities.' WHERE pkey IN ('.$pkeys.') ORDER BY pkey;');
		}
		return $result;
	}

	/**
	 * Make a clone of the array $array.
	 *
	 * @param array &$array
	 */
	protected function CloneArray(&$array)
	{
		$clone = array();
		if ($array != null && is_array($array))
		{
			foreach ($array as $key => $value)
			{
				if ($value instanceof DBRelationEntry)
				{
					// clone relations so pkey is set to -1
					$clone[$key] = clone $value;
				}
				else
				{
					// don't clone other values (DBEntry entities) to preserve their pkeys
					$clone[$key] = $value;
				}
			}
			reset($array);
		}
		return $clone;
	}

}
// BaseDBRelationHandler

/**
 * Helps to manage collections of DBRelationEntry entities with support for additional attributes stored with the relations.
 * A manager class can use a concrete implementation of DBRelationHandler to simplify fetching and synchronization of DBRelationEntry
 * entities. If no additional attributes are required and fetching related entites are preferred use the EntityDBRelationHandler.
 *
 * @access   	public
 * @author   	Marco Pfeifer <m.pfeifer@stollvongati.com>
 *
 * @since    	PHP 5.3
 * @version		1.0
 * @copyright 	Copyright (c) 2011 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class DBRelationHandler extends BaseDBRelationHandler
{

	/**
	 * Property $relationCacheA is a cache (temporary store) for all relations to current EntityB (EntityA instances vary)
	 * managed by the DBRelationHandler.
	 * With this cache the relations can work without instantly storing them.
	 *
	 * @var DBRelationEntry[]
	 */
	private $relationCacheA = null;

	/**
	 * Property $relationCacheB is a cache (temporary store) for all relations to current EntityA (EntityB instances vary)
	 * managed by the DBRelationHandler.
	 * With this cache the relations can work without instantly storing them.
	 *
	 * @var DBRelationEntry[]
	 */
	private $relationCacheB = null;

	/**
	 * Constructor.
	 *
	 * @param DBRelationEntry $relationPrototype  	the prototype instance of the relation to manage.
	 */
	public function DBRelationHandler(DBRelationEntry $relationPrototype)
	{
		parent::BaseDBRelationHandler($relationPrototype);
	}

	/**
	 * Clone the caches.
	 *
	 * @see DBManager-2.0/DBEntry::__clone()
	 */
	public function __clone()
	{
		$this->relationCacheA = $this->CloneArray($this->relationCacheA);
		$this->relationCacheB = $this->CloneArray($this->relationCacheB);
	}

	/**
	 * Find all relations to entity B linked with current entity A.
	 *
	 * @param DBManager $db
	 * @return DBRelationEntry[]
	 */
	protected function FindAllRelationsLinkedWithEntityA(DBManager &$db)
	{
		if ($this->relationCacheB === null)
		{
			if ($this->GetEntityA() != null && $this->GetEntityA()->GetPKey() > -1)
			{
				// the EntityA was saved before so it is likely to find relations at the DB.
				$relationData = $this->FetchAllRelationsByEntityPkey($db, $this->GetRelationPrototype()->GetColumnNameEntityA(), $this->GetEntityA()->GetPKey(), $this->GetRelationPrototype()->GetColumnNameEntityB());
				$entityData = $this->FetchAllEntitiesByRelationData($db, $relationData, $this->GetRelationPrototype()->GetColumnNameEntityB(), $this->GetEntityB()->GetTableName());

				$this->relationCacheB = $this->ConvertArraysToRelationsWithEntityA($db, $relationData, $entityData);
			}
			else
			{
				// the EntityA was not saved before so it is not possible find anything at the DB.
				$this->relationCacheB = array();
			}
		}
		return $this->relationCacheB;
	}

	/**
	 * Fill the relationCacheA with relations between <code>$this->GetEntityB()</code> and provided array of EntityA type entities
	 * (<code>$entities</code>) - this will not store anything.
	 *
	 * @param DBManager $db
	 * @param DBEntry[] $entities 		all linked EntityA instances for this relation
	 * @param array $attributes 		all attributes to store with the relation entities
	 */
	protected function SetRelationCacheA(DBManager &$db, array &$entities, array &$attributes)
	{
		// just create the relation entities - on Store() they will be synchronized independent of the relation's pkey.
		$this->relationCacheA = array();
		for ($i = 0; $i < count($entities); $i++)
		{
			// add only if an entity available!
			if ($entities[$i] != null)
			{
				$this->relationCacheA[] = $this->CreateRelation($db, $entities[$i], $this->GetEntityB(), $attributes[$i]);
			}
		}
	}

	/**
	 * Find all relations to entity A linked with current entity B.
	 *
	 * @param DBManager $db
	 * @return DBRelationEntry[]
	 */
	protected function FindAllRelationsLinkedWithEntityB(DBManager &$db)
	{
		if ($this->relationCacheA === null)
		{
			if ($this->GetEntityB() != null && $this->GetEntityB()->GetPKey() > -1)
			{
				// the EntityB was saved before so it is likely to find relations at the DB.
				$relationData = $this->FetchAllRelationsByEntityPkey($db, $this->GetRelationPrototype()->GetColumnNameEntityB(), $this->GetEntityB()->GetPKey(), $this->GetRelationPrototype()->GetColumnNameEntityA());
				$entityData = $this->FetchAllEntitiesByRelationData($db, $relationData, $this->GetRelationPrototype()->GetColumnNameEntityA(), $this->GetEntityA()->GetTableName());

				$this->relationCacheA = $this->ConvertArraysToRelationsWithEntityB($db, $relationData, $entityData);
			}
			else
			{
				// the EntityB was not saved before so it is not possible find anything at the DB.
				$this->relationCacheA = array();
			}
		}
		return $this->relationCacheA;
	}

	/**
	 * Fill the relationCacheB with relations between <code>$this->GetEntityA()</code> and provided array of EntityB type entities
	 * (<code>$entities</code>) - this will not store anything.
	 *
	 * @param DBManager $db
	 * @param DBEntry[] $entities 		all linked EntityB instances for this relation
	 * @param array $attributes 		all attributes to store with the relation entities
	 */
	protected function SetRelationCacheB(DBManager &$db, array &$entities, array &$attributes)
	{
		// just create the relation entities - on Store() they will be synchronized independent of the relation's pkey.
		$this->relationCacheB = array();
		for ($i = 0; $i < count($entities); $i++)
		{
			// add only if an entity available!
			if ($entities[$i] != null)
			{
				$this->relationCacheB[] = $this->CreateRelation($db, $this->GetEntityA(), $entities[$i], $attributes[$i]);
			}
		}
	}

	/**
	 * Synchronize the relations described through the DBRelationEntry entities in relation cache A (they all have the same EntityB
	 * which is the current EntityB ($this->GetEntityB())).
	 * This will remove all relations to any Entity not in $this->relationCacheA (including referenced Entity if $removeEntities
	 * is <code>true</code>). Method FindAllRelationsLinkedWithEntityB() will return the same DBRelationEntry[] as
	 * currently in the relation cache A (same EntityA-pkeys and attributes).
	 *
	 * @param DBManager $db
	 * @param boolean $removeEntities 			If <code>true</code> any referenced EntityA not in $entity_array will be deleted with the relation. Default is <code>false</code>.
	 * @param boolean $ignoreMultipleEntities 	If <code>true</code> only the first relation between EntityA and EntityB is stored, otherwise all relations (is default).
	 */
	protected function SynchronizeRelationCacheA(DBManager &$db, $removeEntities=false, $ignoreMultipleEntities=false)
	{
		// it is easier to sync the relations without the DBRelationEntry entities!

		if ($this->relationCacheA !== null)
		{
			// build the $entityList array
			$entityList = array();
			foreach ($this->relationCacheA as $relation)
			{
				$this->AddToEntityList($entityList, $relation->GetEntityA(), $relation->GetRelationAttributes(), $db, $ignoreMultipleEntities);
			}

			return $this->SynchronizeRelations($db, $entityList, $this->GetRelationPrototype()->GetColumnNameEntityA(), $this->GetEntityB(), $this->GetRelationPrototype()->GetColumnNameEntityB(), $removeEntities, $this->GetEntityA()->GetTableName());
		}

		// nothing to syncronize since relation cache was not initialized!
		return true;
	}

	/**
	 * Synchronize the relations described through the DBRelationEntry entities in relation cache B (they all have the same EntityA
	 * which is the current EntityA ($this->GetEntityA())).
	 * This will remove all relations to any Entity not in $this->relationCacheB (including referenced Entity if $removeEntities
	 * is <code>true</code>). Method FindAllRelationsLinkedWithEntityA() will return the same DBRelationEntry[] as
	 * currently in the relation cache B (same EntityB-pkeys and attributes).
	 *
	 * @param DBManager $db
	 * @param boolean $removeEntities 			If <code>true</code> any referenced EntityB not in $entity_array will be deleted with the relation. Default is <code>false</code>.
	 * @param boolean $ignoreMultipleEntities 	If <code>true</code> only the first relation between EntityA and EntityB is stored, otherwise all relations (is default).
	 */
	protected function SynchronizeRelationCacheB(DBManager &$db, $removeEntities=false, $ignoreMultipleEntities=false)
	{
		// it is easier to sync the relations without the DBRelationEntry entities!

		if ($this->relationCacheB !== null)
		{
			// build the $entityList array
			$entityList = array();
			foreach ($this->relationCacheB as $relation)
			{
				$this->AddToEntityList($entityList, $relation->GetEntityB(), $relation->GetRelationAttributes(), $db, $ignoreMultipleEntities);
			}

			return $this->SynchronizeRelations($db, $entityList, $this->GetRelationPrototype()->GetColumnNameEntityB(), $this->GetEntityA(), $this->GetRelationPrototype()->GetColumnNameEntityA(), $removeEntities, $this->GetEntityB()->GetTableName());
		}

		// nothing to syncronize since relation cache was not initialized!
		return true;
	}

	/**
	 * Convert the raw data arrays <code>$relationData</code> <code>$entityData</code> into an array of relation objects with current entity A.
	 *
	 * @param DBManager $db 	the DBManager to use
	 * @param mixed[] $relationData  the raw data from relation table (= $db->SelectAssoc(...))
	 * @param mixed[] $entityData  the raw data from entity B table (= $db->SelectAssoc(...))
	 * @return DBRelationEntry[]
	 */
	private function ConvertArraysToRelationsWithEntityA(DBManager &$db, array &$relationData, array &$entityData)
	{
		$result = array();

		// load all entities so we can pick them up when creating the relations
		// entities can be used more than one time (but different attributes)!
		$loadedEntities = array();
		for ($i = 0; $i < count($entityData); $i++)
		{
			$entity = $this->GetRelationPrototype()->CreateEntityB($db);
			if ($entity->LoadFromArray($entityData[$i], $db) === true)
			{
				$loadedEntities[$entity->GetPKey()] = $entity;
			}
		}

		// now create all relations
		for ($i = 0; $i < count($relationData); $i++)
		{
			$entity = $loadedEntities[$relationData[$i][$this->GetRelationPrototype()->GetColumnNameEntityB()]];
			if ($entity != null)
			{
				$relation = $this->CreateRelation($db, $this->GetEntityA(), $entity);
				$relation->LoadFromArray($relationData[$i], $db);

				$result[$i] = $relation;
			}
		}

		return $result;
	}

	/**
	 * Convert the raw data arrays <code>$relationData</code> <code>$entityData</code> into an array of relation objects with current entity B.
	 *
	 * @param DBManager $db 	the DBManager to use
	 * @param mixed[] $relationData  the raw data from relation table (= $db->SelectAssoc(...))
	 * @param mixed[] $entityData  the raw data from entity A table (= $db->SelectAssoc(...))
	 * @return DBRelationEntry[]
	 */
	private function ConvertArraysToRelationsWithEntityB(DBManager &$db, array &$relationData, array &$entityData)
	{
		$result = array();

		// load all entities so we can pick them up when creating the relations
		// entities can be used more than one time (but different attributes)!
		$loadedEntities = array();
		for ($i = 0; $i < count($entityData); $i++)
		{
			$entity = $this->GetRelationPrototype()->CreateEntityA($db);
			if ($entity->LoadFromArray($entityData[$i], $db) === true)
			{
				$loadedEntities[$entity->GetPKey()] = $entity;
			}
		}

		// now create all relations
		for ($i = 0; $i < count($relationData); $i++)
		{
			$entity = $loadedEntities[$relationData[$i][$this->GetRelationPrototype()->GetColumnNameEntityB()]];
			if ($entity != null)
			{
				$relation = $this->CreateRelation($db, $entity, $this->GetEntityB());
				$relation->LoadFromArray($relationData[$i], $db);

				$result[$i] = $relation;
			}
		}

		return $result;
	}

}
// DBRelationHandler

/**
 * Helps to manage collections of DBRelationEntry entities without additional attributes stored with the realtions.
 * A manager class can use a concrete implementation of EntityDBRelationHandler to simplify fetching and synchronization of
 * DBRelationEntry entities. This handler hides the existence of DBRelationEntry entities completeley, therefore no relation
 * attributes are supported. The advantage is that the handler supports caching the related entities and could fetch the
 * DBEntry entities directly instead of the DBRelationEntry entities.
 * If attributes are required use the DBRelationHandler base class.
 *
 * @access   	public
 * @author   	Marco Pfeifer <m.pfeifer@stollvongati.com>
 *
 * @since    	PHP 5.3
 * @version		1.0
 * @copyright 	Copyright (c) 2011 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class EntityDBRelationHandler extends BaseDBRelationHandler
{

	/**
	 * Property $entityCacheA is a cache (temporary store) for all EntityA instances linked with current EntityB managed by this EntityDBRelationHandler.
	 * With this cache the relations can work without instantly storing them.
	 *
	 * @var DBEntry[]  the entities of type EntityA
	 */
	private $entityCacheA = null;

	/**
	 * Property $entityCacheB is a cache (temporary store) for all EntityB instances linked with current EntityA managed by this EntityDBRelationHandler.
	 * With this cache the relations can work without instantly storing them.
	 *
	 * @var DBEntry[] 	the entities of type EntityB
	 */
	private $entityCacheB = null;

	/**
	 * Constructor.
	 *
	 * @param DBRelationEntry $relationPrototype  	the prototype instance of the relation to manage.
	 */
	public function EntityDBRelationHandler(DBRelationEntry $relationPrototype)
	{
		parent::BaseDBRelationHandler($relationPrototype);
	}

	/**
	 * Clone the caches.
	 *
	 * @see DBManager-2.0/DBEntry::__clone()
	 */
	public function __clone()
	{
		$this->entityCacheA = $this->CloneArray($this->entityCacheA);
		$this->entityCacheB = $this->CloneArray($this->entityCacheB);
	}

	/**
	 * Find all EntityB entities linked with current EntityA.
	 *
	 * @param DBManager $db
	 * @param string $orderBy  		order by clause if sorting of entities is required (default is '')
	 * @return DBEntry[] 	all EntityB instances linked with current EntityA
	 */
	protected function FindAllEntitiesLinkedWithEntityA(DBManager &$db, $orderBy='')
	{
		if ($this->entityCacheB === null)
		{
			if ($this->GetEntityA() !== null && $this->GetEntityA()->GetPKey() > -1)
			{
				// the EntityA was saved before so it is likely to find relations at the DB.
				$coulmnNameB = $this->GetRelationPrototype()->GetColumnNameEntityB();
				$relationData = $this->FetchAllRelationsByEntityPkey($db, $this->GetRelationPrototype()->GetColumnNameEntityA(), $this->GetEntityA()->GetPKey(), $coulmnNameB, $orderBy);
				$entityData = $this->FetchAllEntitiesByRelationData($db, $relationData, $coulmnNameB, $this->GetEntityB()->GetTableName());

				$entities = $this->ConvertArrayToEntityB($db, $entityData);

				// add one entity for each relation into cache (a relation could exist more than once)
				$this->entityCacheB = array();
				foreach ($relationData as $relation)
				{
					$entity = $entities[$relation[$coulmnNameB]];
					if ($entity !== null)
					{
						$this->entityCacheB[] = $entity;
					}
				}
			}
			else
			{
				// the EntityA was not saved before, so it is not possible find anything at the DB.
				$this->entityCacheB = array();
			}
		}

		return $this->entityCacheB;
	}

	/**
	 * Find all EntityA entities linked with current EntityB.
	 *
	 * @param DBManager $db
	 * @param string $orderBy  		order by clause if sorting of entities is required (default is '')
	 * @return DBEntry[] 	all EntityA instances linked with current EntityB
	 */
	protected function FindAllEntitiesLinkedWithEntityB(DBManager &$db, $orderBy='')
	{
		if ($this->entityCacheA === null)
		{
			if ($this->GetEntityB() !== null && $this->GetEntityB()->GetPKey() > -1)
			{
				// the EntityB was saved before so it is likely to find relations at the DB.
				$coulmnNameA = $this->GetRelationPrototype()->GetColumnNameEntityA();
				$relationData = $this->FetchAllRelationsByEntityPkey($db, $this->GetRelationPrototype()->GetColumnNameEntityB(), $this->GetEntityB()->GetPKey(), $coulmnNameA, $orderBy);
				$entityData = $this->FetchAllEntitiesByRelationData($db, $relationData, $coulmnNameA, $this->GetEntityA()->GetTableName());

				$entities = $this->ConvertArrayToEntityA($db, $rawResults);

				// add one entity for each relation into cache (a relation could exist more than once)
				$this->entityCacheA = array();
				foreach ($relationData as $relation)
				{
					$entity = $entities[$relation[$coulmnNameA]];
					if ($entity !== null)
					{
						$this->entityCacheA[] = $entity;
					}
				}
			}
			else
			{
				// the EntityB was not saved before, so it is not possible find anything at the DB.
				$this->entityCacheA = array();
			}
		}
		return $this->entityCacheA;
	}

	/**
	 * Synchronize the relations between entities in $entityCacheA (of type EntityA) and current EntityB ($this->GetEntityB())).
	 * This will remove all relations to any Entity not in $this->entityCacheA (including referenced Entity if $removeEntities
	 * is <code>true</code>). Method FindAllEntitiesLinkedWithEntityB() will return the same DBEntry[] as currently in the entity cache A ($entityCacheA).
	 *
	 * @param DBManager $db
	 * @param boolean $removeEntities 		If <code>true</code> any referenced EntityA not in $entityCacheA will be deleted with the relation. Default is <code>false</code>.
	 * @param boolean $ignoreMultipleEntities 	If <code>true</code> only the first relation between EntityA and EntityB is stored, otherwise all relations (is default).
	 */
	protected function SynchronizeEntityCacheA(DBManager &$db, $removeEntities=false, $ignoreMultipleEntities=false)
	{
		if ($this->entityCacheA !== null)
		{
			$entityList = array();
			// build the $entityList array
			foreach ($this->entityCacheA as $entity)
			{
				if ($entity !== null)
				{
					$this->AddToEntityList($entityList, $entity, null, $db, $ignoreMultipleEntities);
				}
			}
			// syncronize the relations
			return $this->SynchronizeRelations($db, $entityList, $this->GetRelationPrototype()->GetColumnNameEntityA(), $this->GetEntityB(), $this->GetRelationPrototype()->GetColumnNameEntityB(), $removeEntities, $this->GetEntityA()->GetTableName());
		}

		// nothing to syncronize since relations are untouched!
		return true;
	}

	/**
	 * Synchronize the relations between entities in $entityCacheB (of type EntityB) and current EntityA ($this->GetEntityA())).
	 * This will remove all relations to any Entity not in $this->entityCacheB (including referenced Entity if $removeEntities
	 * is <code>true</code>). Method FindAllEntitiesLinkedWithEntityA() will return the same DBEntry[] as currently in the entity
	 * cache B ($entityCacheB).
	 *
	 * @param DBManager $db
	 * @param boolean $removeEntities 		If <code>true</code> any referenced EntityB not in $entityCacheB will be deleted with the relation. Default is <code>false</code>.
	 * @param boolean $ignoreMultipleEntities 	If <code>true</code> only the first relation between EntityA and EntityB is stored, otherwise all relations (is default).
	 */
	protected function SynchronizeEntityCacheB(DBManager &$db, $removeEntities=false, $ignoreMultipleEntities=false)
	{
		if ($this->entityCacheB !== null)
		{
			$entityList = array();
			// build the $entityList array
			foreach ($this->entityCacheB as $entity)
			{
				if ($entity !== null)
				{
					$this->AddToEntityList($entityList, $entity, null, $db, $ignoreMultipleEntities);
				}
			}

			// syncronize the relations
			return $this->SynchronizeRelations($db, $entityList, $this->GetRelationPrototype()->GetColumnNameEntityB(), $this->GetEntityA(), $this->GetRelationPrototype()->GetColumnNameEntityA(), $removeEntities, $this->GetEntityB()->GetTableName());
		}

		// nothing to syncronize since relations are untouched!
		return true;
	}

	/**
	 * Set all EntityA instances wich define all relations to current EntityB (set property $entityCacheA) - this will not store anything.
	 *
	 * @param DBManager $db
	 * @param DBEntry[] $entities 		all EntityA instances linked with current EntityB (SetEntityB()).
	 */
	protected function SetEntityCacheA(DBManager &$db, array &$entities)
	{
		// just set the entities - on Store() they will be synchronized.
		$this->entityCacheA = $entities;
	}

	/**
	 * Set all EntityB instances wich define all relations to current EntityA (set property $entityCacheB) - this will not store anything.
	 *
	 * @param DBManager $db
	 * @param DBEntry[] $entities 		all EntityB instances linked with current EntityA (SetEntityA()).
	 */
	protected function SetEntityCacheB(DBManager &$db, array &$entities)
	{
		// just set the entities - on Store() they will be synchronized.
		$this->entityCacheB = $entities;
	}

	/**
	 * Convert the raw data at array <code>$rawResults</code> into an array of entity A objects.
	 *
	 * @param DBManager $db 	the DBManager to use
	 * @param mixed[] $rawResults (= $db->SelectAssoc(...))
	 * @return EntityA[]
	 */
	private function ConvertArrayToEntityA(DBManager &$db, array &$rawResults)
	{
		$result = array();
		foreach ($rawResults as $row)
		{
			$entity = $this->GetRelationPrototype()->CreateEntityA($db);
			$entity->LoadFromArray($row, $db);
			// add with pkey as key for easier handling
			$result[$entity->GetPKey()] = $entity;
		}

		return $result;
	}

	/**
	 * Convert the raw data at array <code>$rawResults</code> into an array of entity B objects.
	 *
	 * @param DBManager $db 	the DBManager to use
	 * @param mixed[] $rawResults (= $db->SelectAssoc(...))
	 * @return EntityB[]
	 */
	private function ConvertArrayToEntityB(DBManager &$db, array &$rawResults)
	{
		$result = array();
		foreach ($rawResults as $row)
		{
			$entity = $this->GetRelationPrototype()->CreateEntityB($db);
			$entity->LoadFromArray($row, $db);
			// add with pkey as key for easier handling
			$result[$entity->GetPKey()] = $entity;
		}

		return $result;
	}

}
// EntityDBRelationHandler