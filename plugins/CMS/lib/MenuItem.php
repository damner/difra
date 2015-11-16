<?php

namespace Difra\Plugins\CMS;

use Difra\Cache;
use Difra\Exception;
use Difra\MySQL;
use Difra\Plugins\CMS;

/**
 * Class Menuitem
 * @package Difra\Plugins\CMS
 */
class MenuItem
{
    /** @var int */
    private $id = null;
    /** @var int */
    private $menu = null;
    /** @var int */
    private $parent = null;
    /** @var bool */
    private $visible = true;
    /** @var int ID страницы */
    private $page = null;
    /** @var array */
    private $pageData = [];
    /** @var string */
    private $link = null;
    /** @var string */
    private $linkLabel = null;
    /** @var bool */
    private $modified = false;
    /** @var bool */
    private $loaded = true;

    /**
     * Create menu element
     * @static
     * @return MenuItem
     */
    public static function create()
    {
        return new self;
    }

    /**
     * Get menu element by id
     * @static
     * @param int $id
     * @return MenuItem
     */
    public static function get($id)
    {
        $menuitem = new self;
        $menuitem->id = $id;
        $menuitem->loaded = false;
        return $menuitem;
    }

    /**
     * Get elements list for menu with id=$menuId
     * @static
     * @param int $menuId
     * @return MenuItem[]|bool
     */
    public static function getList($menuId)
    {
        try {
            $cacheKey = 'cms_menuitem_list_' . $menuId;
            $cache = Cache::getInstance();
            if (!$data = $cache->get($cacheKey)) {
                $data = CMS::getDB()->fetch(<<<SQL
SELECT `cms_menu_items`.*,`cms`.`id` as `page_id`,`cms`.`tag`,`cms`.`hidden`,`cms`.`title`
FROM `cms_menu_items` LEFT JOIN `cms` ON `cms_menu_items`.`page`=`cms`.`id`
WHERE `menu`=? ORDER BY `position`
SQL
                    , [$menuId]
                );
                $cache->put($cacheKey, $data);
            }
            if (!is_array($data) or empty($data)) {
                return false;
            }
            $res = [];
            foreach ($data as $menuData) {
                $menuitem = new self;
                $menuitem->id = $menuData['id'];
                $menuitem->menu = $menuData['menu'];
                $menuitem->parent = $menuData['parent'];
                $menuitem->visible = $menuData['visible'];
                $menuitem->page = $menuData['page'];
                if (!empty($menuData['tag'])) {
                    $menuitem->pageData = [
                        'id' => $menuData['page_id'],
                        'tag' => $menuData['tag'],
                        'hidden' => $menuData['hidden'],
                        'title' => $menuData['title']
                    ];
                } else {
                    $menuitem->link = $menuData['link'];
                    $menuitem->linkLabel = $menuData['link_label'];
                }
                $menuitem->loaded = true;
                $res[] = $menuitem;
            }
            return $res;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->modified and $this->loaded) {
            $this->save();
        }
    }

    /**
     * Save menu element data
     */
    private function save()
    {
        $db = CMS::getDB();
        if (!$this->id) {
            $pos = $db->fetchOne('SELECT MAX(`position`) FROM `cms_menu_items`');
            $db->query(<<<SQL
INSERT INTO `cms_menu_items` SET
    `menu`=:menu,
    `position`=:position,
    `parent`=:parent,
    `visible`=:visible,
    `page`=:page,
    `link`=:link,
    `link_label`=:link_label
SQL
                , [
                    'menu' => $this->menu,
                    'position' => intval($pos) + 1,
                    'parent' => $this->parent ?: null,
                    'visible' => $this->visible,
                    'page' => $this->page,
                    'link' => $this->link ?: null,
                    'link_label' => $this->linkLabel
                ]
            );
            $this->id = $db->getLastId();
        } else {
            $db->query(<<<'SQL'
UPDATE `cms_menu_items` SET
    `menu`=:menu,
    `parent`=:parent,
    `visible`=:visible,
    `page`=:page,
    `link`=:link,
    `link_label`=:link_label,
    WHERE `id`=:id
SQL
                , [
                    'id' => $this->id,
                    'menu' => $this->menu,
                    'parent' => $this->parent ?: null,
                    'visible' => $this->visible,
                    'page' => $this->page,
                    'link' => $this->link ?: null,
                    'link_label' => $this->linkLabel
                ]
            );
        }
        $this->modified = false;
        $this->clearCache();
    }

    public function clearCache()
    {
        $cache = Cache::getInstance();
        $cache->remove('cms_menuitem_' . $this->getId());
        $cache->remove('cms_menuitem_list_' . $this->getMenuId());
    }

    /**
     * Get menu element id
     * @return int
     */
    public function getId()
    {
        if (!$this->id) {
            $this->save();
        }
        return $this->id;
    }

    /**
     * Get menu id
     * @return int
     */
    public function getMenuId()
    {
        $this->load();
        return $this->menu;
    }

    /**
     * Load menu element data
     * @return bool
     */
    private function load()
    {
        if ($this->loaded) {
            return true;
        }
        if (!$this->id) {
            return false;
        }
        $cache = Cache::getInstance();
        $cacheKey = 'cms_menuitem_' . $this->id;
        if (!$data = $cache->get($cacheKey)) {
            $data = CMS::getDB()->fetchRow("SELECT * FROM `cms_menu_items` WHERE `id`=?", [$this->id]);
            $cache->put($cacheKey, $data);
        }
        if (!$data) {
            return false;
        }
        $this->menu = $data['menu'];
        $this->parent = $data['parent'];
        $this->visible = $data['visible'];
        $this->page = $data['page'];
        $this->link = $data['link'];
        $this->linkLabel = $data['link_label'];
        $this->loaded = true;
        return true;
    }

