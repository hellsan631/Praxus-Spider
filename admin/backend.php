<?php

if(!session_id()) {
	@session_start();
} else {

}

class Config{
	static $confArray;

	public static function read($name){
		return self::$confArray[$name];
	}

	public static function write($name, $value){
		self::$confArray[$name] = $value;
	}

}

class InvalidArrayKey extends LogicException {}

abstract class DatabaseObject{

    protected $dataArray = array();
    protected $className;

    //Force Extending class to define this method
    abstract protected function classDataSetup();

    public function refresh(){
        $this->classDataSetup();
    }

    // Common method
    public function saveNew(){

        $keyChain = $this->getKeyChain();

        if($keyChain === false){
            throw new InvalidArrayKey("The Array Key doesn't exist within the implemented object.");
        }

        $prepareStatement = "INSERT INTO {$this->className} (";

        foreach($keyChain as $val){
            if($val == "id"){

            }else{
                $prepareStatement .= "$val, ";
            }
        }

        $prepareStatement = rtrim($prepareStatement, ", ");
        $prepareStatement .= ") VALUES (";

        foreach($keyChain as $val){
            if($val == "id"){

            }else{
                $prepareStatement .= ":$val, ";
            }
        }

        $prepareStatement = rtrim($prepareStatement, ", ");
        $prepareStatement .= ")";

        $executeArray = array();

        foreach ($keyChain as $val) {
            if($val == "id"){

            }else{
                $executeArray[':'.$val] = $this->dataArray[$val];
            }
        }

        trigger_error($prepareStatement. " <br/> ".$executeArray);

        try {

            $pdo = Core::getInstance();
            $query = $pdo->dbh->prepare($prepareStatement);

            $query->execute($executeArray);

            if($query->rowCount() > 0){
                $this->setValue('id', $pdo->dbh->lastInsertId());
                return true;
            }

            return false;

        }catch(PDOException $pe) {

            trigger_error('Could not connect to MySQL database. ' . $pe->getMessage() , E_USER_ERROR);

        }

        return false;

    }

    public function saveOld(){

        $keyChain = $this->getKeyChain();

        if($keyChain === false){
            throw new InvalidArrayKey("The Array Key doesn't exist within the implemented object.");
        }

        $prepareStatement = "UPDATE {$this->className} SET ";

        foreach($keyChain as $val){
            if($val == "id"){

            }else{
                $prepareStatement .= "$val = :$val, ";
            }
        }

        $prepareStatement = rtrim($prepareStatement, ", ");
        $prepareStatement .= " WHERE id = :id";

        $executeArray = array();

        foreach ($keyChain as $val) {
            $executeArray[':'.$val] = $this->dataArray[$val];
        }

        //trigger_error($prepareStatement);

        try {

            $pdo = Core::getInstance();
            $query = $pdo->dbh->prepare($prepareStatement);

            $query->execute($executeArray);

            if($query->rowCount() > 0){
                return true;
            }

            return false;

        }catch(PDOException $pe) {

            trigger_error('Could not connect to MySQL database. ' . $pe->getMessage() , E_USER_ERROR);

        }

        return false;

    }

    public function erase(){//needs to handle tree traversal

        $prepareStatement = "DELETE FROM {$this->className} WHERE id = :id";
        $executeArray = array(':id' => $this->getValue('id'));

        try {

            $pdo = Core::getInstance();
            $query = $pdo->dbh->prepare($prepareStatement);

            $query->execute($executeArray);

            if($query->rowCount() > 0){

                if($this->className == "member"){

                    $query = $pdo->dbh->prepare("UPDATE {$this->className} SET parent_id = :parent_id WHERE parent_id = :id");
                    $executeArray = array(':id' => $this->getValue('id'), ':parent_id' => $this->getValue('parent_id'));

                    $query->execute($executeArray);

                }

                return true;
            }

            return false;

        }catch(PDOException $pe) {

            trigger_error('Could not connect to MySQL database. ' . $pe->getMessage() , E_USER_ERROR);

        }

        return false;

    }

    public function loadInstance($id){

        try {

            $pdo = Core::getInstance();
            $query = $pdo->dbh->prepare("SELECT * FROM {$this->className} WHERE id = :id");

            $query->execute(array(':id' => $id));

            $newInstance = $query->fetchObject($this->className);

            if(is_object($newInstance)){
                $newInstance->refresh();
            }

            return $newInstance;

        }catch(PDOException $pe) {

            trigger_error('Could not connect to MySQL database. ' . $pe->getMessage() , E_USER_ERROR);

        }

        return false;

    }

    public function loadChildInstance($parent_id){

        try {

            $pdo = Core::getInstance();
            $query = $pdo->dbh->prepare("SELECT * FROM {$this->className} WHERE parent_id = :id ORDER BY rank_sort ASC");

            $query->execute(array(':id' => $parent_id));

            $objects = $query->fetchAll(PDO::FETCH_CLASS, $this->className);

            if($objects === false){
                return false;
            }

            foreach($objects as $key => $object){

                if(is_object($object)){
                   $object->refresh();
                }

                $objects[$key] = $object;
            }

            return $objects;

        }catch(PDOException $pe) {

            trigger_error('Could not connect to MySQL database. ' . $pe->getMessage() , E_USER_ERROR);

        }

        return false;

    }

    public function getNextInstance($last_id){

        try {

            $pdo = Core::getInstance();
            $query = $pdo->dbh->prepare("SELECT * FROM {$this->className} WHERE id = (SELECT MIN(id) FROM {$this->className} WHERE id > :last_id)");

            $query->execute(array(':last_id' => $last_id));

            $newInstance = $query->fetchObject($this->className);

            if(is_object($newInstance)){
                $newInstance->refresh();
            }

            return $newInstance;

        }catch(PDOException $pe) {

            trigger_error('Could not connect to MySQL database. ' . $pe->getMessage() , E_USER_ERROR);

        }

        return false;

    }

