<?php

namespace Difra\Plugins\Users;

use Difra\Auth;
use Difra\Exception;
use Difra\Libs\Cookies;
use Difra\PDO;
use Difra\Plugins\Users;

/**
 * Class Session
 * Long sessions ("remember me" checkbox)
 * @package Difra\Plugins\Users
 */
class Session
{
    public static function save()
    {
        if (!Auth::getInstance()->isLogged() or isset($_COOKIE['resume'])) {
            return;
        }

        $sessionId = sha1(uniqid()) . substr(sha1(uniqid()), 1, 8);
        $cookies = Cookies::getInstance();
        $cookies->setExpire(time() + 31 * 3 * 24 * 60 * 60);
        $cookies->set('resume', $sessionId);

        PDO::getInstance(Users::getDB())->query(
            'REPLACE INTO `user_session` SET `user`=?, `session`=?, `ip`=?',
            [Auth::getInstance()->getUserId(), $sessionId, ip2long($_SERVER['REMOTE_ADDR'])]
        );
    }

    public static function remove()
    {
        PDO::getInstance(Users::getDB())->query("DELETE FROM `user_session` WHERE `session`=?", [$_COOKIE['resume']]);
        Cookies::getInstance()->remove('resume');
    }

    public static function load()
    {
        if (Auth::getInstance()->isLogged()) {
            return;
        }

        if (!isset($_COOKIE['resume']) or strlen($_COOKIE['resume']) != 48) {
            return;
        }

        try {
            // find session in db
            $session = PDO::getInstance()->fetchRow(<<<QUERY
SELECT `s`.`ip`, `s`.`user`
    FROM `user_session` `s`
    WHERE `s`.`session`=?
QUERY
                , [$_COOKIE['resume']]
            );
            if (empty($session)) {
                throw new Exception('Long session not found in database');
            }

            // check ip
            if ($session['ip'] & ip2long(Users::IP_MASK) != ip2long($_SERVER['REMOTE_ADDR']) & ip2long(Users::IP_MASK)) {
                throw new Exception('Long session IP does not match');
            }

            // find user
            $user = User::getById($session['user']);
            $user->login();
        } catch (Exception $ex) {
            self::remove();
        }
    }
}
