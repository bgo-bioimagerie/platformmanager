<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: upgrade_old_seorder
class CoreUpgradeDB1656643659 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply upgrade old seorder");
    $s = new CoreSpace();
    $spaces = $s->getSpaces('id');
    foreach($spaces as $space) {
        $id_space = $space['id'];
        $sql = 'SELECT id_user, id_client FROM cl_j_client_user WHERE id_space=?';
        $users = $this->runRequest($sql, [$id_space])->fetchAll();
        $clmap = [];
        foreach ($users as $user) {
          if(!isset($clmap[$user['id_user']])) {
            $clmap[$user['id_user']] = [];
          }
          $clmap[$user['id_user']][] = $user['id_client'];
        }
        $sql = 'SELECT * from se_order WHERE id_resp=0 AND id_space=?';
        $res = $this->runRequest($sql, [$id_space])->fetchAll();
        foreach($res as $order) {
          if(isset($clmap[$order['id_user']])) {
            $user_clients = $clmap[$order['id_user']];
            if(count($user_clients) > 1) {
              Configuration::getLogger()->error('[db][upgrade] user has multiple clients, skipping', ['user' => $user['id_user'], 'clients' => $user_clients, 'space' => $space['name'], 'id_space' => $id_space]);
              continue;
            }
            $client = $user_clients[0];
            $sql = 'UPDATE se_order SET id_resp=? WHERE id_user=? AND id_space=?';
            $this->runRequest($sql, [$client, $order['id_user'], $id_space]);
          } else {
            Configuration::getLogger()->error('[db][upgrade] no client found for user', ['user' => $user['id_user'], 'space' => $space['name'], 'id_space' => $id_space]);
          }
        }
    }
    Configuration::getLogger()->info("[db][upgrade] Apply upgrade old seorder");
  }
}
$db = new CoreUpgradeDB1656643659();
$db->run();
?>