    public function getValue($name){

        if(array_key_exists($name, $this->dataArray)){
            return $this->dataArray[$name];
        }else{
            throw new InvalidArrayKey("The Array Key doesn't exist within the implemented object.");
        }

    }

    public function setValue($name, $value){
        if(array_key_exists($name, $this->dataArray)){
            $this->dataArray[$name] = $value;
        }else{
            throw new InvalidArrayKey("The Array Key doesn't exist within the implemented object.");
        }

    }

    private function getKeyChain(){

        if($this->className == "member"){
            return array('id', 'name', 'title', 'join_date', 'parent_id', 'rank_sort', 'link1', 'link2', 'link3', 'image_url', 'color');
        }

        return false;

    }

    public function getList($sorting = null){

        if($sorting == null){
            $prepareStatement = "SELECT * FROM {$this->className}";
        }else if($sorting == "tree"){
            $prepareStatement = "SELECT * FROM {$this->className} ORDER BY parent_id, rank_sort";
        }else if($sorting == "id"){
            $prepareStatement = "SELECT * FROM {$this->className} ORDER BY id ASC";
        }

        try {

            $pdo = Core::getInstance();
            $query = $pdo->dbh->prepare($prepareStatement);

            $query->execute();

            $objects = $query->fetchAll(PDO::FETCH_CLASS, $this->className);

            if($objects === false){
                return false;
            }

            if($sorting == "tree"){

                $allObjects = $this->recurseTree($objects);

                return $allObjects;

            }else{

                $tempObjects = array();

                foreach($objects as $key => $object){

                    if(is_object($object)){
                        $object->refresh();
                    }

                    $tempObjects[$key] = $object;
                }

                return $tempObjects;

            }

        }catch(PDOException $pe) {

            trigger_error('Could not connect to MySQL database. ' . $pe->getMessage() , E_USER_ERROR);

        }

        return false;
    }

    public function recurseTree($allObjects, $master_id = null){

        if($master_id == null){
            $master_id = $this->getValue('id');
        }

        $objects_store = array();

        $self = new member;
        $self = $self->loadInstance($master_id);

        $parent = new member;
        $parent = $parent->loadInstance($self->getValue('parent_id'));

        if($parent !== false){
            array_push($objects_store, $parent);

            array_push($objects_store, "sep");
        }

        $objects_store = $this->goTree($allObjects, $master_id, $objects_store);

        if(count($objects_store) > 0){
            return $objects_store;
        }

        return false;

    }

    public function goTree($allObjects, $master_id, $objects_store){

        $id_keeper = array();
        $ARRAY_KEEPER = array();

        foreach($allObjects as $value){

            if($value->getValue('id') == $master_id){

                if(!in_array($value, $objects_store)){

                    array_push($objects_store, $value);

                    array_push($objects_store, "sep");

                }

            }else if($value->getValue('parent_id') == $master_id){

                if(!in_array ($value, $objects_store)){

                    array_push($ARRAY_KEEPER, $value);

                    if(!in_array ($value->getValue('id'), $id_keeper)){
                        array_push($id_keeper, $value->getValue('id'));
                    }
                }
            }

        }

        foreach($ARRAY_KEEPER as $value){

            array_push($objects_store, $value);

        }

        array_push($objects_store, "sep");

        foreach($id_keeper as $value){

            $master_id = $value;
            $objects_store = $this->goTree($allObjects, $master_id, $objects_store);

        }

        return $objects_store;

    }

}

class member extends DatabaseObject{

    public $id;
    public $name;
    public $title;
    public $join_date;
    public $parent_id;
    public $rank_sort;
    public $link1;
    public $link2;
    public $link3;
    public $image_url;
    public $color;

    public function __construct(){
        $this->classDataSetup();
    }

    protected function classDataSetup(){
        $this->dataArray = array('id' => $this->id, 'name' => $this->name, 'title' => $this->title,
            'join_date' => $this->join_date, 'parent_id' => $this->parent_id, 'rank_sort' => $this->rank_sort,
            'link1' => $this->link1, 'link2' => $this->link2, 'link3' => $this->link3,
            'image_url' => $this->image_url, 'color' => $this->color);
        $this->className = 'member';
    }

    public function getRelated($className){

        try {

            $pdo = Core::getInstance();
            $query = $pdo->dbh->prepare("SELECT * FROM {$className} WHERE parent_id = :id ORDER BY rank_sort ASC");

            $query->execute(array(':id' => $this->getValue('id')));

            $objects = $query->fetchAll(PDO::FETCH_CLASS, $className);

            foreach($objects as $key => $object){
                $object->refresh();
                $objects[$key] = $object;
            }

            return $objects;

        }catch(PDOException $pe) {

            trigger_error('Could not connect to MySQL database. ' . $pe->getMessage() , E_USER_ERROR);

        }

        return false;

    }

}

class Core{

	public $dbh;
	private static $instance;

	public function __construct(){

		$dsn = 'mysql:host=' . Config::read('db.host') .
			';dbname='    . Config::read('db.base') .
			';connect_timeout=15';

		$this->dbh = new PDO($dsn, Config::read('db.user'), Config::read('db.password'), array(PDO::ATTR_PERSISTENT => true));
		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
	}

	public static function getInstance(){
		if (!isset(self::$instance)){
			$object = __CLASS__;
			self::$instance = new $object;
		}

		return self::$instance;
	}

}

?>