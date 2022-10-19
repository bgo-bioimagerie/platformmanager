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
    public function indexAction($idSpace)
    {
        $modelParam = new CoreConfig();
        $message_public = $modelParam->getParamSpace("tilemessage", $idSpace);
        $message_private = $modelParam->getParamSpace("private_tilemessage", $idSpace);

        $tweets = array();
        if ($modelParam->getParamSpace("use_twitter", $idSpace)) {
            $tweets = $this->getTweets($idSpace);
        }

        $modelNews = new ComNews();
        $news = $modelNews->getByDate($idSpace, 10); /// \todo add a parameter here to get the limit number


        $this->render(array("id_space" => $idSpace,
            "message_public" => $message_public,
            "message_private" => $message_private,
            "news" => $news,
            "tweets" => $tweets
        ));
    }

    public function getnewsAction($idSpace)
    {
        $modelNews = new ComNews();
        $news = $modelNews->getByDate($idSpace, 10);
        $this->render(['data' => ['news' => $news]]);
    }

    public function getTweets($idSpace)
    {
        $modelParam = new CoreConfig();
        require_once('externals/twitter-api/TwitterAPIExchange.php');

        // connect
        $settings = array(
            'oauth_access_token' => $modelParam->getParamSpace("twitter_oauth_access_token", $idSpace),
            'oauth_access_token_secret' => $modelParam->getParamSpace("twitter_oauth_access_token_sec", $idSpace),
            'consumer_key' => $modelParam->getParamSpace("twitter_consumer_key", $idSpace),
            'consumer_secret' => $modelParam->getParamSpace("twitter_consumer_secret", $idSpace)
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
