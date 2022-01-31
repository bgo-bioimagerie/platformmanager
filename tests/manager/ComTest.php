<?php

require_once 'tests/ComBaseTest.php';


class ComTest extends ComBaseTest {

    public function testConfigureModuleCom() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateCom($space, $user);
        }
    }

    public function testEditNews(){
        // only space admin can edit news
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);

            $user = $this->user($data['users'][0]);
            $this->asUser($user['login'], $space['id']);
            $canEdit = true;
            try {
                $req = $this->request([
                    "path" => "comnewsedit/".$space['id'].'/0',
                    "id" => 0
                 ]); 
                $c = new ComnewsController($req, $space);
                $data = $c->runAction('com', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
            } catch(Exception) {
                $canEdit = false;
            }
            $this->assertFalse($canEdit);


            if(empty($data['admins'])) {
                continue;
            }
            $admin = $this->user($data['admins'][0]);
            $this->asUser($admin['login'], $space['id']);

            $date = new DateTime();
            $date->modify('next monday');
            $req = $this->request([
                "path" => "comnewsedit/".$space['id'].'/0',
                "formid" => 'comneweditform',
                "id" => 0,
                "title" => "a news",
                "content"=> "some great news",
                "date" => date('Y-m-d'),
                "expire" => $date->format('Y-m-d')
             ]); 
            $c = new ComnewsController($req, $space);
            $data = $c->runAction('com', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
            $news = $data['news'];
            $this->assertTrue($news['id'] > 0);
            break;
        }
    }

    public function testReadNews() {
        // user and manager can read news
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);

            $user = $this->user($data['users'][0]);
            $this->asUser($user['login'], $space['id']);

            $req = $this->request([
                "path" => "comnewsedit/".$space['id'],
             ]); 
            $c = new ComnewsController($req, $space);
            $data = $c->runAction('com', 'index', ['id_space' =>$space['id']]);
            $news = $data['news'];
            $this->assertTrue(count($news) > 0);
            break;
        }
    }

}

?>