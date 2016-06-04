<?php

/**
 * Export a .sql file to have a backup of the database
 * 
 */
class CoreBackupDatabase{
	
	/**
	 * Start the backup
	 */
	public function run(){
		
		// save directory
		$chemin = "./data/backup/";
		if (!file_exists($chemin)) {
			mkdir($chemin, 0777, true);
		}
		
		// run backup
		$backupClass = new BackupMySQL(array(
				'host' => Configuration::get("host"),
				'username' => Configuration::get("login"),
				'passwd' => Configuration::get("pwd"),
				'dbname' => Configuration::get("dbname"),
				'dossier' => $chemin
		));
		
		$fichier = $backupClass->getFileName();
		header("Content-disposition: attachment; filename=$fichier");
		header("Content-Type: application/force-download");
		header("Content-Transfer-Encoding: $type\n");
		header("Content-Length: ".filesize($chemin . $fichier));
		header("Pragma: no-cache");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
		header("Expires: 0");
		readfile($chemin . $fichier);
	}
}


error_reporting(E_ALL);
ini_set('display_errors', false);


/**
 * Backup MySQL
 */
class BackupMySQL extends mysqli {
	
	/**
	 * Folder containing the backup files
	 * @var string
	 */
	protected $dossier;
	
	/**
	 * File name
	 * @var string
	 */
	protected $nom_fichier;
	
	/**
	 * Ressource of the GZip files
	 * @var ressource
	 */
	protected $gz_fichier;
	

	public function getFileName(){
		return $this->nom_fichier;
	}	
	
	/**
	 * Constructor
	 * @param array $options
	 */
	public function __construct($options = array()) {
		$default = array(
			'host' => ini_get('mysqli.default_host'),
			'username' => ini_get('mysqli.default_user'),
			'passwd' => ini_get('mysqli.default_pw'),
			'dbname' => '',
			'port' => ini_get('mysqli.default_port'),
			'socket' => ini_get('mysqli.default_socket'),
			// autres options
			'dossier' => './',
			'nbr_fichiers' => 5,
			'nom_fichier' => 'backup'
			);
		$options = array_merge($default, $options);
		extract($options);
		
		// Connexion de la connexion DB
		@parent::__construct($host, $username, $passwd, $dbname, $port, $socket);
		if($this->connect_error) {
			$this->message('Erreur de connexion (' . $this->connect_errno . ') '. $this->connect_error);
			return;
		}
		
		// Controle du dossier
		$this->dossier = $dossier;
		if(!is_dir($this->dossier)) {
			$this->message('Erreur de dossier &quot;' . htmlspecialchars($this->dossier) . '&quot;');
			return;
		}
		
		// Controle du fichier
		$this->nom_fichier = $nom_fichier . date('Ymd-His') . '.sql.gz';
		//echo "file path = " . $this->dossier . $this->nom_fichier . "</br>";
		$this->gz_fichier = gzopen($this->dossier . $this->nom_fichier, 'w');
		if(!$this->gz_fichier) {
			$this->message('Erreur de fichier &quot;' . htmlspecialchars($this->nom_fichier) . '&quot;');
			return;
		}
		
		// Demarrage du traitement
		$this->sauvegarder();
		$this->purger_fichiers($nbr_fichiers);
	}
	
	/**
	 * Information message 
	 * @param string $message HTML
	 */
	protected function message($message = '&nbsp;') {
		//echo '<p style="padding:0; margin:1px 10px; font-family:sans-serif;">'. $message .'</p>';
	}
	
	/**
	 * Protect the quot SQL
	 * @param string $string
	 * @return string
	 */
	protected function insert_clean($string) {
		// Ne pas changer l'ordre du tableau !!!
		$s1 = array( "\\"	, "'"	, "\r", "\n", );
		$s2 = array( "\\\\"	, "''"	, '\r', '\n', );
		return str_replace($s1, $s2, $string);
	}
	
	/**
	 * Save tables
	 */
	protected function sauvegarder() {
		$this->message('Sauvegarde...');
		
		$sql  = '--' ."\n";
		$sql .= '-- '. $this->nom_fichier ."\n";
		gzwrite($this->gz_fichier, $sql);
		
		// Liste les tables
		$result_tables = $this->query('SHOW TABLE STATUS');
		if($result_tables && $result_tables->num_rows) {
			while($obj_table = $result_tables->fetch_object()) {
				$this->message('- ' . htmlspecialchars($obj_table->{'Name'}));
				
				// DROP ...
				$sql  = "\n\n";
				$sql .= 'DROP TABLE IF EXISTS `'. $obj_table->{'Name'} .'`' .";\n";

				// CREATE ...
				$result_create = $this->query('SHOW CREATE TABLE `'. $obj_table->{'Name'} .'`');
				if($result_create && $result_create->num_rows) {
					$obj_create = $result_create->fetch_object();
					$sql .= $obj_create->{'Create Table'} .";\n";
					$result_create->free_result();
				}

				// INSERT ...
				$result_insert = $this->query('SELECT * FROM `'. $obj_table->{'Name'} .'`');
				if($result_insert && $result_insert->num_rows) {
					$sql .= "\n";
					while($obj_insert = $result_insert->fetch_object()) {
						$virgule = false;
						
						$sql .= 'INSERT INTO `'. $obj_table->{'Name'} .'` VALUES (';
						foreach($obj_insert as $val) {
							$sql .= ($virgule ? ',' : '');
							if(is_null($val)) {
								$sql .= 'NULL';
							} else {
								$sql .= '\''. $this->insert_clean($val) . '\'';
							}
							$virgule = true;
						} // for
						
						$sql .= ')' .";\n";
						
					} // while
					$result_insert->free_result();
				}
				
				gzwrite($this->gz_fichier, $sql);
			} // while
			$result_tables->free_result();
		}
		gzclose($this->gz_fichier);
		$this->message('<strong style="color:green;">' . htmlspecialchars($this->nom_fichier) . '</strong>');
		
		$this->message('Sauvegarde termin&eacute;e !');
	}
	
	/**
	 * Remove older files
	 * @param int $nbr_fichiers_max Maximum number of backup
	 */
	protected function purger_fichiers($nbr_fichiers_max) {
		$this->message();
		$this->message('Purge des anciens fichiers...');
		$fichiers = array();
		
		// On recupere le nom des fichiers gz
		if($dossier = dir($this->dossier)) {
			while(false !== ($fichier = $dossier->read())) {
				if($fichier != '.' && $fichier != '..') {
					if(is_dir($this->dossier . $fichier)) {
						// Ceci est un dossier ( et non un fichier )
						continue;
					} else {
						// On ne prend que les fichiers se terminant par ".gz"
						if(preg_match('/\.gz$/i', $fichier)) {
							$fichiers[] = $fichier;
						}
					}
				}
			} // while
			$dossier->close();
		}
		
		// On supprime les  anciens fichiers
		$nbr_fichiers_total = count($fichiers);
		if($nbr_fichiers_total >= $nbr_fichiers_max) {
			// Inverser l'ordre des fichiers gz pour ne pas supprimer les derniers fichiers
			rsort($fichiers);
			
			// Suppression...
			for($i = $nbr_fichiers_max; $i < $nbr_fichiers_total; $i++) {
				$this->message('<strong style="color:red;">' . htmlspecialchars($fichiers[$i]) . '</strong>');
				unlink($this->dossier . $fichiers[$i]);
			}
		}
		$this->message('Purge termin&eacute;e !');
	}
	
}