<?php

use Difra\Param, Difra\Plugins\Announcements;

class EventsIndexController extends \Difra\Controller
{
    /**
     * @var \DOMNode null
     */
    private $eventRoot = null;

    public function indexAction(\Difra\Param\AnyString $link = null)
    {

        if (!is_null($link)) {
            // страница анонса события

            $this->eventRoot = $this->root->appendChild($this->xml->createElement('announcements-event-view'));
            $this->eventRoot->setAttribute('view', true);
            $this->_showEvent(rawurldecode($link->val()));
        } else {

            if (\Difra\Plugger::isEnabled('Blogs')) {
                $Group = \Difra\Plugins\Blogs\Group::current();
                if ($Group) {
                    // страница анонсов группы

                    $this->eventRoot = $this->root->appendChild($this->xml->createElement('groupEvents'));
                    $groupId = $Group->getId();
                    $this->_showGroupEvents($groupId);
                } else {
                    throw new \Difra\View\Exception(404);
                    return;
                }
            } else {
                // общая страница анонсов
                $this->eventRoot = $this->root->appendChild($this->xml->createElement('allEvents'));
                $this->_showByPriority();
            }
        }
    }

    private function _showEvent($link)
    {

        $Announcements = \Difra\Plugins\Announcements::getInstance();
        $Announce = $Announcements->getByLink($link);

        if ($Announce === false) {
            throw new \Difra\View\Exception(404);
            return;
        }

        if ($link != $Announce->getShortLink()) {
            \Difra\View::redirect($Announce->getShortLink());
        }

        $Announce->getXML($this->eventRoot);

        $additionalsFieldsNode =
            $this->eventRoot->appendChild($this->eventRoot->ownerDocument->createElement('additionalsFields'));
        \Difra\Plugins\Announcements\Additionals::getListXML($additionalsFieldsNode);
    }

    private function _showByPriority($priority = 100)
    {

        $Announcements = \Difra\Plugins\Announcements::getInstance();
        $Announcements->getByPriorityXML($this->eventRoot, $priority);
    }

    private function _showGroupEvents($groupId)
    {

        $Announcements = \Difra\Plugins\Announcements::getInstance();
        $Announcements->getByGroupXML($groupId, $this->eventRoot);
    }
}
