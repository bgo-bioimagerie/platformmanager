<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Project model.
 * This is a minimal project. Extend it to create your own project manager module.
 *
 * @author Sylvain Prigent
 */
class CoreProject extends Model {

	/**
	 * Create the Project table
	 * 
	 * @return PDOStatement
	 */
	public function createTable(){
			
		$sql = "CREATE TABLE IF NOT EXISTS `core_projects` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(30) NOT NULL DEFAULT '',
		`description` varchar(150) NOT NULL DEFAULT '',
		`status` int(1) NOT NULL DEFAULT 1,		
		PRIMARY KEY (`id`)
		);";
		
		$pdo = $this->runRequest($sql);
		return $pdo;
	}
	
	/**
	 * Create the default empty Project
	 * 
	 * @return PDOStatement
	 */
	public function createDefaultProject(){
	
		if(!$this->isProject("--")){
			$sql = "INSERT INTO core_projects (name, description) VALUES(?,?)";
			$this->runRequest($sql, array("--", "--"));
		}
		//INSERT INTO `membres` (`pseudo`, `passe`, `email`) VALUES("Pierre", SHA1("dupont"), "pierre@dupont.fr");
	}
	
	/**
	 * get Projects informations
	 * 
	 * @param string $sortentry Entry that is used to sort the Projects
	 * @return multitype: array
	 */
	public function getProjects($sortentry = 'id'){
		 
		$sql = "select * from core_projects order by " . $sortentry . " ASC;";
		$user = $this->runRequest($sql);
		return $user->fetchAll();
	}
	
	/**
	 * get the names of all the Projects
	 *
	 * @return multitype: array
	 */
	public function projectsName(){
			
		$sql = "select name from core_projects";
		$projects = $this->runRequest($sql);
		return $projects->fetchAll();
	}
	
	/**
	 * Get the Projects ids and names
	 *
	 * @return array
	 */
	public function projectsIDName(){
			
		$sql = "select id, name from core_projects";
		$projects = $this->runRequest($sql);
		return $projects->fetchAll();
	}
	
	/**
	 * Return all the project that have the status "open"
	 * @return multitype:
	 */
	public function openedProjectsIDName(){
		$sql = "select id, name from core_projects where status=1 ORDER BY name";
		$projects = $this->runRequest($sql);
		return $projects->fetchAll();
	}
	
	/**
	 * add a Project to the table
	 *
	 * @param string $name name of the Project
	 * @param string $description description of the Project
	 */
	public function addProject($name, $description){
		
		$sql = "insert into core_projects(name, description)"
				. " values(?, ?)";
		$user = $this->runRequest($sql, array($name, $description));		
	}
	
	/**
	 * update the information of a Project
	 *
	 * @param int $id Id of the Project to update
	 * @param string $name New name of the Project
	 * @param string $description New description of the Project
	 */
	public function editProject($id, $name, $description){
		
		$sql = "update core_projects set name=?, description=? where id=?";
		$project = $this->runRequest($sql, array("".$name."", "".$description."", $id));
	}
	
	/**
	 * Check if a project exists
	 * @param string $name project name
	 * @return boolean
	 */
	public function isProject($name){
		$sql = "select * from core_projects where name=?";
		$project = $this->runRequest($sql, array($name));
		if ($project->rowCount() == 1)
			return true;
		else
			return false;
	}
	
	/**
	 * set a project (add if not exists)
	 * @param string $name Project name
	 * @param string $description Project description
	 */
	public function setProject($name, $description){
		if (!$this->isProject($name)){
			$this->addProject($name, $description);
		}
	}
	
	/**
	 * get the informations of a Project
	 *
	 * @param int $id Id of the Project to query
	 * @throws Exception id the Project is not found
	 * @return mixed array
	 */
	public function getProject($id){
		$sql = "select * from core_projects where id=?";
		$project = $this->runRequest($sql, array($id));
		if ($project->rowCount() == 1)
    		return $project->fetch();  // get the first line of the result
    	else
    		throw new Exception("Cannot find the project using the given id"); 
	}
	
	/**
	 * get the name of a Project
	 *
	 * @param int $id Id of the Project to query
	 * @throws Exception if the Project is not found
	 * @return mixed array
	 */
	public function getProjectName($id){
		$sql = "select name from core_projects where id=?";
		$project = $this->runRequest($sql, array($id));
		if ($project->rowCount() == 1){
			$tmp = $project->fetch();
			return $tmp[0];  // get the first line of the result
		}
		else{
			return "";
		}
	}
	
	/**
	 * get the id of a Project from it's name
	 * 
	 * @param string $name Name of the Project
	 * @throws Exception if the Project connot be found
	 * @return mixed array
	 */
	public function getProjectId($name){
		$sql = "select id from core_projects where name=?";
		$project = $this->runRequest($sql, array($name));
		if ($project->rowCount() == 1){
			$tmp = $project->fetch();
			return $tmp[0];  // get the first line of the result
		}
		else{
			return 0;
		}
	}
	
	/**
	 * set the status of the project
	 *
	 * @param int $status 1 open or active, 0 closed or inactive
	 */
	public function setStatus($id, $status){
		$sql = "update core_projects set status=? where id=?";
		$this->runRequest($sql, array($status, $id));
	}

}

