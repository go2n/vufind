<?php

/**
 * Abstract base class of SOLR error listeners.
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2013.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Search
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace VuFind\Search\Solr;

use Laminas\EventManager\EventInterface;

use Laminas\EventManager\SharedEventManagerInterface;
use SplObjectStorage;

use VuFindSearch\Backend\BackendInterface;
use VuFindSearch\Service;

/**
 * Abstract base class of SOLR error listeners.
 *
 * @category VuFind
 * @package  Search
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
abstract class AbstractErrorListener
{
    /**
     * Tag indicating a parser error.
     *
     * @var string
     */
    public const TAG_PARSER_ERROR = 'VuFind\Search\ParserError';

    /**
     * Backends to listen for.
     *
     * @var SplObjectStorage
     */
    protected $backends;

    /**
     * Constructor.
     *
     * @param string $backend Name of backend to listen for
     *
     * @return void
     */
    public function __construct(BackendInterface $backend)
    {
        $this->backends = new SplObjectStorage();
        $this->addBackend($backend);
    }

    /**
     * Add backend to listen for.
     *
     * @param BackendInterface $backend Backend instance
     *
     * @return void
     */
    public function addBackend(BackendInterface $backend)
    {
        $this->backends->attach($backend);
    }

    /**
     * Return true if listeners listens for backend errors.
     *
     * @param BackendInterface $backend Backend instance
     *
     * @return bool
     */
    public function listenForBackend(BackendInterface $backend)
    {
        return $this->backends->contains($backend);
    }

    /**
     * Attach listener to shared event manager.
     *
     * @param SharedEventManagerInterface $manager Shared event manager
     *
     * @return void
     */
    public function attach(SharedEventManagerInterface $manager)
    {
        $manager->attach(
            'VuFind\Search',
            Service::EVENT_ERROR,
            [$this, 'onSearchError']
        );
    }

    /**
     * VuFindSearch.error event.
     *
     * @param EventInterface $event The event
     *
     * @return EventInterface
     */
    abstract public function onSearchError(EventInterface $event);
}