    /**
     * Get menu element data as XML node
     * @param \DOMElement $node
     * @return bool
     */
    public function getXML($node)
    {
        if (!$this->load()) {
            return false;
        }
        $node->setAttribute('id', $this->id);
        $node->setAttribute('id', $this->id);
        $node->setAttribute('menu', $this->menu);
        $node->setAttribute('parent', $this->parent);
        if ($this->page) {
            if (empty($this->pageData)) {
                $page = Page::get($this->page);
                $this->pageData = [
                    'id' => $page->getId(),
                    'tag' => $page->getUri(),
                    'hidden' => $page->getHidden(),
                    'title' => $page->getTitle()
                ];
            }
            $hidden = (!$this->visible or $this->pageData['hidden']) ? '1' : '0';
            $node->setAttribute('type', 'page');
            $node->setAttribute('label', $this->pageData['title']);
            $node->setAttribute('link', $this->pageData['tag']);
            $node->setAttribute('hidden', $hidden);
            $node->setAttribute('page', $this->pageData['id']);
        } elseif ($this->link) {
            $node->setAttribute('type', 'link');
            $node->setAttribute('label', $this->linkLabel);
            $node->setAttribute('link', $this->link);
            $node->setAttribute('hidden', !$this->visible ? '1' : '0');
        }
        return true;
    }

    /**
     * Delete menu element
     */
    public function delete()
    {
        $this->load();
        $this->modified = false;
        CMS::getDB()->query('DELETE FROM `cms_menu_items` WHERE `id`=?', [$this->id]);
        $this->clearCache();
    }

    /**
     * Get page id (or null if element is not a page)
     * @return int|null
     */
    public function getPage()
    {
        $this->load();
        return $this->page;
    }

    /**
     * Set page id
     * @param int|null $page
     */
    public function setPage($page)
    {
        $this->load();
        if ($page == $this->page) {
            return;
        }
        $this->page = $page;
        $this->modified = true;
    }

    /**
     * Set parent menu by id
     * @param int|null $parent
     */
    public function setParent($parent)
    {
        $this->load();
        if ($parent == $this->parent) {
            return;
        }
        $this->parent = $parent;
        $this->modified = true;
    }

    /**
     * Set link by id
     * @param string|null $link
     */
    public function setLink($link)
    {
        $this->load();
        if ($link == $this->link) {
            return;
        }
        $this->link = $link;
        $this->modified = true;
    }

    /**
     * Set link label
     * @param string $label
     */
    public function setLinkLabel($label)
    {
        $this->load();
        if ($label == $this->linkLabel) {
            return;
        }
        $this->linkLabel = $label;
        $this->modified = true;
    }

    /**
     * Set menu by id
     * @param int $menu
     */
    public function setMenu($menu)
    {
        $this->load();
        if ($this->menu == $menu) {
            return;
        }
        $this->menu = $menu;
        $this->modified = true;
    }

    /**
     * Move element up
     */
    public function moveUp()
    {
        $this->load();
        $db = CMS::getDB();
        $items = $db->fetch(
            "SELECT `id`,`position` FROM `cms_menu_items`
                WHERE `menu`=:menu
                AND `parent`" . ($this->parent ? "=" . $db->escape($this->parent) : ' IS NULL')
            . " ORDER BY `position`",
            [
                'menu' => $this->menu
            ]
        );
        $newSort = [];
        $pos = 1;
        $prev = false;
        foreach ($items as $item) {
            if ($item['id'] != $this->id) {
                if ($prev) {
                    $newSort[$prev['id']] = $pos++;
                }
                $prev = $item;
            } else {
                $newSort[$item['id']] = $pos++;
            }
        }
        if ($prev) {
            $newSort[$prev['id']] = $pos;
        }
        foreach ($newSort as $id => $pos) {
            CMS::getDB()->query(
                "UPDATE `cms_menu_items` SET `position`=:pos WHERE `id`=:id",
                [
                    'pos' => $pos,
                    'id' => $this->id
                ]
            );
        }
        $this->clearCache();
    }

    /**
     * Move element down
     */
    public function moveDown()
    {
        $this->load();
        $db = CMS::getDB();
        $items = CMS::getDB()->fetch(
            "SELECT `id`,`position` FROM `cms_menu_items`
                WHERE
                    `menu`=:menu
                    AND `parent`" . ($this->parent ? '=' . $db->escape($this->parent) : ' IS NULL')
            . " ORDER BY `position`",
            ['menu' => $this->menu]
        );
        $newSort = [];
        $pos = 1;
        $next = false;
        foreach ($items as $item) {
            if ($item['id'] != $this->id) {
                $newSort[$item['id']] = $pos++;
                if ($next) {
                    $newSort[$next['id']] = $pos++;
                    $next = false;
                }
            } else {
                $next = $item;
            }
        }
        if ($next) {
            $newSort[$next['id']] = $pos;
        }
        $db->beginTransaction();
        try {
            foreach ($newSort as $id => $pos) {
                $db->query(
                    "UPDATE `cms_menu_items` SET `position`=:pos WHERE `id`=:id",
                    [
                        'id' => $id,
                        'pos' => $pos
                    ]
                );
            }
        } catch (\PDOException $e) {
            $db->rollBack();
            throw new Exception('PDO Exception: ' . $e->getMessage());
        }
        $db->commit();
        $this->clearCache();
    }
}
