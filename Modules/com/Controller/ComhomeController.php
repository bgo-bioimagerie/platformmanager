<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/com/Model/ComTranslator.php';
require_once 'Modules/com/Model/ComNews.php';
require_once 'Modules/com/Controller/ComController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ComhomeController extends ComController
{
    public function indexAction($id_space)
    {
        $modelParam = new CoreConfig();
        $message_public = $modelParam->getParamSpace("tilemessage", $id_space);
        $message_private = $modelParam->getParamSpace("private_tilemessage", $id_space);

        $tweets = array();
        if ($modelParam->getParamSpace("use_twitter", $id_space)) {
            $tweets = $this->getTweets($id_space);
        }

        $modelNews = new ComNews();
        $news = $modelNews->getByDate($id_space, 10); /// \todo add a parameter here to get the limit number


        $this->render(array("id_space" => $id_space,
            "message_public" => $message_public,
            "message_private" => $message_private,
            "news" => $news,
            "tweets" => $tweets
        ));
    }

    public function getnewsAction($id_space)
    {
        $modelNews = new ComNews();
        $news = $modelNews->getByDate($id_space, 10);
        $this->render(['data' => ['news' => $news]]);
    }

    public function getTweets($id_space)
    {
        $modelParam = new CoreConfig();
        require_once('externals/twitter-api/TwitterAPIExchange.php');

        // connect
        $settings = array(
            'oauth_access_token' => $modelParam->getParamSpace("twitter_oauth_access_token", $id_space),
            'oauth_access_token_secret' => $modelParam->getParamSpace("twitter_oauth_access_token_sec", $id_space),
            'consumer_key' => $modelParam->getParamSpace("twitter_consumer_key", $id_space),
            'consumer_secret' => $modelParam->getParamSpace("twitter_consumer_secret", $id_space)
        );

        $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
        $requestMethod = "GET";
        $getfield = 'count=3';

        $twitter = new TwitterAPIExchange($settings);

        $tweets = json_decode($twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest(), true);


        $htmls = array();
        foreach ($tweets as $tweet) {
            $url = 'https://publish.twitter.com/oembed';
            $requestMethod = "GET";
            $getfield = '?url='. "https://twitter.com/".$tweet['user']["screen_name"]."/status/".$tweet['id'];

            $htmlArray = json_decode($twitter->setGetfield($getfield)
                    ->buildOauth($url, $requestMethod)
                    ->performRequest(), true);
            $htmls[] = $htmlArray["html"];
        }
        return $htmls;
    }
}
