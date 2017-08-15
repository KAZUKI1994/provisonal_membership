<?php
/**
 * Created by PhpStorm.
 * User: higashiguchi0kazuki
 * Date: 8/15/17
 * Time: 13:30
 */

function db_connect(){
    $dsn = 'mysql:host=localhost;dbname=purephplogin;charset=utf8';
    $user = 'cake';
    $password = 'cake';

    try{
        $dsn = new PDO($dsn, $user, $password);
        return $dsn;
    }catch(PDOException $e){
        print('Error:'.$e->getMessage());
        die();
    }
}
