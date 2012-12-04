<?php

namespace Difra\Plugins\Announcements;

Class Additionals {

    private $id = null;
    private $name = null;
    private $alias = null;

    private $loaded = true;
    private $modified = false;

    public static function create( $id = null ) {

        $A = new self;
        $A->id = $id;
        return $A;
    }

    /**
     * Чистит кэш
     * @static
     *
     */
    private static function cleanCache() {

        \Difra\Cache::getInstance()->remove( 'announcements_additionals' );
    }

    /**
     * Устанавливает название дополнительного поля
     * @param string $name
     */
    public function setName( $name ) {

        $this->name = trim( $name );
        $this->modified = true;
    }

    /**
     * Устанавливает значение технической метки
     * @param string $alias
     */
    public function setAlias( $alias ) {

        $this->alias = trim( $alias );
        $this->modified = true;
    }

    /**
     * Сохраняет в базу дополнительное поле
     */
    private function save() {

        $db = \Difra\MySQL::getInstance();

        if( !is_null( $this->id ) ) {
            //update
            $query = "UPDATE `announcements_additionals` SET `name`='" . $db->escape( $this->name ) .
                    "', `alias`='" . $db->escape( $this->alias ) . "' WHERE `id`='" . intval( $this->id ) . "'";
        } else {
            // insert
            $query = "INSERT INTO `announcements_additionals` SET `name`='" . $db->escape( $this->name ) .
                    "', `alias`='" . $db->escape( $this->alias ) . "'";
        }

        $db->query( $query );
    }

    /**
     * Проверяет альяс на дубликаты в базе
     * @static
     * @param string $alias
     */
    public static function checkAlias( $alias ) {

        $res = \Difra\Cache::getInstance()->get( 'announcements_additionals' );
        if( $res ) {
            foreach( $res as $k=>$data ) {
                if( $data['alias'] == $alias ) {
                    return true;
                }
            }
            return false;
        } else {
            $db = \Difra\MySQL::getInstance();
            $res = $db->fetchOne( "SELECT `id` FROM `announcements_additionals` WHERE `alias`='" . $db->escape( $alias ) . "'" );
            return !empty( $res ) ? true : false;
        }
    }

    /**
     * Возвращает список дополнительных полей в XML
     * @static
     * @param \DOMNode $node
     */
    public static function getListXML( $node ) {

        $Cache = \Difra\Cache::getInstance();

        $res = $Cache->get( 'announcements_additionals' );

        if( !$res ) {
            $db = \Difra\MySQL::getInstance();
            $res = $db->fetch( "SELECT * FROM `announcements_additionals`" );
        } else {
            $node->setAttribute( 'cached', true );
        }

        if( !empty( $res ) ) {
            $saveToCache = null;
            foreach( $res as $n=>$data ) {
                $itemNode = $node->appendChild( $node->ownerDocument->createElement( 'item' ) );
                $itemNode->setAttribute( 'id', $data['id'] );
                $itemNode->setAttribute( 'name', $data['name'] );
                $itemNode->setAttribute( 'alias', $data['alias'] );
                $saveToCache[$data['id']] = $data;
            }
            $Cache->put( 'announcements_additionals', $saveToCache, 10800 );
        }
    }

    /**
     * Удаляет дополнительное поле
     * @static
     * @param int $id
     */
    public static function delete( $id ) {

        \Difra\MySQL::getInstance()->query( "DELETE FROM `announcements_additionals` WHERE `id`='" . intval( $id ) . "'" );
        self::cleanCache();
    }

    public function __destruct() {

        if( $this->modified && $this->loaded ) {
            $this->save();
            self::cleanCache();
        }
    }

}