<?php
    require('../vendor/autoload.php');
    use \PhpMqtt\Client\MqttClient;
    use \PhpMqtt\Client\ConnectionSettings;

    function mqttPublish($server, $port, $username, $password, $useTls, $topic, $data) {
        $clientId = $username.'-'.rand(1000, 9999);
        $clean_session = true;
        $qos = 0;
        $retain = false;

        /*echo $server.' ';
        echo $port.' ';
        echo $username.' ';
        echo $password.' ';
        echo $useTls.' ';
        echo $topic.' ';
        echo $data.' ';*/

        $connectionSettings = (new ConnectionSettings)
            ->setUsername($username)
            ->setPassword($password)
            ->setConnectTimeout(3)
            ->setUseTls($useTls);

        try {
            $mqtt = new MqttClient($server, $port, $clientId);
            $mqtt->connect($connectionSettings, $clean_session);
            $mqtt->publish($topic, $data, $qos, $retain);
            $mqtt->disconnect();
            return null;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }
