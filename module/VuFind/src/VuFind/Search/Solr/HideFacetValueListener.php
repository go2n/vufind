<?php
/**
 * Hide values of facet for displaying
 *
 * PHP version 7
 *
 * Copyright (C) The National Library of Finland 2014.
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
 * @author   Frank Morgner <morgnerf@ub.uni-leipzig.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace VuFind\Search\Solr;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\SharedEventManagerInterface;
use VuFindSearch\Backend\BackendInterface;
use VuFindSearch\Service;

/**
 * Hide single facet values from displaying.
 *
 * @category VuFind
 * @package  Search
 * @author   Frank Morgner <morgnerf@ub.uni-leipzig.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class HideFacetValueListener
{
    /**
     * Backend.
     *
     * @var BackendInterface
     */
    protected $backend;

    /**
     * List of facets to show. All other facets are hidden
     *
     * @var array
     */
    protected $showFacets = [];

    /**
     * List of facets to hide.
     *
     * @var array
     */
    protected $hideFacets = [];

    /**
     * Constructor.
     *
     * @param BackendInterface $backend         Search backend
     * @param array            $hideFacetValues Assoc. array of field
     * name => values to exclude from display.
     * @param array            $showFacetValues Assoc. array of field
     * name => values to exclusively show in display.
     */
    public function __construct(
        BackendInterface $backend,
        array $hideFacetValues,
        array $showFacetValues = []
    ) {
        $this->backend = $backend;
        $this->hideFacets = $hideFacetValues;
        $this->showFacets = $showFacetValues;
    }

    /**
     * Attach listener to shared event manager.
     *
     * @param SharedEventManagerInterface $manager Shared event manager
     *
     * @return void
     */
    public function attach(
        SharedEventManagerInterface $manager
    ) {
        $manager->attach(
            'VuFind\Search',
            Service::EVENT_POST,
            [$this, 'onSearchPost']
        );
    }

    /**
     * Hide facet values from display
     *
     * @param EventInterface $event Event
     *
     * @return EventInterface
     */
    public function onSearchPost(EventInterface $event)
    {
        $command = $event->getParam('command');

        if ($command->getTargetBackendName() !== $this->backend->getIdentifier()) {
            return $event;
        }
        $context = $command->getContext();
        if ($context == 'search' || $context == 'retrieve') {
            $this->processHideFacetValue($event);
        }
        return $event;
    }

    /**
     * Process hide facet value
     *
     * @param EventInterface $event Event
     *
     * @return void
     */
    protected function processHideFacetValue($event)
    {
        $result = $event->getParam('command')->getResult();
        $facets = $result->getFacets()->getFieldFacets();

        foreach ($this->hideFacets as $facet => $value) {
            if (isset($facets[$facet])) {
                $facets[$facet]->removeKeys((array)$value);
            }
        }
        foreach ($this->showFacets as $facet => $value) {
            if (isset($facets[$facet])) {
                $facetValues = $facets[$facet]->toArray();
                $facetsToHide = array_diff(array_keys($facetValues), (array)$value);
                $facets[$facet]->removeKeys($facetsToHide);
            }
        }
        return null;
    }
}
